<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\View\View;
use Illuminate\Http\RedirectResponse;
use App\Models\Shipment;
use App\Models\Invoice;
use App\Models\User;
use Illuminate\Support\Facades\DB;
use Barryvdh\DomPDF\Facade\Pdf;

class InvoiceController extends Controller
{
    /**
     * Show the invoice creation form
     */
    public function create(Request $request): View
    {
        if (!Auth::check() || !Auth::user()->isAdmin()) {
            return redirect()->route('dashboard')->with('error', 'No tienes permisos para acceder a esta sección');
        }

        // Get shipments that are ready to invoice (recibido_ch)
        $clientId = $request->get('client_id');
        
        $query = Shipment::where('internal_status', 'recibido_ch')
            ->whereNull('invoice_id')
            ->with('user');
        
        if ($clientId) {
            $query->where('user_id', $clientId);
        }
        
        $shipments = $query->orderBy('delivery_date', 'desc')->get();
        
        // Get clients that have shipments ready to invoice
        $clientsWithPendingShipments = Shipment::where('internal_status', 'recibido_ch')
            ->whereNull('invoice_id')
            ->with('user')
            ->get()
            ->pluck('user')
            ->unique('id')
            ->values();
        
        // Get next invoice number
        $lastInvoice = Invoice::orderBy('invoice_number', 'desc')->first();
        $nextInvoiceNumber = $lastInvoice ? $lastInvoice->invoice_number + 1 : 1;

        return view('admin.invoice.create', [
            'shipments' => $shipments,
            'clients' => $clientsWithPendingShipments,
            'nextInvoiceNumber' => $nextInvoiceNumber,
            'user' => Auth::user()->only('id', 'name', 'email', 'role')
        ]);
    }

