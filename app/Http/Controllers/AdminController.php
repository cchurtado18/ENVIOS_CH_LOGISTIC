<?php

namespace App\Http\Controllers;

use App\Models\User;
use App\Models\Shipment;
use App\Models\Invoice;
use App\Models\PendingTracking;
use App\Services\EverestScrapingService;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Str;
use Illuminate\View\View;
use Illuminate\Http\RedirectResponse;
use App\Notifications\ClientPasswordReset;
class AdminController extends Controller
{
    protected $scrapingService;

    public function __construct(EverestScrapingService $scrapingService)
    {
        $this->scrapingService = $scrapingService;
    }

    /**
     * Show admin dashboard
     */
    public function index(Request $request): View
    {
        if (!Auth::check() || !Auth::user()->isAdmin()) {
            abort(403);
        }

        $range = $request->input('range', '30_days');
        $statusFilter = $request->input('status', 'all');

        $now = now();
        $rangeStart = match ($range) {
            '7_days' => $now->copy()->subDays(7),
            '30_days' => $now->copy()->subDays(30),
            '90_days' => $now->copy()->subDays(90),
            'this_month' => $now->copy()->startOfMonth(),
            'all_time' => null,
            default => $now->copy()->subDays(30),
        };

        $totalClients = User::where('role', 'client')->count();

        $shipmentsBase = Shipment::query();
        if ($rangeStart) {
            $shipmentsBase->where('created_at', '>=', $rangeStart);
        }

        $filteredShipmentsQuery = clone $shipmentsBase;
        if ($statusFilter !== 'all') {
            $filteredShipmentsQuery->where('internal_status', $statusFilter);
        }

        $statusCounts = (clone $shipmentsBase)
            ->select('internal_status', DB::raw('COUNT(*) as total'))
            ->groupBy('internal_status')
            ->pluck('total', 'internal_status')
            ->toArray();

        $totalRevenue = (float) Invoice::sum('total_amount');
        $revenueToday = (float) Invoice::whereBetween(
            'created_at',
            [$now->copy()->startOfDay(), $now->copy()->endOfDay()]
        )->sum('total_amount');
        $invoicesCount = (int) Invoice::count();

        $recentShipments = (clone $filteredShipmentsQuery)
            ->with('user:id,name')
            ->orderBy('created_at', 'desc')
            ->limit(10)
            ->get([
                'id',
                'tracking_number',
                'status',
                'internal_status',
                'user_id',
                'created_at',
                'delivery_date',
                'invoice_id',
            ]);

        $stats = [
            'clients' => $totalClients,
            'in_transit' => $statusCounts[Shipment::INTERNAL_STATUS_EN_TRANSITO] ?? 0,
            'received_ch' => $statusCounts[Shipment::INTERNAL_STATUS_RECIBIDO_CH] ?? 0,
            'facturado' => $statusCounts[Shipment::INTERNAL_STATUS_FACTURADO] ?? 0,
            'delivered' => $statusCounts[Shipment::INTERNAL_STATUS_ENTREGADO] ?? 0,
            'revenue_total' => $totalRevenue,
            'revenue_today' => $revenueToday,
            'invoices_count' => $invoicesCount,
        ];

        $rangeOptions = [
            '7_days' => 'Últimos 7 días',
            '30_days' => 'Últimos 30 días',
            '90_days' => 'Últimos 90 días',
            'this_month' => 'Este mes',
            'all_time' => 'Todo el historial',
        ];

        $statusOptions = [
            'all' => 'Todos los estados',
            Shipment::INTERNAL_STATUS_EN_TRANSITO => 'En tránsito',
            Shipment::INTERNAL_STATUS_RECIBIDO_CH => 'Recibido CH',
            Shipment::INTERNAL_STATUS_FACTURADO => 'Facturado',
            Shipment::INTERNAL_STATUS_ENTREGADO => 'Entregado',
        ];

        return view('admin.index', [
            'stats' => $stats,
            'recentShipments' => $recentShipments,
            'range' => $range,
            'statusFilter' => $statusFilter,
            'rangeOptions' => $rangeOptions,
            'statusOptions' => $statusOptions,
            'user' => Auth::user()->only('id', 'name', 'email', 'role'),
        ]);
    }

