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

    public function __construct(EverestScrapingService $scrapingService)
    {
        $this->scrapingService = $scrapingService;
    }
    /**
     * Show client dashboard
     */
    public function index(): View
    {
        $user = Auth::user();
        
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
            // Try to scrape the shipment
            $shipmentData = $this->scrapingService->scrapeSingleShipment($trackingNumber);

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

                // Update or create shipment and assign to user
                $shipment = Shipment::updateOrCreate(
                    ['tracking_number' => $trackingNumber],
                    array_merge($shipmentData, [
                        'warehouse_id' => $warehouse->id,
                        'user_id' => $user->id,
                        'metadata' => json_encode($shipmentData),
                    ])
                );

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

            return redirect()->route('dashboard')->with('info', 'Tu tracking está en verificación, te notificaremos cuando esté disponible.');
        }
    }

    /**
     * Show shipment details
     */
    public function show(Shipment $shipment): View
    {
        // Ensure user owns this shipment
        if ($shipment->user_id !== Auth::id()) {
            abort(403);
        }

        return view('dashboard.shipment-detail', [
            'shipment' => $shipment,
            'user' => Auth::user()->only('id', 'name', 'email', 'role')
        ]);
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
