<?php

namespace App\Http\Controllers;

use App\Models\Shipment;
use App\Models\PendingTracking;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\View\View;

class DashboardController extends Controller
{
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
            'deliveredShipments' => $deliveredShipments,
            'pendingTrackings' => $pendingTrackings,
            'user' => $user->only('id', 'name', 'email', 'role')
        ]);
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
     * Show user profile
     */
    public function profile(): View
    {
        return view('dashboard.profile', [
            'user' => Auth::user()
        ]);
    }
}