    /**
     * Show all clients
     */
    public function clients(Request $request): View
    {
        if (!Auth::check() || !Auth::user()->isAdmin()) {
            abort(403);
        }

        $query = User::where('role', 'client');

        // Search by name
        if ($request->has('search') && $request->search) {
            $search = $request->search;
            $query->where(function($q) use ($search) {
                $q->where('name', 'like', "%{$search}%")
                  ->orWhere('email', 'like', "%{$search}%");
            });
        }

        $clients = $query->orderBy('created_at', 'desc')->paginate(20);

        return view('admin.clients', [
            'clients' => $clients,
            'search' => $request->search ?? '',
            'user' => Auth::user()->only('id', 'name', 'email', 'role')
        ]);
    }

    /**
     * Show create client form
     */
    public function createClient(): View
    {
        if (!Auth::check() || !Auth::user()->isAdmin()) {
            abort(403);
        }

        return view('admin.client-create', [
            'user' => Auth::user()->only('id', 'name', 'email', 'role')
        ]);
    }

    /**
     * Store new client
     */
    public function storeClient(Request $request): RedirectResponse
    {
        if (!Auth::check() || !Auth::user()->isAdmin()) {
            abort(403);
        }

        $request->validate([
            'name' => 'required|string|max:255',
            'email' => 'required|email|unique:users,email',
            'phone' => 'required|string|max:20',
            'department' => 'required|string|max:255',
            'address' => 'required|string|max:500',
            'password' => 'required|string|min:8',
        ]);

        // Only admins can create users, and they are always created as 'client'
        // To create an admin, it must be done manually via tinker or seeder
        $client = User::create([
            'name' => $request->name,
            'email' => $request->email,
            'phone' => $request->phone,
            'department' => $request->department,
            'address' => $request->address,
            'password' => Hash::make($request->password),
            'role' => 'client', // Explicitly set - cannot be changed via this form
        ]);

        return redirect()->route('admin.clients')->with('success', 'Cliente creado exitosamente');
    }

    /**
     * Show client details
     */
    public function clientDetails($id): View
    {
        if (!Auth::check() || !Auth::user()->isAdmin()) {
            abort(403);
        }

        $client = User::findOrFail($id);
        
        $inTransitShipments = Shipment::where('user_id', $client->id)
            ->where('status', '!=', 'delivered')
            ->orderBy('created_at', 'desc')
            ->get();
            
        $deliveredShipments = Shipment::where('user_id', $client->id)
            ->where('status', 'delivered')
            ->orderBy('delivery_date', 'desc')
            ->limit(20)
            ->get();

        return view('admin.client-details', [
            'client' => $client,
            'inTransitShipments' => $inTransitShipments,
            'deliveredShipments' => $deliveredShipments,
            'user' => Auth::user()->only('id', 'name', 'email', 'role')
        ]);
    }

    /**
     * Show edit client form
     */
    public function editClient($id): View
    {
        if (!Auth::check() || !Auth::user()->isAdmin()) {
            abort(403);
        }

        $client = User::findOrFail($id);

        return view('admin.client-edit', [
            'client' => $client,
            'user' => Auth::user()->only('id', 'name', 'email', 'role')
        ]);
    }

    /**
     * Update client
     */
    public function updateClient(Request $request, $id): RedirectResponse
    {
        if (!Auth::check() || !Auth::user()->isAdmin()) {
            abort(403);
        }

        $client = User::findOrFail($id);

        $request->validate([
            'name' => 'required|string|max:255',
            'email' => 'required|email|unique:users,email,' . $client->id,
            'phone' => 'required|string|max:20',
            'department' => 'required|string|max:255',
            'address' => 'required|string|max:500',
        ]);

        // Update client info - explicitly exclude 'role' to prevent unauthorized role changes
        // Only admins can update clients, but they cannot change roles via this method
        $client->update($request->only(['name', 'email', 'phone', 'department', 'address']));
        // Note: Role changes should be done manually via database or tinker for security

        return redirect()->route('admin.client', $client->id)->with('success', 'Cliente actualizado exitosamente');
    }

