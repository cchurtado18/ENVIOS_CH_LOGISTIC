<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Services\EverestScrapingService;
use App\Models\ScrapingLog;
use Illuminate\Http\Request;
use Illuminate\Http\JsonResponse;

class ScrapingController extends Controller
{
    protected $scrapingService;

    public function __construct(EverestScrapingService $scrapingService)
    {
        $this->scrapingService = $scrapingService;
    }

    /**
     * Get scraping logs
     */
    public function index(): JsonResponse
    {
        $logs = ScrapingLog::orderBy('created_at', 'desc')
            ->paginate(20);

        return response()->json([
            'success' => true,
            'data' => $logs
        ]);
    }

    /**
     * Start scraping warehouses
     */
    public function scrapeWarehouses(): JsonResponse
    {
        try {
            $warehouses = $this->scrapingService->scrapeWarehouses();
            
            return response()->json([
                'success' => true,
                'message' => 'Warehouses scraped successfully',
                'data' => [
                    'warehouses_found' => count($warehouses),
                    'warehouses' => $warehouses
                ]
            ]);
        } catch (\Exception $e) {
            return response()->json([
                'success' => false,
                'message' => 'Failed to scrape warehouses',
                'error' => $e->getMessage()
            ], 500);
        }
    }

    /**
     * Start scraping shipments
     */
    public function scrapeShipments(Request $request): JsonResponse
    {
        $request->validate([
            'tracking_numbers' => 'sometimes|array',
            'tracking_numbers.*' => 'string'
        ]);

        try {
            $trackingNumbers = $request->input('tracking_numbers', []);
            $shipments = $this->scrapingService->scrapeShipments($trackingNumbers);
            
            return response()->json([
                'success' => true,
                'message' => 'Shipments scraped successfully',
                'data' => [
                    'shipments_found' => count($shipments),
                    'shipments' => $shipments
                ]
            ]);
        } catch (\Exception $e) {
            return response()->json([
                'success' => false,
                'message' => 'Failed to scrape shipments',
                'error' => $e->getMessage()
            ], 500);
        }
    }

    /**
     * Scrape single shipment by tracking number
     */
    public function scrapeShipment(string $trackingNumber): JsonResponse
    {
        try {
            $shipment = $this->scrapingService->scrapeSingleShipment($trackingNumber);
            
            if (!$shipment) {
                return response()->json([
                    'success' => false,
                    'message' => 'Shipment not found or could not be scraped'
                ], 404);
            }
            
            return response()->json([
                'success' => true,
                'message' => 'Shipment scraped successfully',
                'data' => $shipment
            ]);
        } catch (\Exception $e) {
            return response()->json([
                'success' => false,
                'message' => 'Failed to scrape shipment',
                'error' => $e->getMessage()
            ], 500);
        }
    }

    /**
     * Public tracking API for external use (no database writes)
     */
    public function publicTrack(string $trackingNumber): JsonResponse
    {
        try {
            // Use the scraping service but only get raw data (no saving)
            $shipment = $this->scrapingService->scrapeSingleShipmentPublic($trackingNumber);
            
            if (!$shipment) {
                return response()->json([
                    'success' => false,
                    'message' => 'Track not found'
                ], 404);
            }
            
            return response()->json([
                'success' => true,
                'data' => $shipment
            ]);
        } catch (\Exception $e) {
            return response()->json([
                'success' => false,
                'message' => 'Failed to track shipment',
                'error' => $e->getMessage()
            ], 500);
        }
    }

    /**
     * Get scraping status
     */
    public function status(): JsonResponse
    {
        $recentLogs = ScrapingLog::orderBy('created_at', 'desc')
            ->limit(10)
            ->get();

        $stats = [
            'total_scraping_attempts' => ScrapingLog::count(),
            'successful_scrapes' => ScrapingLog::where('status', 'success')->count(),
            'failed_scrapes' => ScrapingLog::where('status', 'failed')->count(),
            'total_records_processed' => ScrapingLog::sum('records_processed'),
            'last_scrape' => ScrapingLog::latest()->first()?->created_at,
        ];

        return response()->json([
            'success' => true,
            'data' => [
                'stats' => $stats,
                'recent_logs' => $recentLogs
            ]
        ]);
    }
}