    /**
     * Store a new invoice
     */
    public function store(Request $request): RedirectResponse
    {
        if (!Auth::check() || !Auth::user()->isAdmin()) {
            return redirect()->route('dashboard')->with('error', 'No tienes permisos');
        }

        $request->validate([
            'client_id' => 'required|exists:users,id',
            'shipment_ids' => 'required|array',
            'invoice_number' => 'required|integer|unique:invoices,invoice_number',
            'invoice_date' => 'required|date',
            'sender_name' => 'nullable|string|max:255',
            'sender_location' => 'nullable|string|max:255',
            'sender_phone' => 'nullable|string|max:20',
            'recipient_name' => 'nullable|string|max:255',
            'recipient_location' => 'nullable|string|max:255',
            'recipient_phone' => 'nullable|string|max:20',
            'recipient_address' => 'nullable|string|max:255',
            'delivery_cost' => 'nullable|numeric|min:0',
            'services' => 'required|array',
            'services.*.shipment_id' => 'required|exists:shipments,id',
            'services.*.service_type' => 'required|in:maritime,aerial',
            'services.*.price_per_pound' => 'required|numeric|min:0',
        ]);

        try {
            DB::beginTransaction();

            // Get client
            $client = User::findOrFail($request->client_id);
            
            // Get selected shipments
            $shipments = Shipment::whereIn('id', $request->shipment_ids)
                ->where('user_id', $request->client_id)
                ->where('internal_status', 'recibido_ch')
                ->whereNull('invoice_id')
                ->get();

            if ($shipments->isEmpty()) {
                return back()->with('error', 'No se encontraron paquetes válidos para facturar');
            }

            // Create services array for quick lookup
            $servicesLookup = [];
            foreach ($request->services as $service) {
                $servicesLookup[$service['shipment_id']] = [
                    'service_type' => $service['service_type'],
                    'price_per_pound' => $service['price_per_pound'],
                    'description' => $service['description'] ?? null,
                ];
            }

            // Calculate totals
            $totalMaritimeLbs = 0;
            $totalAerialLbs = 0;
            $subtotalMaritime = 0;
            $subtotalAerial = 0;

            foreach ($shipments as $shipment) {
                if (isset($servicesLookup[$shipment->id])) {
                    $service = $servicesLookup[$shipment->id];
                    $weight = $shipment->weight ?? 0;
                    // Minimum 1 lb charge, then use actual weight
                    $billingWeight = $weight < 1 ? 1 : $weight;
                    
                    if ($service['service_type'] === 'maritime') {
                        $totalMaritimeLbs += $billingWeight;
                        $subtotalMaritime += $billingWeight * $service['price_per_pound'];
                    } else {
                        $totalAerialLbs += $billingWeight;
                        $subtotalAerial += $billingWeight * $service['price_per_pound'];
                    }
                }
            }

            $deliveryCost = $request->delivery_cost ?? 0;
            $totalAmount = $subtotalMaritime + $subtotalAerial + $deliveryCost;

            // Create invoice
            $invoice = Invoice::create([
                'user_id' => $request->client_id,
                'invoice_number' => $request->invoice_number,
                'invoice_date' => $request->invoice_date,
                'sender_name' => $request->sender_name,
                'sender_location' => $request->sender_location,
                'sender_phone' => $request->sender_phone,
                'recipient_name' => $request->recipient_name ?? $client->name,
                'recipient_location' => $request->recipient_location ?? $client->department,
                'recipient_phone' => $request->recipient_phone ?? $client->phone,
                'subtotal_maritime' => $subtotalMaritime,
                'subtotal_aerial' => $subtotalAerial,
                'total_maritime_lbs' => $totalMaritimeLbs,
                'total_aerial_lbs' => $totalAerialLbs,
                'package_count' => $shipments->count(),
                'delivery_cost' => $deliveryCost,
                'total_amount' => $totalAmount,
                'note' => $request->note,
            ]);

            // Update shipments - save previous status before invoicing
            foreach ($shipments as $shipment) {
                if (isset($servicesLookup[$shipment->id])) {
                    $service = $servicesLookup[$shipment->id];
                    $weight = $shipment->weight ?? 0;
                    // Minimum 1 lb charge, then use actual weight
                    $billingWeight = $weight < 1 ? 1 : $weight;
                    
                    // Save previous internal_status in metadata before changing
                    $metadata = $shipment->metadata ?? [];
                    $metadata['previous_internal_status_before_invoice'] = $shipment->internal_status;
                    
                    $shipment->update([
                        'invoice_id' => $invoice->id,
                        'service_type_billing' => $service['service_type'],
                        'price_per_pound' => $service['price_per_pound'],
                        'invoice_value' => $billingWeight * $service['price_per_pound'],
                        'invoiced_at' => now(),
                        'internal_status' => 'facturado',
                        'description' => $service['description'] ?? $shipment->description,
                        'metadata' => $metadata,
                    ]);
                }
            }

            DB::commit();

            return redirect()->route('admin.invoice.show', $invoice->id)
                ->with('success', 'Factura creada exitosamente');

        } catch (\Exception $e) {
            DB::rollBack();
            return back()->with('error', 'Error al crear la factura: ' . $e->getMessage())->withInput();
        }
    }

    /**
     * Display a specific invoice
     */
    public function show(string $id): View
    {
        if (!Auth::check() || !Auth::user()->isAdmin()) {
            return redirect()->route('dashboard')->with('error', 'No tienes permisos');
        }

        $invoice = Invoice::with(['user', 'shipments'])->findOrFail($id);

        return view('admin.invoice.show', [
            'invoice' => $invoice,
            'user' => Auth::user()->only('id', 'name', 'email', 'role')
        ]);
    }

    /**
     * List all invoices
     */
    public function index(): View
    {
        if (!Auth::check() || !Auth::user()->isAdmin()) {
            return redirect()->route('dashboard')->with('error', 'No tienes permisos');
        }

        $invoices = Invoice::with('user')
            ->orderBy('invoice_date', 'desc')
            ->orderBy('invoice_number', 'desc')
            ->get();

        return view('admin.invoice.index', [
            'invoices' => $invoices,
            'user' => Auth::user()->only('id', 'name', 'email', 'role')
        ]);
    }

