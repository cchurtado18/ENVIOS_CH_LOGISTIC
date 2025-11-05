<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Models\Shipment;
use App\Models\Warehouse;
use App\Services\EverestScrapingService;
use Illuminate\Http\Request;
use Illuminate\Http\JsonResponse;

class ShipmentController extends Controller
{
    protected $scrapingService;

    public function __construct(EverestScrapingService $scrapingService)
    {
        $this->scrapingService = $scrapingService;
    }

    /**
     * Display a listing of shipments (user's shipments only).
     */
    public function index(Request $request): JsonResponse
    {
        $query = Shipment::with('warehouse')->where('user_id', $request->user()->id);

        // Filter by status
        if ($request->has('status')) {
            $query->where('status', $request->get('status'));
        }

        // Filter by warehouse
        if ($request->has('warehouse_id')) {
            $query->where('warehouse_id', $request->get('warehouse_id'));
        }

        // Search by tracking number
        if ($request->has('tracking_number')) {
            $query->where('tracking_number', 'like', '%' . $request->get('tracking_number') . '%');
        }

        // Date range filter
        if ($request->has('date_from')) {
            $query->whereDate('created_at', '>=', $request->get('date_from'));
        }

        if ($request->has('date_to')) {
            $query->whereDate('created_at', '<=', $request->get('date_to'));
        }

        $shipments = $query->orderBy('created_at', 'desc')->paginate(20);

        return response()->json([
            'success' => true,
            'data' => $shipments
        ]);
    }

    /**
     * Store a newly created shipment.
     */
    public function store(Request $request): JsonResponse
    {
        $request->validate([
            'tracking_number' => 'required|string|max:100',
            'reference_number' => 'nullable|string|max:100',
            'warehouse_id' => 'nullable|exists:warehouses,id',
            'status' => 'sometimes|string|in:pending,in_transit,delivered,exception',
            'origin_address' => 'nullable|string',
            'destination_address' => 'nullable|string',
            'wrh' => 'nullable|string',
            'value' => 'nullable|numeric|min:0',
            'currency' => 'sometimes|string|max:3',
            'description' => 'nullable|string',
            'carrier' => 'nullable|string|max:100',
            'service_type' => 'nullable|string|max:100',
        ]);

        $data = $request->all();
        $data['user_id'] = $request->user()->id;

        $shipment = Shipment::create($data);

        return response()->json([
            'success' => true,
            'message' => 'Shipment created successfully',
            'data' => $shipment->load('warehouse')
        ], 201);
    }

    /**
     * Display the specified shipment.
     */
    public function show(Request $request, string $id): JsonResponse
    {
        $shipment = Shipment::with('warehouse')
            ->where('id', $id)
            ->where('user_id', $request->user()->id)
            ->firstOrFail();

        return response()->json([
            'success' => true,
            'data' => $shipment
        ]);
    }

    /**
     * Update the specified shipment.
     */
    public function update(Request $request, string $id): JsonResponse
    {
        $shipment = Shipment::where('id', $id)
            ->where('user_id', $request->user()->id)
            ->firstOrFail();

        $request->validate([
            'tracking_number' => 'sometimes|string|max:100',
            'reference_number' => 'nullable|string|max:100',
            'warehouse_id' => 'nullable|exists:warehouses,id',
            'status' => 'sometimes|string|in:pending,in_transit,delivered,exception',
            'origin_address' => 'nullable|string',
            'destination_address' => 'nullable|string',
            'wrh' => 'nullable|string',
            'value' => 'nullable|numeric|min:0',
            'currency' => 'sometimes|string|max:3',
            'description' => 'nullable|string',
            'carrier' => 'nullable|string|max:100',
            'service_type' => 'nullable|string|max:100',
        ]);

        $shipment->update($request->all());

        return response()->json([
            'success' => true,
            'message' => 'Shipment updated successfully',
            'data' => $shipment->load('warehouse')
        ]);
    }

    /**
     * Remove the specified shipment.
     */
    public function destroy(Request $request, string $id): JsonResponse
    {
        $shipment = Shipment::where('id', $id)
            ->where('user_id', $request->user()->id)
            ->firstOrFail();
        
        $shipment->delete();

        return response()->json([
            'success' => true,
            'message' => 'Shipment deleted successfully'
        ]);
    }

    /**
     * Get shipment by tracking number (user's shipments only).
     */
    public function track(Request $request, string $trackingNumber): JsonResponse
    {
        $shipment = Shipment::with('warehouse')
            ->where('tracking_number', $trackingNumber)
            ->where('user_id', $request->user()->id)
            ->first();

        if (!$shipment) {
            return response()->json([
                'success' => false,
                'message' => 'Shipment not found'
            ], 404);
        }

        return response()->json([
            'success' => true,
            'data' => $shipment
        ]);
    }

    /**
     * Track and save shipment by scraping
     */
    public function trackAndSave(Request $request): JsonResponse
    {
        $request->validate([
            'tracking_number' => 'required|string|max:255'
        ]);

        try {
            // Scrape the shipment data
            $shipmentData = $this->scrapingService->scrapeSingleShipment($request->tracking_number);
            
            if (!$shipmentData) {
                return response()->json([
                    'success' => false,
                    'message' => 'No se pudo obtener información del paquete o el número de tracking es inválido.'
                ], 404);
            }

            // Check if shipment already exists for this user
            $existingShipment = Shipment::where('tracking_number', $request->tracking_number)
                ->where('user_id', $request->user()->id)
                ->first();

            if ($existingShipment) {
                // Update existing shipment
                $existingShipment->update([
                    'status' => $shipmentData['status'],
                    'wrh' => $shipmentData['wrh'],
                    'description' => $shipmentData['description'],
                    'carrier' => $shipmentData['carrier'],
                    'pickup_date' => $shipmentData['pickup_date'],
                    'delivery_date' => $shipmentData['delivery_date'],
                    'tracking_events' => $shipmentData['tracking_events'],
                ]);

                return response()->json([
                    'success' => true,
                    'message' => 'Shipment updated successfully',
                    'data' => $existingShipment
                ]);
            }

            // Create new shipment
            $shipment = Shipment::create([
                'tracking_number' => $request->tracking_number,
                'user_id' => $request->user()->id,
                'status' => $shipmentData['status'],
                'wrh' => $shipmentData['wrh'],
                'description' => $shipmentData['description'],
                'carrier' => $shipmentData['carrier'],
                'pickup_date' => $shipmentData['pickup_date'],
                'delivery_date' => $shipmentData['delivery_date'],
                'tracking_events' => $shipmentData['tracking_events'],
            ]);

            return response()->json([
                'success' => true,
                'message' => 'Shipment tracked and saved successfully',
                'data' => $shipment
            ], 201);

        } catch (\Exception $e) {
            return response()->json([
                'success' => false,
                'message' => 'Failed to track shipment',
                'error' => $e->getMessage()
            ], 500);
        }
    }
}
