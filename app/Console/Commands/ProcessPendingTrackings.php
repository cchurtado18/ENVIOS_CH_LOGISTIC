<?php

namespace App\Console\Commands;

use Illuminate\Console\Command;
use App\Models\PendingTracking;
use App\Models\Shipment;
use App\Services\EverestScrapingService;
use App\Notifications\PendingTrackingFound;
use Illuminate\Support\Facades\Log;

class ProcessPendingTrackings extends Command
{
    protected $scrapingService;

    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'shipments:process-pending';

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = 'Process pending tracking numbers and create shipments when found';

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
        $this->info('Processing pending trackings...');
        
        // Get pending trackings older than 1 minute (to avoid immediate retry)
        $pendingTrackings = PendingTracking::where('status', 'waiting')
            ->where('created_at', '<=', now()->subMinute())
            ->get();
        
        $foundCount = 0;
        $updatedCount = 0;
        
        foreach ($pendingTrackings as $pending) {
            try {
                // Increment attempts
                $pending->increment('attempts');
                
                // Scrape the tracking
                $shipmentData = $this->scrapingService->scrapeSingleShipment($pending->tracking_number);
                
                if ($shipmentData) {
                    // Found! Create the shipment
                    $warehouse = \App\Models\Warehouse::firstOrCreate(
                        ['code' => 'EVEREST'],
                        ['name' => 'Everest CargoTrack', 'is_active' => true]
                    );
                    
                    $shipment = Shipment::create(array_merge($shipmentData, [
                        'user_id' => $pending->user_id,
                        'warehouse_id' => $warehouse->id,
                    ]));
                    
                    // Mark pending as found
                    $pending->update([
                        'status' => 'found',
                        'found_at' => now(),
                    ]);
                    
                    // Send notification to user
                    $pending->user->notify(new PendingTrackingFound($shipment));
                    
                    $foundCount++;
                    $this->info("✅ Found: {$pending->tracking_number} - Created shipment for user {$pending->user->email}");
                    $this->info("  → Notification sent to {$pending->user->email}");
                    
                } else {
                    // Not found yet - check if should give up
                    // If more than 48 hours old, delete it
                    if ($pending->created_at < now()->subHours(48)) {
                        $pending->delete();
                        $this->warn("⏰ Deleted: {$pending->tracking_number} (48h without data)");
                    }
                }
                
                // Add delay to avoid rate limiting
                sleep(1);
                
            } catch (\Exception $e) {
                $this->error("Error processing {$pending->tracking_number}: {$e->getMessage()}");
                Log::error("Failed to process pending tracking", [
                    'tracking_number' => $pending->tracking_number,
                    'error' => $e->getMessage()
                ]);
            }
        }
        
        $this->info("✅ Complete: {$foundCount} found, {$pendingTrackings->count()} processed");
        return Command::SUCCESS;
    }
}
