<?php

namespace App\Http\Controllers;

use App\Services\EverestScrapingService;
use Illuminate\Http\Request;
use Illuminate\Http\JsonResponse;
use Illuminate\View\View;

class TrackingController extends Controller
{
    protected $scrapingService;

    public function __construct(EverestScrapingService $scrapingService)
    {
        $this->scrapingService = $scrapingService;
    }

    /**
     * Show tracking form (public web view)
     */
    public function show(): View
    {
        return view('tracking.index');
    }

    /**
     * Track a shipment (public endpoint)
     */
    public function track(Request $request): JsonResponse|View
    {
        $request->validate([
            'tracking_number' => 'required|string|max:255'
        ]);

        try {
            $trackingNumber = $request->input('tracking_number');
            $shipment = $this->scrapingService->scrapeSingleShipment($trackingNumber);
            
            if (!$shipment) {
                if ($request->expectsJson()) {
                    return response()->json([
                        'success' => false,
                        'message' => 'Shipment not found or could not be scraped'
                    ], 404);
                }

                return view('tracking.index', [
                    'error' => 'No se pudo encontrar el paquete o el número de tracking es inválido.',
                    'tracking_number' => $trackingNumber
                ]);
            }
            
            // If request expects JSON (API call)
            if ($request->expectsJson()) {
                return response()->json([
                    'success' => true,
                    'message' => 'Shipment tracked successfully',
                    'data' => $shipment
                ]);
            }

            // Return web view with data
            return view('tracking.result', [
                'shipment' => $shipment,
                'tracking_number' => $trackingNumber
            ]);

        } catch (\Exception $e) {
            if ($request->expectsJson()) {
                return response()->json([
                    'success' => false,
                    'message' => 'Failed to track shipment',
                    'error' => $e->getMessage()
                ], 500);
            }

            return view('tracking.index', [
                'error' => 'Ocurrió un error al rastrear el paquete. Por favor, inténtalo de nuevo.',
                'tracking_number' => $request->input('tracking_number')
            ]);
        }
    }
}