    /**
     * Show edit form for an invoice
     */
    public function edit(string $id): View|RedirectResponse
    {
        if (!Auth::check() || !Auth::user()->isAdmin()) {
            return redirect()->route('dashboard')->with('error', 'No tienes permisos');
        }

        $invoice = Invoice::with(['user', 'shipments'])->findOrFail($id);

        return view('admin.invoice.edit', [
            'invoice' => $invoice,
            'user' => Auth::user()->only('id', 'name', 'email', 'role')
        ]);
    }

    /**
     * Update an invoice
     */
    public function update(Request $request, string $id): RedirectResponse
    {
        if (!Auth::check() || !Auth::user()->isAdmin()) {
            return redirect()->route('dashboard')->with('error', 'No tienes permisos');
        }

        $invoice = Invoice::findOrFail($id);

        $request->validate([
            'invoice_date' => 'required|date',
            'sender_name' => 'nullable|string|max:255',
            'sender_location' => 'nullable|string|max:255',
            'sender_phone' => 'nullable|string|max:20',
            'recipient_name' => 'nullable|string|max:255',
            'recipient_location' => 'nullable|string|max:255',
            'recipient_phone' => 'nullable|string|max:20',
            'services' => 'required|array',
            'services.*.shipment_id' => 'required|exists:shipments,id',
            'services.*.price_per_pound' => 'required|numeric|min:0',
            'delivery_cost' => 'nullable|numeric|min:0',
        ]);

        try {
            DB::beginTransaction();

            // Recalculate totals
            $shipments = $invoice->shipments;
            $totalMaritimeLbs = 0;
            $totalAerialLbs = 0;
            $subtotalMaritime = 0;
            $subtotalAerial = 0;

            $servicesLookup = [];
            foreach ($request->services as $service) {
                $servicesLookup[$service['shipment_id']] = [
                    'price_per_pound' => $service['price_per_pound'],
                    'description' => $service['description'] ?? null,
                ];
            }

            foreach ($shipments as $shipment) {
                if (isset($servicesLookup[$shipment->id])) {
                    $serviceData = $servicesLookup[$shipment->id];
                    $pricePerPound = $serviceData['price_per_pound'];
                    $weight = $shipment->weight ?? 0;
                    // Minimum 1 lb charge, then use actual weight
                    $billingWeight = $weight < 1 ? 1 : $weight;
                    
                    if ($shipment->service_type_billing === 'maritime') {
                        $totalMaritimeLbs += $billingWeight;
                        $subtotalMaritime += $billingWeight * $pricePerPound;
                    } else {
                        $totalAerialLbs += $billingWeight;
                        $subtotalAerial += $billingWeight * $pricePerPound;
                    }
                    
                    // Update shipment
                    $shipment->update([
                        'price_per_pound' => $pricePerPound,
                        'invoice_value' => $billingWeight * $pricePerPound,
                        'description' => $serviceData['description'] ?? $shipment->description,
                    ]);
                }
            }

            $deliveryCost = $request->delivery_cost ?? 0;
            $totalAmount = $subtotalMaritime + $subtotalAerial + $deliveryCost;

            // Update invoice
            $invoice->update([
                'invoice_date' => $request->invoice_date,
                'sender_name' => $request->sender_name,
                'sender_location' => $request->sender_location,
                'sender_phone' => $request->sender_phone,
                'recipient_name' => $request->recipient_name,
                'recipient_location' => $request->recipient_location,
                'recipient_phone' => $request->recipient_phone,
                'subtotal_maritime' => $subtotalMaritime,
                'subtotal_aerial' => $subtotalAerial,
                'total_maritime_lbs' => $totalMaritimeLbs,
                'total_aerial_lbs' => $totalAerialLbs,
                'delivery_cost' => $deliveryCost,
                'total_amount' => $totalAmount,
                'note' => $request->note,
            ]);

            DB::commit();

            return redirect()->route('admin.invoice.show', $invoice->id)
                ->with('success', 'Factura actualizada correctamente');

        } catch (\Exception $e) {
            DB::rollBack();
            return back()->with('error', 'Error al actualizar la factura: ' . $e->getMessage())->withInput();
        }
    }

