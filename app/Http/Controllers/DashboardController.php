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
            
            // Get in-transit shipments (not delivered) - Using model scope
            $inTransitShipments = Shipment::where('user_id', $user->id)
                ->inTransit()
                ->orderBy('created_at', 'desc')
                ->get();
                
            // Get delivered shipments (history) - Using model scope
            $deliveredShipments = Shipment::where('user_id', $user->id)
                ->delivered()
                ->orderBy('delivery_date', 'desc')
                ->orderBy('updated_at', 'desc')
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
                // Use Shipment model constants for consistency
                $internalStatus = Shipment::INTERNAL_STATUS_EN_TRANSITO;
                
                // Check if shipment is delivered (status or delivery_date)
                $isDelivered = false;
                if (isset($shipmentData['status']) && $shipmentData['status'] === Shipment::STATUS_DELIVERED) {
                    $isDelivered = true;
                } elseif (isset($shipmentData['delivery_date']) && !empty($shipmentData['delivery_date'])) {
                    $isDelivered = true;
                    // Ensure status is set to 'delivered' if delivery_date exists
                    $shipmentData['status'] = Shipment::STATUS_DELIVERED;
                }
                
                // Set internal_status based on delivery status
                if ($isDelivered) {
                    $internalStatus = Shipment::INTERNAL_STATUS_RECIBIDO_CH;
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
        $log = \Illuminate\Support\Facades\Log::channel('single');
        $log->info('========================================');
        $log->info('=== SHIPMENT SHOW METHOD CALLED ===');
        $log->info('ID recibido: ' . $id);
        $log->info('Auth check: ' . (Auth::check() ? 'SI' : 'NO'));
        $log->info('User ID: ' . (Auth::id() ?? 'NULL'));
        $log->info('Session ID: ' . session()->getId());
        $log->info('Request URL: ' . request()->fullUrl());
        $log->info('Request Method: ' . request()->method());

        try {
            // Check authentication first
            if (!Auth::check()) {
                $log->warning('Usuario NO autenticado - redirigiendo a login');
                return redirect()->route('login')->with('error', 'Debes iniciar sesión para ver este paquete.');
            }
            
            $user = Auth::user();
            $log->info('Usuario autenticado correctamente', ['user_id' => $user->id, 'email' => $user->email]);
            
            // Validate ID is numeric
            if (!is_numeric($id)) {
                $log->warning('ID no es numérico: ' . $id);
                return redirect()->route('dashboard')->with('error', 'ID de paquete inválido.');
            }
            
            $log->info('Buscando shipment en BD', ['shipment_id' => $id, 'user_id' => $user->id]);
            
            $shipment = Shipment::where('id', $id)
                ->where('user_id', $user->id)
                ->first();

            if (!$shipment) {
                $log->warning('Shipment NO encontrado o no pertenece al usuario', [
                    'shipment_id' => $id,
                    'user_id' => $user->id
                ]);
                return redirect()->route('dashboard')->with('error', 'No se encontró el paquete o no tienes permiso para verlo.');
            }

            $log->info('Shipment encontrado', [
                'shipment_id' => $shipment->id,
                'tracking_number' => $shipment->tracking_number,
                'status' => $shipment->status
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
                $log->info('Tracking events procesados', ['count' => count($shipment->tracking_events)]);
            } catch (\Exception $e) {
                $log->warning('Error decodificando tracking_events: ' . $e->getMessage());
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
                $log->info('Fechas procesadas correctamente');
            } catch (\Exception $e) {
                $log->warning('Error parseando fechas: ' . $e->getMessage());
            }

            $log->info('ANTES DE RENDERIZAR VISTA');
            $log->info('Ruta de vista: dashboard.shipment-detail');
            $log->info('Variables a pasar: shipment, tracking_number, user');

            $view = view('dashboard.shipment-detail', [
                'shipment' => $shipment,
                'tracking_number' => $shipment->tracking_number,
                'user' => $user->only('id', 'name', 'email', 'role')
            ]);

            $log->info('VISTA CREADA - RETORNANDO');
            $log->info('========================================');
            
            return $view;
            
        } catch (\Illuminate\Database\Eloquent\ModelNotFoundException $e) {
            $log->error('ModelNotFoundException: ' . $e->getMessage());
            $log->info('========================================');
            return redirect()->route('dashboard')->with('error', 'No se encontró el paquete.');
        } catch (\Throwable $e) {
            $log->error('========================================');
            $log->error('ERROR FATAL EN SHIPMENT SHOW');
            $log->error('Mensaje: ' . $e->getMessage());
            $log->error('Archivo: ' . $e->getFile());
            $log->error('Línea: ' . $e->getLine());
            $log->error('Clase: ' . get_class($e));
            $log->error('Trace completo:');
            $log->error($e->getTraceAsString());
            $log->error('========================================');
            
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
