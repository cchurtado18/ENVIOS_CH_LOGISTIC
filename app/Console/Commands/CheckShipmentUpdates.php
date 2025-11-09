<?php

namespace App\Console\Commands;

use Illuminate\Console\Command;
use App\Models\Shipment;
use App\Services\EverestScrapingService;
use App\Notifications\ShipmentStatusChanged;
use Illuminate\Support\Facades\Log;

class CheckShipmentUpdates extends Command
{
    protected $scrapingService;

    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'shipments:check-updates';

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = 'Check for shipment status updates and send notifications';

    public function __construct(EverestScrapingService $scrapingService)
    {
        parent::__construct();
        $this->scrapingService = $scrapingService;
    }

    /**
     * Execute the console command.
     */
    public function handle()
    {
        $this->info('Checking shipment updates...');
        
        // Get all active shipments (not delivered)
        $shipments = Shipment::where('status', '!=', 'delivered')
            ->whereNotNull('user_id')
            ->get();
        
        $updatedCount = 0;
        $notificationCount = 0;
        
        foreach ($shipments as $shipment) {
            try {
                $oldStatus = $shipment->status;
                
                // Scrape latest data
                $scrapedData = $this->scrapingService->scrapeSingleShipment($shipment->tracking_number);
                
                if (!$scrapedData) {
                    $this->warn("Could not scrape tracking: {$shipment->tracking_number}");
                    continue;
                }
                
                // Determine internal status using centralized logic
                $computedInternalStatus = Shipment::determineInternalStatusFromData($scrapedData);
                $internalStatus = $shipment->internal_status;

                if ($computedInternalStatus === Shipment::INTERNAL_STATUS_RECIBIDO_CH) {
                    $internalStatus = Shipment::INTERNAL_STATUS_RECIBIDO_CH;
                } elseif (!$internalStatus || $internalStatus === Shipment::INTERNAL_STATUS_EN_TRANSITO) {
                    $internalStatus = $computedInternalStatus;
                }
                
                // Check if status changed
                $statusChanged = $scrapedData['status'] !== $oldStatus;
                $internalStatusChanged = $internalStatus !== $shipment->internal_status;
                
                // Update shipment
                $shipment->update([
                    'status' => $scrapedData['status'],
                    'internal_status' => $internalStatus,
                    'wrh' => $scrapedData['wrh'] ?? $shipment->wrh,
                    'weight' => $scrapedData['weight'] ?? $shipment->weight,
                    'weight_unit' => $scrapedData['weight_unit'] ?? $shipment->weight_unit,
                    'description' => $scrapedData['description'] ?? $shipment->description,
                    'pickup_date' => $scrapedData['pickup_date'] ?? $shipment->pickup_date,
                    'delivery_date' => $scrapedData['delivery_date'] ?? $shipment->delivery_date,
                    'last_scan_date' => $scrapedData['last_scan_date'] ?? $shipment->last_scan_date,
                    'last_scan_location' => $scrapedData['last_scan_location'] ?? $shipment->last_scan_location,
                    'tracking_events' => $scrapedData['tracking_events'] ?? $shipment->tracking_events,
                ]);
                
                if ($statusChanged || $internalStatusChanged) {
                    $updatedCount++;
                    if ($statusChanged) {
                        $this->info("Status updated: {$shipment->tracking_number} from '{$oldStatus}' to '{$scrapedData['status']}'");
                    }
                    if ($internalStatusChanged) {
                        $this->info("  → Internal status updated to: {$internalStatus}");
                    }
                    
                    // Send notification to user if status changed to delivered
                    if ($statusChanged && $scrapedData['status'] === Shipment::STATUS_DELIVERED && $shipment->user) {
                        $shipment->user->notify(
                            new ShipmentStatusChanged($shipment, $oldStatus, $scrapedData['status'])
                        );
                        $notificationCount++;
                        $this->info("  → Notification sent to user: {$shipment->user->email}");
                    }
                }
                
                // Add delay to avoid rate limiting
                sleep(1);
                
            } catch (\Exception $e) {
                $this->error("Error processing shipment {$shipment->tracking_number}: {$e->getMessage()}");
                Log::error("Failed to check shipment update", [
                    'tracking_number' => $shipment->tracking_number,
                    'error' => $e->getMessage()
                ]);
            }
        }
        
        $this->info("✅ Check complete: {$updatedCount} shipments updated, {$notificationCount} notifications sent");
        return Command::SUCCESS;
    }
}