    /**
     * Delete an invoice
     */
    public function destroy(string $id): RedirectResponse
    {
        if (!Auth::check() || !Auth::user()->isAdmin()) {
            return redirect()->route('dashboard')->with('error', 'No tienes permisos');
        }

        try {
            $invoice = Invoice::with('shipments')->findOrFail($id);

            DB::beginTransaction();

            // Get shipments associated with this invoice BEFORE deleting
            $shipments = Shipment::where('invoice_id', $invoice->id)->get();

            // Restore shipments to their previous status before invoicing
            foreach ($shipments as $shipment) {
                try {
                    // Get previous status from metadata
                    $metadata = is_array($shipment->metadata) ? $shipment->metadata : (is_string($shipment->metadata) ? json_decode($shipment->metadata, true) : []);
                    if (!is_array($metadata)) {
                        $metadata = [];
                    }
                    
                    $previousStatus = $metadata['previous_internal_status_before_invoice'] ?? null;
                    
                    // If no previous status in metadata, determine based on delivery status
                    if (!$previousStatus) {
                        // If shipment is delivered, restore to recibido_ch
                        // Otherwise, restore to recibido_ch (most common before invoicing)
                        if ($shipment->status === Shipment::STATUS_DELIVERED || $shipment->delivery_date) {
                            $previousStatus = Shipment::INTERNAL_STATUS_RECIBIDO_CH;
                        } else {
                            $previousStatus = Shipment::INTERNAL_STATUS_RECIBIDO_CH;
                        }
                    }
                    
                    // Ensure previous status is valid
                    if (!in_array($previousStatus, [
                        Shipment::INTERNAL_STATUS_EN_TRANSITO,
                        Shipment::INTERNAL_STATUS_RECIBIDO_CH,
                        Shipment::INTERNAL_STATUS_FACTURADO,
                        Shipment::INTERNAL_STATUS_ENTREGADO
                    ])) {
                        // Default to recibido_ch if previous status is invalid
                        $previousStatus = Shipment::INTERNAL_STATUS_RECIBIDO_CH;
                    }
                    
                    // Remove previous status from metadata
                    if (isset($metadata['previous_internal_status_before_invoice'])) {
                        unset($metadata['previous_internal_status_before_invoice']);
                    }
                    
                    // Update shipment - restore to previous status
                    $shipment->update([
                        'invoice_id' => null,
                        'invoiced_at' => null,
                        'invoice_value' => null,
                        'service_type_billing' => null,
                        'price_per_pound' => null,
                        'internal_status' => $previousStatus,
                        'metadata' => $metadata,
                    ]);
                } catch (\Exception $e) {
                    // Log error for individual shipment but continue with others
                    \Illuminate\Support\Facades\Log::warning('Error restoring shipment when deleting invoice: ' . $e->getMessage(), [
                        'shipment_id' => $shipment->id,
                        'invoice_id' => $id,
                    ]);
                }
            }

            // Delete invoice
            $invoice->delete();

            DB::commit();

            return redirect()->route('admin.invoice.index')
                ->with('success', 'Factura eliminada correctamente. Los paquetes han regresado a su estado anterior.');

        } catch (\Exception $e) {
            DB::rollBack();
            
            // Log the error for debugging
            \Illuminate\Support\Facades\Log::error('Error deleting invoice: ' . $e->getMessage(), [
                'invoice_id' => $id,
                'trace' => $e->getTraceAsString(),
                'file' => $e->getFile(),
                'line' => $e->getLine(),
            ]);
            
            return redirect()->route('admin.invoice.index')
                ->with('error', 'Error al eliminar la factura: ' . $e->getMessage());
        }
    }

    /**
     * Download invoice as PDF
     */
    public function downloadPdf(string $id)
    {
        if (!Auth::check() || !Auth::user()->isAdmin()) {
            return redirect()->route('dashboard')->with('error', 'No tienes permisos');
        }

        $invoice = Invoice::with(['user', 'shipments'])->findOrFail($id);

        $pdf = Pdf::loadView('admin.invoice.pdf', compact('invoice'));
        
        return $pdf->download('Factura-' . $invoice->invoice_number . '.pdf');
    }
}