    /**
     * Update client password
     */
    public function updateClientPassword(Request $request, $id): RedirectResponse
    {
        if (!Auth::check() || !Auth::user()->isAdmin()) {
            abort(403);
        }

        $client = User::findOrFail($id);

        $request->validate([
            'password' => 'required|string|min:8|confirmed',
        ]);

        $client->update([
            'password' => Hash::make($request->password),
        ]);

        return redirect()->route('admin.client', $client->id)->with('success', 'Contraseña actualizada exitosamente');
    }

    /**
     * Show assign package form
     */
    public function assignPackage($id): View
    {
        try {
            if (!Auth::check() || !Auth::user()->isAdmin()) {
                abort(403);
            }

            $client = User::findOrFail($id);

            return view('admin.assign-package', [
                'client' => $client,
                'user' => Auth::user()->only('id', 'name', 'email', 'role')
            ]);
        } catch (\Exception $e) {
            \Illuminate\Support\Facades\Log::error('Error showing assign package form: ' . $e->getMessage(), [
                'client_id' => $id,
                'trace' => $e->getTraceAsString(),
            ]);
            abort(500, 'Error al cargar el formulario de asignación: ' . $e->getMessage());
        }
    }

    /**
     * Assign package to client
     */
    public function assignPackagePost(Request $request, $id): RedirectResponse
    {
        if (!Auth::check() || !Auth::user()->isAdmin()) {
            abort(403);
        }

        $client = User::findOrFail($id);

        $request->validate([
            'tracking_number' => 'required|string|max:255',
        ]);

        try {
            // Scrape the shipment - returns an array, not a model
            $shipmentData = $this->scrapingService->scrapeSingleShipment($request->tracking_number);

            if (!$shipmentData) {
                return back()->withErrors(['tracking_number' => 'No se pudo encontrar el paquete con ese número de tracking.']);
            }

            // Find or create warehouse
            $warehouse = \App\Models\Warehouse::first();
            if (!$warehouse) {
                $warehouse = \App\Models\Warehouse::create([
                    'name' => 'Default Warehouse',
                    'code' => 'DEFAULT',
                    'is_active' => true,
                ]);
            }

            // Check if shipment already exists
            $existingShipment = Shipment::where('tracking_number', $request->tracking_number)->first();

            if ($existingShipment) {
                if ($existingShipment->user_id === $client->id) {
                    return back()->withErrors(['tracking_number' => 'Este tracking ya está asignado a este cliente.']);
                }

                if ($existingShipment->user_id && $existingShipment->user_id !== $client->id) {
                    return back()->withErrors(['tracking_number' => 'Este tracking ya está asignado a otro cliente.']);
                }
            }

            // Prepare data for shipment
            $internalStatus = Shipment::determineInternalStatusFromData($shipmentData);
            $shipmentUpdateData = array_merge($shipmentData, [
                'warehouse_id' => $warehouse->id,
                'internal_status' => $internalStatus,
                'metadata' => $shipmentData,
                'user_id' => $client->id, // Assign to client
            ]);

            // Remove tracking_number from update data (it's the key)
            unset($shipmentUpdateData['tracking_number']);

            if ($existingShipment) {
                // Update existing shipment and assign to client
                $existingShipment->update($shipmentUpdateData);
                $shipment = $existingShipment;
            } else {
                // Create new shipment and assign to client
                $shipment = Shipment::create(array_merge([
                    'tracking_number' => $request->tracking_number,
                ], $shipmentUpdateData));
            }

            return redirect()->route('admin.client', $client->id)->with('success', 'Paquete asignado exitosamente al cliente.');
        } catch (\Exception $e) {
            \Illuminate\Support\Facades\Log::error('Error assigning package: ' . $e->getMessage(), [
                'tracking_number' => $request->tracking_number,
                'client_id' => $client->id,
                'trace' => $e->getTraceAsString(),
            ]);
            return back()->withErrors(['tracking_number' => 'Error al rastrear el paquete: ' . $e->getMessage()]);
        }
    }

