<?php

namespace App\Http\Controllers;

use App\Models\Shipment;
use App\Models\PendingTracking;
use App\Services\EverestScrapingService;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\View\View;
use Illuminate\Http\RedirectResponse;

class DashboardController extends Controller
{
    protected $scrapingService;

    public function __construct()
    {
        // Lazy load the service to avoid breaking if service fails to instantiate
        // We'll load it when needed in the track() method
    }
    
    /**
     * Get scraping service instance
     */
    protected function getScrapingService(): EverestScrapingService
    {
        if (!$this->scrapingService) {
            $this->scrapingService = app(EverestScrapingService::class);
        }
        return $this->scrapingService;
    }
    /**
     * Show client dashboard
     */
    public function index(): View
    {
        try {
            $user = Auth::user();
            
            if (!$user) {
                return redirect()->route('login');
            }
            
            // Get in-transit shipments (not delivered)
            $inTransitShipments = Shipment::where('user_id', $user->id)
                ->where('status', '!=', 'delivered')
                ->orderBy('created_at', 'desc')
                ->get();
                
            // Get delivered shipments (history)
            $deliveredShipments = Shipment::where('user_id', $user->id)
                ->where('status', 'delivered')
                ->orderBy('delivery_date', 'desc')
                ->limit(20)
                ->get();
                
            // Get pending trackings (status: waiting = not verified yet)
            $pendingTrackings = PendingTracking::where('user_id', $user->id)
                ->where('status', 'waiting')
                ->orderBy('created_at', 'desc')
                ->get();

            return view('dashboard.index', [
                'inTransitShipments' => $inTransitShipments,
                'activeShipments' => $inTransitShipments, // Alias for compatibility
                'deliveredShipments' => $deliveredShipments,
                'pendingTrackings' => $pendingTrackings,
                'user' => $user->only('id', 'name', 'email', 'role')
            ]);
        } catch (\Exception $e) {
            \Illuminate\Support\Facades\Log::error('Error loading dashboard: ' . $e->getMessage(), [
                'user_id' => Auth::id(),
                'trace' => $e->getTraceAsString(),
            ]);
            
            return redirect()->route('login')->with('error', 'Error al cargar el dashboard. Por favor, intenta de nuevo.');
        }
    }

