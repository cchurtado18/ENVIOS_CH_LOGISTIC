<?php

namespace App\Http\Controllers;

use App\Models\User;
use App\Models\Shipment;
use App\Models\Invoice;
use App\Models\PendingTracking;
use App\Services\EverestScrapingService;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
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
    public function index(): View
    {
        if (!Auth::check() || !Auth::user()->isAdmin()) {
            abort(403);
        }

        $stats = [
            'total_clients' => User::where('role', 'client')->count(),
            'total_shipments' => Shipment::count(),
            'total_invoices' => Invoice::count(),
            'total_revenue' => Invoice::sum('total_amount'),
        ];

        return view('admin.index', [
            'stats' => $stats,
            'user' => Auth::user()->only('id', 'name', 'email', 'role')
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

            // Determine internal_status based on delivery status
            $internalStatus = Shipment::INTERNAL_STATUS_EN_TRANSITO;
            $isDelivered = false;
            
            if (isset($shipmentData['status']) && $shipmentData['status'] === Shipment::STATUS_DELIVERED) {
                $isDelivered = true;
                $internalStatus = Shipment::INTERNAL_STATUS_RECIBIDO_CH;
            } elseif (isset($shipmentData['delivery_date']) && !empty($shipmentData['delivery_date'])) {
                $isDelivered = true;
                $internalStatus = Shipment::INTERNAL_STATUS_RECIBIDO_CH;
                if (!isset($shipmentData['status']) || $shipmentData['status'] !== Shipment::STATUS_DELIVERED) {
                    $shipmentData['status'] = Shipment::STATUS_DELIVERED;
                }
            }

            // Check if shipment already exists
            $existingShipment = Shipment::where('tracking_number', $request->tracking_number)->first();

            // Prepare data for shipment
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
}
