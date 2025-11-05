<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\Shipment;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\View\View;
use Barryvdh\DomPDF\Facade\Pdf;

class InventoryController extends Controller
{
    /**
     * Show inventory page
     */
    public function index(): View
    {
        if (!Auth::check() || !Auth::user()->isAdmin()) {
            abort(403);
        }

        // Group shipments by internal_status
        $shipments = Shipment::with('user')
            ->orderBy('created_at', 'desc')
            ->get();

        $groupedShipments = [
            'recibido_ch' => $shipments->where('internal_status', 'recibido_ch'),
            'en_transito' => $shipments->where('internal_status', 'en_transito'),
            'facturado' => $shipments->where('internal_status', 'facturado'),
            'entregado' => $shipments->where('internal_status', 'entregado'),
        ];

        return view('admin.inventory', [
            'groupedShipments' => $groupedShipments,
            'user' => Auth::user()->only('id', 'name', 'email', 'role')
        ]);
    }

    /**
     * Download report for "Recibido CH" packages
     */
    public function downloadReceivedCHReport()
    {
        if (!Auth::check() || !Auth::user()->isAdmin()) {
            abort(403);
        }

        $shipments = Shipment::where('internal_status', 'recibido_ch')
            ->with('user')
            ->orderBy('created_at', 'desc')
            ->get();

        $pdf = Pdf::loadView('admin.reports.received-ch-report', [
            'shipments' => $shipments
        ]);

        return $pdf->download('reporte_recibido_ch_' . date('Y-m-d') . '.pdf');
    }
}