    /**
     * Track and save shipment for authenticated user
     */
    public function track(Request $request): RedirectResponse
    {
        $request->validate([
            'tracking_number' => 'required|string|max:255',
        ]);

        $user = Auth::user();
        $trackingNumber = $request->input('tracking_number');

        try {
            // Get scraping service
            $scrapingService = $this->getScrapingService();
            
            // Try to scrape the shipment
            $shipmentData = $scrapingService->scrapeSingleShipment($trackingNumber);

            if ($shipmentData) {
                // Find or create warehouse
                $warehouse = \App\Models\Warehouse::first();
                if (!$warehouse) {
                    $warehouse = \App\Models\Warehouse::create([
                        'name' => 'Default Warehouse',
                        'code' => 'DEFAULT',
                        'is_active' => true,
                    ]);
                }

                // Determine internal_status based on external status
                $internalStatus = 'en_transito';
                if (isset($shipmentData['status'])) {
                    if ($shipmentData['status'] === 'delivered') {
                        $internalStatus = 'recibido_ch';
                    } elseif ($shipmentData['status'] === 'pending') {
                        $internalStatus = 'en_transito';
                    }
                }

                // Check if shipment already exists
                $existingShipment = Shipment::where('tracking_number', $trackingNumber)->first();

                // Prepare data for shipment (don't double-encode metadata)
                $shipmentUpdateData = array_merge($shipmentData, [
                    'warehouse_id' => $warehouse->id,
                    'internal_status' => $internalStatus,
                    'metadata' => $shipmentData, // Already an array, model will cast to JSON
                ]);

                // Remove any fields that shouldn't be mass-assigned
                unset($shipmentUpdateData['tracking_number']); // This is the key, not an update field

                if ($existingShipment) {
                    // If shipment exists, check if it belongs to another user
                    if ($existingShipment->user_id && $existingShipment->user_id !== $user->id) {
                        return redirect()->route('dashboard')->with('error', 'Este paquete ya está asignado a otro cliente.');
                    }
                    
                    // Update existing shipment and assign to current user
                    $existingShipment->update(array_merge($shipmentUpdateData, [
                        'user_id' => $user->id, // Always assign to current user
                    ]));
                    $shipment = $existingShipment;
                } else {
                    // Create new shipment and assign to user
                    $shipmentUpdateData['user_id'] = $user->id;
                    $shipment = Shipment::create(array_merge([
                        'tracking_number' => $trackingNumber,
                    ], $shipmentUpdateData));
                }

                // Check if there was a pending tracking for this number and mark it as found
                PendingTracking::where('tracking_number', $trackingNumber)
                    ->where('user_id', $user->id)
                    ->where('status', 'waiting')
                    ->update([
                        'status' => 'found',
                        'found_at' => now(),
                    ]);

                return redirect()->route('dashboard')->with('success', 'Paquete rastreado y guardado exitosamente.');
            } else {
                // Shipment not found, create pending tracking
                PendingTracking::firstOrCreate(
                    [
                        'tracking_number' => $trackingNumber,
                        'user_id' => $user->id,
                        'status' => 'waiting',
                    ],
                    [
                        'attempts' => 1,
                    ]
                );

                return redirect()->route('dashboard')->with('info', 'Tu tracking está en verificación, te notificaremos cuando esté disponible.');
            }
        } catch (\Exception $e) {
            // Log the error for debugging
            \Illuminate\Support\Facades\Log::error('Error tracking shipment: ' . $e->getMessage(), [
                'tracking_number' => $trackingNumber,
                'user_id' => $user->id,
                'trace' => $e->getTraceAsString(),
            ]);

            // On error, also create pending tracking
            PendingTracking::firstOrCreate(
                [
                    'tracking_number' => $trackingNumber,
                    'user_id' => $user->id,
                    'status' => 'waiting',
                ],
                [
                    'attempts' => 1,
                    'error_message' => $e->getMessage(),
                ]
            );

            return redirect()->route('dashboard')->with('error', 'Error al rastrear el paquete. Se ha agregado a la cola de verificación: ' . $e->getMessage());
        }
    }