    /**
     * Reset client password
     */
    public function resetClientPassword(Request $request, $id): RedirectResponse
    {
        if (!Auth::check() || !Auth::user()->isAdmin()) {
            abort(403);
        }

        $client = User::findOrFail($id);

        // Generate random password
        $newPassword = Str::random(12);

        $client->update([
            'password' => Hash::make($newPassword),
        ]);

        // Send notification
        try {
            $client->notify(new ClientPasswordReset($newPassword));
        } catch (\Exception $e) {
            // If email fails, we still want to show the password
        }

        return redirect()->route('admin.client', $client->id)
            ->with('success', 'Contraseña restablecida. Nueva contraseña: ' . $newPassword)
            ->with('password', $newPassword);
    }

    /**
     * Update shipment status
     */
    public function updateShipmentStatus(Request $request, $id): RedirectResponse
    {
        if (!Auth::check() || !Auth::user()->isAdmin()) {
            abort(403);
        }

        $request->validate([
            'internal_status' => 'required|string|in:en_transito,recibido_ch,facturado,entregado',
            'delivery_date' => 'nullable|date',
        ]);

        $shipment = Shipment::findOrFail($id);

        try {
            $updateData = [
                'internal_status' => $request->internal_status,
            ];

            // If changing to delivered, update status and delivery_date
            if ($request->internal_status === Shipment::INTERNAL_STATUS_ENTREGADO || 
                $request->internal_status === Shipment::INTERNAL_STATUS_RECIBIDO_CH) {
                $updateData['status'] = Shipment::STATUS_DELIVERED;
                
                // Set delivery_date if provided or if not set
                if ($request->delivery_date) {
                    $updateData['delivery_date'] = $request->delivery_date;
                } elseif (!$shipment->delivery_date) {
                    $updateData['delivery_date'] = now();
                }
            } elseif ($request->internal_status === Shipment::INTERNAL_STATUS_EN_TRANSITO) {
                // If changing to in_transit, ensure status is not delivered
                if ($shipment->status === Shipment::STATUS_DELIVERED) {
                    $updateData['status'] = Shipment::STATUS_IN_TRANSIT;
                    $updateData['delivery_date'] = null;
                }
            }

            $shipment->update($updateData);

            $redirectUrl = $request->input('redirect_to', route('admin.inventory'));
            
            return redirect($redirectUrl)->with('success', 'Estado del paquete actualizado exitosamente.');
        } catch (\Exception $e) {
            \Illuminate\Support\Facades\Log::error('Error updating shipment status: ' . $e->getMessage(), [
                'shipment_id' => $id,
                'trace' => $e->getTraceAsString(),
            ]);
            
            return back()->withErrors(['error' => 'Error al actualizar el estado del paquete: ' . $e->getMessage()]);
        }
    }

    /**
     * Delete shipment
     */
    public function deleteShipment(Request $request, $id): RedirectResponse
    {
        if (!Auth::check() || !Auth::user()->isAdmin()) {
            abort(403);
        }

        $shipment = Shipment::findOrFail($id);
        $trackingNumber = $shipment->tracking_number;
        
        try {
            $shipment->delete();
            
            $redirectUrl = $request->input('redirect_to', route('admin.inventory'));
            
            return redirect($redirectUrl)->with('success', "Paquete {$trackingNumber} eliminado exitosamente.");
        } catch (\Exception $e) {
            \Illuminate\Support\Facades\Log::error('Error deleting shipment: ' . $e->getMessage(), [
                'shipment_id' => $id,
                'trace' => $e->getTraceAsString(),
            ]);
            
            return back()->withErrors(['error' => 'Error al eliminar el paquete: ' . $e->getMessage()]);
        }
    }
}