    /**
     * Show shipment details
     */
    public function show($id)
    {
        \Illuminate\Support\Facades\Log::info('=== SHIPMENT SHOW START ===', [
            'id' => $id,
            'auth_check' => Auth::check(),
            'user_id' => Auth::id(),
        ]);

        try {
            // Check authentication first
            if (!Auth::check()) {
                \Illuminate\Support\Facades\Log::warning('User not authenticated in shipment show');
                return redirect()->route('login')->with('error', 'Debes iniciar sesión para ver este paquete.');
            }
            
            $user = Auth::user();
            \Illuminate\Support\Facades\Log::info('User authenticated', ['user_id' => $user->id]);
            
            // Validate ID is numeric
            if (!is_numeric($id)) {
                \Illuminate\Support\Facades\Log::warning('Invalid shipment ID', ['id' => $id]);
                return redirect()->route('dashboard')->with('error', 'ID de paquete inválido.');
            }
            
            // Find shipment and ensure user owns it
            \Illuminate\Support\Facades\Log::info('Searching for shipment', [
                'shipment_id' => $id,
                'user_id' => $user->id
            ]);
            
            $shipment = Shipment::where('id', $id)
                ->where('user_id', $user->id)
                ->first();

            if (!$shipment) {
                \Illuminate\Support\Facades\Log::warning('Shipment not found or not owned by user', [
                    'shipment_id' => $id,
                    'user_id' => $user->id
                ]);
                return redirect()->route('dashboard')->with('error', 'No se encontró el paquete o no tienes permiso para verlo.');
            }

            \Illuminate\Support\Facades\Log::info('Shipment found', [
                'shipment_id' => $shipment->id,
                'tracking_number' => $shipment->tracking_number
            ]);

            // Safely handle tracking_events
            try {
                if ($shipment->tracking_events) {
                    if (is_string($shipment->tracking_events)) {
                        $decoded = json_decode($shipment->tracking_events, true);
                        $shipment->tracking_events = is_array($decoded) ? $decoded : [];
                    } elseif (!is_array($shipment->tracking_events)) {
                        $shipment->tracking_events = [];
                    }
                } else {
                    $shipment->tracking_events = [];
                }
                \Illuminate\Support\Facades\Log::info('Tracking events processed', [
                    'count' => count($shipment->tracking_events)
                ]);
            } catch (\Exception $e) {
                \Illuminate\Support\Facades\Log::warning('Error decoding tracking_events: ' . $e->getMessage());
                $shipment->tracking_events = [];
            }

            // Ensure dates are Carbon instances
            try {
                if ($shipment->pickup_date && !($shipment->pickup_date instanceof \Carbon\Carbon)) {
                    $shipment->pickup_date = \Carbon\Carbon::parse($shipment->pickup_date);
                }
                if ($shipment->delivery_date && !($shipment->delivery_date instanceof \Carbon\Carbon)) {
                    $shipment->delivery_date = \Carbon\Carbon::parse($shipment->delivery_date);
                }
                \Illuminate\Support\Facades\Log::info('Dates processed');
            } catch (\Exception $e) {
                \Illuminate\Support\Facades\Log::warning('Error parsing dates: ' . $e->getMessage());
            }

            \Illuminate\Support\Facades\Log::info('Rendering view');

            return view('dashboard.shipment-detail', [
                'shipment' => $shipment,
                'tracking_number' => $shipment->tracking_number,
                'user' => $user->only('id', 'name', 'email', 'role')
            ]);
        } catch (\Illuminate\Database\Eloquent\ModelNotFoundException $e) {
            \Illuminate\Support\Facades\Log::error('ModelNotFoundException in shipment show: ' . $e->getMessage());
            return redirect()->route('dashboard')->with('error', 'No se encontró el paquete.');
        } catch (\Throwable $e) {
            \Illuminate\Support\Facades\Log::error('FATAL ERROR in shipment show: ' . $e->getMessage(), [
                'shipment_id' => $id ?? 'unknown',
                'user_id' => Auth::id(),
                'file' => $e->getFile(),
                'line' => $e->getLine(),
                'trace' => $e->getTraceAsString(),
                'exception_class' => get_class($e),
            ]);
            
            return redirect()->route('dashboard')->with('error', 'Error al cargar el paquete: ' . $e->getMessage());
        }
    }

    /**
     * Show pending trackings
     */
    public function pending(): View
    {
        $user = Auth::user();
        
        $pendingTrackings = PendingTracking::where('user_id', $user->id)
            ->orderBy('created_at', 'desc')
            ->get();

        return view('dashboard.pending', [
            'pendingTrackings' => $pendingTrackings,
            'user' => $user->only('id', 'name', 'email', 'role')
        ]);
    }

    /**
     * Delete pending tracking
     */
    public function deletePending(PendingTracking $pendingTracking): RedirectResponse
    {
        // Ensure user owns this pending tracking
        if ($pendingTracking->user_id !== Auth::id()) {
            abort(403);
        }

        $pendingTracking->delete();

        return redirect()->route('dashboard')->with('success', 'Tracking eliminado exitosamente.');
    }

    /**
     * Show user profile
     */
    public function profile(): View
    {
        return view('dashboard.profile', [
            'user' => Auth::user()
        ]);
    }
}
