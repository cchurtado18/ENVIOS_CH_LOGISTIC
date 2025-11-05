<?php

namespace App\Services;

use App\Models\Warehouse;
use App\Models\Shipment;
use App\Models\ScrapingLog;
use GuzzleHttp\Client;
use GuzzleHttp\Cookie\CookieJar;
use GuzzleHttp\Exception\RequestException;
use Symfony\Component\DomCrawler\Crawler;
use Illuminate\Support\Facades\Log;
use Carbon\Carbon;

class EverestScrapingService
{
    protected $client;
    protected $baseUrl;
    protected $userAgent;
    protected $timeout;

    public function __construct()
    {
        $this->client = new Client([
            'timeout' => 60, // Aumentado a 60 segundos
            'connect_timeout' => 30,
            'verify' => false, // Para sitios con certificados SSL problemáticos
        ]);
        
        $this->baseUrl = 'https://everest.cargotrack.net/';
        $this->userAgent = config('scraping.user_agent', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36');
        $this->timeout = 60;
    }

    /**
     * Scrape warehouse data from Everest CargoTrack
     */
    public function scrapeWarehouses(): array
    {
        $log = $this->startScrapingLog('everest_warehouse', $this->baseUrl . 'warehouse.asp');
        
        try {
            $response = $this->makeRequest('warehouse.asp');
            $crawler = new Crawler($response);
            
            $warehouses = $this->extractWarehouseData($crawler);
            
            $this->saveWarehouses($warehouses);
            
            $this->completeScrapingLog($log, 'success', count($warehouses), count($warehouses), 0);
            
            return $warehouses;
            
        } catch (\Exception $e) {
            $this->completeScrapingLog($log, 'failed', 0, 0, 1, $e->getMessage());
            Log::error('Everest warehouse scraping failed: ' . $e->getMessage());
            throw $e;
        }
    }

    /**
     * Scrape shipment data from Everest CargoTrack
     */
    public function scrapeShipments(array $trackingNumbers = []): array
    {
        $log = $this->startScrapingLog('everest_shipment', $this->baseUrl . 'm/track.asp');
        
        try {
            $shipments = [];
            
            if (empty($trackingNumbers)) {
                // Si no se proporcionan números de tracking, intentar obtener una lista
                $trackingNumbers = $this->getAvailableTrackingNumbers();
            }
            
            foreach ($trackingNumbers as $trackingNumber) {
                $shipment = $this->scrapeSingleShipment($trackingNumber);
                if ($shipment) {
                    $shipments[] = $shipment;
                }
            }
            
            $this->saveShipments($shipments);
            
            $this->completeScrapingLog($log, 'success', count($shipments), count($shipments), 0);
            
            return $shipments;
            
        } catch (\Exception $e) {
            $this->completeScrapingLog($log, 'failed', 0, 0, 1, $e->getMessage());
            Log::error('Everest shipment scraping failed: ' . $e->getMessage());
            throw $e;
        }
    }

    /**
     * Scrape a single shipment by tracking number
     */
    public function scrapeSingleShipment(string $trackingNumber): ?array
    {
        // Increase execution time for web scraping
        set_time_limit(120);
        
        try {
            // Use the exact URL that works from manual testing
            $url = "https://everest.cargotrack.net/m/track.asp?track={$trackingNumber}&action2=process";
            
            $response = $this->client->get($url, [
                'headers' => [
                    'User-Agent' => $this->userAgent,
                    'Accept' => 'text/html,application/xhtml+xml,application/xml;q=0.9,*/*;q=0.8',
                    'Accept-Language' => 'en-US,en;q=0.5',
                    'Accept-Encoding' => 'gzip, deflate',
                    'Connection' => 'keep-alive',
                ],
                'allow_redirects' => true,
                'cookies' => new CookieJar(),
            ]);
            
            $html = $response->getBody()->getContents();
            $crawler = new Crawler($html);
            
            // Check if we have the data we need
            $mainInfo = $crawler->filter('table.insert')->first();
            if ($mainInfo->count() === 0) {
                Log::warning("No table.insert found in response for tracking: {$trackingNumber}");
                return null;
            }
            
            $shipment = $this->extractShipmentData($crawler, $trackingNumber, $html);
            
            return $shipment;
            
        } catch (\Exception $e) {
            Log::error("Failed to scrape shipment {$trackingNumber}: " . $e->getMessage());
            return null;
        }
    }

    /**
     * Public tracking method - returns data without saving to database
     * Perfect for external API access
     */
    public function scrapeSingleShipmentPublic(string $trackingNumber): ?array
    {
        return $this->scrapeSingleShipment($trackingNumber);
    }

    /**
     * Make HTTP request to Everest CargoTrack
     */
    protected function makeRequest(string $endpoint, array $params = [], array $postData = []): string
    {
        $url = $this->baseUrl . $endpoint;
        
        if (!empty($params)) {
            $url .= '?' . http_build_query($params);
        }
        
        $options = [
            'headers' => [
                'User-Agent' => $this->userAgent,
                'Accept' => 'text/html,application/xhtml+xml,application/xml;q=0.9,*/*;q=0.8',
                'Accept-Language' => 'en-US,en;q=0.5',
                'Accept-Encoding' => 'gzip, deflate',
                'Connection' => 'keep-alive',
                'Content-Type' => 'application/x-www-form-urlencoded',
            ],
            'allow_redirects' => true,
            'cookies' => new CookieJar(),
        ];
        
        if (!empty($postData)) {
            $options['form_params'] = $postData;
            $response = $this->client->post($url, $options);
        } else {
            $response = $this->client->get($url, $options);
        }
        
        return $response->getBody()->getContents();
    }

    /**
     * Extract warehouse data from HTML
     */
    protected function extractWarehouseData(Crawler $crawler): array
    {
        $warehouses = [];
        
        // Buscar tablas o divs que contengan información de almacenes
        $crawler->filter('table, .warehouse-item, .facility-item')->each(function (Crawler $node) use (&$warehouses) {
            $warehouse = [
                'name' => $this->extractText($node, '.name, .warehouse-name, td:nth-child(1)'),
                'code' => $this->extractText($node, '.code, .warehouse-code, td:nth-child(2)'),
                'address' => $this->extractText($node, '.address, .warehouse-address, td:nth-child(3)'),
                'city' => $this->extractText($node, '.city, .warehouse-city, td:nth-child(4)'),
                'phone' => $this->extractText($node, '.phone, .warehouse-phone, td:nth-child(5)'),
                'email' => $this->extractText($node, '.email, .warehouse-email, td:nth-child(6)'),
            ];
            
            if (!empty($warehouse['name'])) {
                $warehouses[] = $warehouse;
            }
        });
        
        return $warehouses;
    }

    /**
     * Extract shipment data from HTML
     */
    protected function extractShipmentData(Crawler $crawler, string $trackingNumber, string $html): ?array
    {
        // Use the HTML passed as parameter instead of getting it from crawler
        
        // Extract data using direct regex patterns from the complete HTML
        $wrh = $this->extractWRHFromHtml($html);
        $description = $this->extractDescriptionFromHtml($html);
        $pickupDate = $this->extractPickupDateFromHtml($html);
        $trackingEvents = $this->extractTrackingEventsFromHtml($html);
        
        // Determine status from tracking events (more accurate than just looking for "ENTREGADO")
        $status = $this->extractStatusFromTrackingEvents($trackingEvents);
        
        $weight = $this->extractWeightFromHtml($html);
        
        $shipment = [
            'tracking_number' => $trackingNumber,
            'status' => $status,
            'wrh' => $wrh ?? 'pendiente',
            'weight' => $weight,
            'weight_unit' => $weight ? 'lbs' : 'lbs',
            'description' => $description,
            'carrier' => 'CH Logistics',
            'pickup_date' => $pickupDate,
            'delivery_date' => $this->extractDeliveryDateFromEvents($trackingEvents),
            'tracking_events' => $trackingEvents,
        ];
        
        return $shipment;
    }

    /**
     * Extract tracking events from HTML
     */
    protected function extractTrackingEvents(Crawler $crawler): array
    {
        $events = [];
        
        // Look for tracking events in tables with class "ntextrow"
        $crawler->filter('td.ntextrow')->each(function (Crawler $node) use (&$events) {
            $text = trim($node->text());
            
            // Parse the event text to extract date, time, and status
            if (preg_match('/(\d{2}\/\d{2}\/\d{4})\s+(\d{1,2}:\d{2}:\d{2}\s+[AP]M)\s+(.+)/', $text, $matches)) {
                $event = [
                    'date' => $matches[1],
                    'time' => $matches[2],
                    'status' => trim($matches[3]),
                    'description' => trim($matches[3]),
                ];
                
                $events[] = $event;
            }
        });
        
        // If no events found with ntextrow, try alternative approach
        if (empty($events)) {
            $crawler->filter('td')->each(function (Crawler $node) use (&$events) {
                $text = trim($node->text());
                
                // Look for patterns like "Delivered 10/17/2025 2:38:27 PM"
                if (preg_match('/([A-Za-z\s]+)\s+(\d{2}\/\d{2}\/\d{4})\s+(\d{1,2}:\d{2}:\d{2}\s+[AP]M)/', $text, $matches)) {
                    $event = [
                        'date' => $matches[2],
                        'time' => $matches[3],
                        'status' => trim($matches[1]),
                        'description' => trim($matches[1]),
                    ];
                    
                    $events[] = $event;
                }
            });
        }
        
        // If still no events, try searching in all text content
        if (empty($events)) {
            $allText = $crawler->text();
            
            // Look for patterns like "Delivered 10/17/2025 2:38:27 PM"
            if (preg_match_all('/([A-Za-z\s]+)\s+(\d{2}\/\d{2}\/\d{4})\s+(\d{1,2}:\d{2}:\d{2}\s+[AP]M)/', $allText, $matches, PREG_SET_ORDER)) {
                foreach ($matches as $match) {
                    $event = [
                        'date' => $match[2],
                        'time' => $match[3],
                        'status' => trim($match[1]),
                        'description' => trim($match[1]),
                    ];
                    
                    $events[] = $event;
                }
            }
        }
        
        // If still no events, try searching in HTML content directly
        if (empty($events)) {
            $html = $crawler->html();
            
            // Look for patterns in HTML like "Delivered<br>10/17/2025&nbsp;2:38:27 PM"
            if (preg_match_all('/([A-Za-z\s]+)<br>\s*<span class="ntext">(\d{2}\/\d{2}\/\d{4})&nbsp;(\d{1,2}:\d{2}:\d{2}\s+[AP]M)<\/span>/', $html, $matches, PREG_SET_ORDER)) {
                foreach ($matches as $match) {
                    $event = [
                        'date' => $match[2],
                        'time' => $match[3],
                        'status' => trim($match[1]),
                        'description' => trim($match[1]),
                    ];
                    
                    $events[] = $event;
                }
            }
        }
        
        // If still no events, try searching for the specific pattern we see
        if (empty($events)) {
            $html = $crawler->html();
            
            // Look for patterns like "Delivered<br><span class="ntext">10/17/2025&nbsp;2:38:27 PM</span>"
            if (preg_match_all('/([A-Za-z\s]+)<br>\s*<span class="ntext">(\d{2}\/\d{2}\/\d{4})&nbsp;(\d{1,2}:\d{2}:\d{2}\s+[AP]M)<\/span>/', $html, $matches, PREG_SET_ORDER)) {
                foreach ($matches as $match) {
                    $event = [
                        'date' => $match[2],
                        'time' => $match[3],
                        'status' => trim($match[1]),
                        'description' => trim($match[1]),
                    ];
                    
                    $events[] = $event;
                }
            }
        }
        
        return $events;
    }

    /**
     * Save warehouses to database
     */
    protected function saveWarehouses(array $warehouses): void
    {
        foreach ($warehouses as $warehouseData) {
            Warehouse::updateOrCreate(
                ['code' => $warehouseData['code']],
                array_merge($warehouseData, [
                    'metadata' => json_encode($warehouseData),
                    'is_active' => true,
                ])
            );
        }
    }

    /**
     * Save shipments to database
     */
    protected function saveShipments(array $shipments): void
    {
        foreach ($shipments as $shipmentData) {
            // Buscar o crear un warehouse por defecto
            $warehouse = Warehouse::first();
            if (!$warehouse) {
                $warehouse = Warehouse::create([
                    'name' => 'Default Warehouse',
                    'code' => 'DEFAULT',
                    'is_active' => true,
                ]);
            }
            
            Shipment::updateOrCreate(
                ['tracking_number' => $shipmentData['tracking_number']],
                array_merge($shipmentData, [
                    'warehouse_id' => $warehouse->id,
                    'metadata' => json_encode($shipmentData),
                ])
            );
        }
    }

    /**
     * Extract weight from HTML content
     */
    protected function extractWeightFromHtml(string $html): ?float
    {
        // Look for pattern like "0.6&nbsp;lbs" (with HTML entities) - decode HTML entities first
        $decodedHtml = html_entity_decode($html, ENT_QUOTES | ENT_HTML5, 'UTF-8');
        
        // Try with original HTML first
        if (preg_match('/(\d+\.?\d*)&nbsp;lbs/', $html, $matches)) {
            return (float) $matches[1];
        }
        
        // Try with decoded HTML
        if (preg_match('/(\d+\.?\d*)\s+lbs/', $decodedHtml, $matches)) {
            return (float) $matches[1];
        }
        
        // Try with original HTML but different pattern
        if (preg_match('/(\d+\.?\d*)\s+lbs/', $html, $matches)) {
            return (float) $matches[1];
        }
        
        return null;
    }

    /**
     * Extract description from HTML content
     */
    protected function extractDescriptionFromHtml(string $html): ?string
    {
        // Look for pattern like "1 bulto(s) con 0.6 lbs"
        if (preg_match('/(\d+)\s+bulto\(s\)\s+con\s+(\d+\.?\d*)\s+lbs/', $html, $matches)) {
            return "{$matches[1]} bulto(s) con {$matches[2]} lbs";
        }
        
        // Look for pattern with HTML entities
        if (preg_match('/(\d+)\s+bulto\(s\)\s+con\s+(\d+\.?\d*)&nbsp;lbs/', $html, $matches)) {
            return "{$matches[1]} bulto(s) con {$matches[2]} lbs";
        }
        
        // Look for pattern with HTML entities and different spacing
        if (preg_match('/(\d+)&nbsp;bulto\(s\)&nbsp;con&nbsp;(\d+\.?\d*)&nbsp;lbs/', $html, $matches)) {
            return "{$matches[1]} bulto(s) con {$matches[2]} lbs";
        }
        
        return null;
    }

    /**
     * Extract WRH from HTML content
     * Pattern: <span class="ntextbig"><strong>731138</strong></span>
     */
    protected function extractWRHFromHtml(string $html): ?string
    {
        // Try pattern with strong tag inside span
        if (preg_match('/<span[^>]*class="ntextbig"[^>]*><strong>(\d+)<\/strong><\/span>/i', $html, $matches)) {
            return $matches[1];
        }
        
        // Try pattern without strong tag
        if (preg_match('/<span[^>]*class="ntextbig"[^>]*>(\d+)<\/span>/i', $html, $matches)) {
            return $matches[1];
        }
        
        // Try pattern after "Recibido el" date - the number between date and tracking number
        if (preg_match('/Recibido\s+el[^>]*<strong>[^<]+<\/strong>[^<]*<span[^>]*class="ntextbig"[^>]*><strong>(\d+)<\/strong><\/span>/i', $html, $matches)) {
            return $matches[1];
        }
        
        return null;
    }

    /**
     * Extract pickup date from text content
     */
    protected function extractPickupDateFromText(string $text): ?string
    {
        if (preg_match('/(\d{2}\/\d{2}\/\d{4})\s+a\s+las\s+(\d{1,2}:\d{2}:\d{2}\s+[AP]M)/', $text, $matches)) {
            try {
                return Carbon::createFromFormat('m/d/Y g:i:s A', $matches[1] . ' ' . $matches[2])->toDateTimeString();
            } catch (\Exception $e) {
                return null;
            }
        }
        return null;
    }

    /**
     * Extract pickup date from main info
     */
    protected function extractPickupDate(Crawler $mainInfo): ?string
    {
        $text = $this->extractText($mainInfo, 'td.ntext');
        if ($text && preg_match('/(\d{2}\/\d{2}\/\d{4})\s+a\s+las\s+(\d{1,2}:\d{2}:\d{2}\s+[AP]M)/', $text, $matches)) {
            try {
                return Carbon::createFromFormat('m/d/Y g:i:s A', $matches[1] . ' ' . $matches[2])->toDateTimeString();
            } catch (\Exception $e) {
                return null;
            }
        }
        
        // Try alternative selector
        $text = $this->extractText($mainInfo, 'td');
        if ($text && preg_match('/(\d{2}\/\d{2}\/\d{4})\s+a\s+las\s+(\d{1,2}:\d{2}:\d{2}\s+[AP]M)/', $text, $matches)) {
            try {
                return Carbon::createFromFormat('m/d/Y g:i:s A', $matches[1] . ' ' . $matches[2])->toDateTimeString();
            } catch (\Exception $e) {
                return null;
            }
        }
        
        return null;
    }

    /**
     * Extract delivery date from tracking events
     */
    protected function extractDeliveryDate(Crawler $crawler): ?string
    {
        $events = $this->extractTrackingEvents($crawler);
        foreach ($events as $event) {
            if (stripos($event['status'], 'delivered') !== false) {
                try {
                    return Carbon::createFromFormat('m/d/Y g:i:s A', $event['date'] . ' ' . $event['time'])->toDateTimeString();
                } catch (\Exception $e) {
                    return null;
                }
            }
        }
        return null;
    }

    /**
     * Extract status from tracking events (first event is usually the current status)
     */
    protected function extractStatusFromEvents(Crawler $crawler): string
    {
        $events = $this->extractTrackingEvents($crawler);
        if (!empty($events)) {
            $firstEvent = $events[0];
            $status = strtolower($firstEvent['status']);
            
            if (stripos($status, 'delivered') !== false) {
                return 'delivered';
            } elseif (stripos($status, 'recibido') !== false) {
                return 'in_transit';
            } else {
                return 'in_transit';
            }
        }
        
        return 'pending';
    }

    /**
     * Extract status from tracking events array
     */
    protected function extractStatusFromTrackingEvents(array $events): string
    {
        if (!empty($events)) {
            $firstEvent = $events[0];
            $status = strtolower($firstEvent['status']);
            
            if (stripos($status, 'delivered') !== false || stripos($status, 'entregado') !== false) {
                return 'delivered';
            } elseif (stripos($status, 'recibido') !== false || stripos($status, 'received') !== false) {
                return 'in_transit';
            } else {
                return 'in_transit';
            }
        }
        
        return 'pending';
    }

    /**
     * Helper methods for data extraction
     */
    protected function extractText(Crawler $node, string $selector): ?string
    {
        try {
            $text = $node->filter($selector)->first()->text();
            return trim($text) ?: null;
        } catch (\Exception $e) {
            return null;
        }
    }

    protected function extractNumeric(Crawler $node, string $selector): ?float
    {
        $text = $this->extractText($node, $selector);
        if ($text) {
            return (float) preg_replace('/[^0-9.]/', '', $text);
        }
        return null;
    }

    protected function extractDate(Crawler $node, string $selector): ?string
    {
        $text = $this->extractText($node, $selector);
        if ($text) {
            try {
                return Carbon::parse($text)->toDateTimeString();
            } catch (\Exception $e) {
                return null;
            }
        }
        return null;
    }

    protected function getAvailableTrackingNumbers(): array
    {
        // Implementar lógica para obtener números de tracking disponibles
        // Esto dependerá de la estructura específica del sitio
        return [];
    }

    /**
     * Logging methods
     */
    protected function startScrapingLog(string $type, string $url): ScrapingLog
    {
        return ScrapingLog::create([
            'source_url' => $url,
            'scraper_type' => $type,
            'status' => 'running',
            'started_at' => now(),
            'user_agent' => $this->userAgent,
        ]);
    }

    protected function completeScrapingLog(ScrapingLog $log, string $status, int $found, int $processed, int $errors, ?string $errorMessage = null): void
    {
        $log->update([
            'status' => $status,
            'records_found' => $found,
            'records_processed' => $processed,
            'records_errors' => $errors,
            'error_message' => $errorMessage,
            'completed_at' => now(),
        ]);
    }

    /**
     * Extract pickup date from HTML content
     * Looks for pattern like "Recibido el 10/15/2025 a las 1:09:00 PM"
     */
    protected function extractPickupDateFromHtml(string $html): ?string
    {
        // Decode HTML entities first to make pattern matching easier
        $decodedHtml = html_entity_decode($html, ENT_QUOTES | ENT_HTML5, 'UTF-8');
        
        // Try pattern with "Recibido el" and <strong> tags - exact pattern from HTML
        // Pattern: Recibido el&nbsp;<strong>10/15/2025</strong>&nbsp;a las&nbsp;<strong>1:09:00 PM</strong>
        if (preg_match('/Recibido\s+el[^>]*<strong>(\d{2}\/\d{2}\/\d{4})<\/strong>[^>]*a\s+las[^>]*<strong>(\d{1,2}:\d{2}:\d{2}\s+[AP]M)<\/strong>/i', $html, $matches)) {
            try {
                return Carbon::createFromFormat('m/d/Y g:i:s A', $matches[1] . ' ' . $matches[2])->toDateTimeString();
            } catch (\Exception $e) {
                return null;
            }
        }
        
        // Try pattern with "Recibido el" - most specific pattern (case insensitive)
        if (preg_match('/Recibido\s+el\s+(\d{2}\/\d{2}\/\d{4})\s+a\s+las\s+(\d{1,2}:\d{2}:\d{2}\s+[AP]M)/i', $decodedHtml, $matches)) {
            try {
                return Carbon::createFromFormat('m/d/Y g:i:s A', $matches[1] . ' ' . $matches[2])->toDateTimeString();
            } catch (\Exception $e) {
                return null;
            }
        }
        
        // Try pattern in original HTML with HTML entities
        if (preg_match('/Recibido\s+el\s+(\d{2}\/\d{2}\/\d{4})&nbsp;a&nbsp;las&nbsp;(\d{1,2}:\d{2}:\d{2}\s+[AP]M)/i', $html, $matches)) {
            try {
                return Carbon::createFromFormat('m/d/Y g:i:s A', $matches[1] . ' ' . $matches[2])->toDateTimeString();
            } catch (\Exception $e) {
                return null;
            }
        }
        
        // Try simpler pattern without "Recibido el" - fallback
        if (preg_match('/(\d{2}\/\d{2}\/\d{4})\s+a\s+las\s+(\d{1,2}:\d{2}:\d{2}\s+[AP]M)/i', $decodedHtml, $matches)) {
            try {
                return Carbon::createFromFormat('m/d/Y g:i:s A', $matches[1] . ' ' . $matches[2])->toDateTimeString();
            } catch (\Exception $e) {
                return null;
            }
        }
        
        return null;
    }

    /**
     * Extract tracking events from HTML content
     */
    protected function extractTrackingEventsFromHtml(string $html): array
    {
        $events = [];
        
        // Look for patterns like "Delivered<br><span class="ntext">10/17/2025&nbsp;2:38:27 PM</span>"
        if (preg_match_all('/([A-Za-z\s]+)<br>\s*<span class="ntext">(\d{2}\/\d{2}\/\d{4})&nbsp;(\d{1,2}:\d{2}:\d{2}\s+[AP]M)<\/span>/', $html, $matches, PREG_SET_ORDER)) {
            foreach ($matches as $match) {
                $event = [
                    'date' => $match[2],
                    'time' => $match[3],
                    'status' => trim($match[1]),
                    'description' => trim($match[1]),
                ];
                
                $events[] = $event;
            }
        }
        
        return $events;
    }

    /**
     * Extract delivery date from tracking events
     */
    protected function extractDeliveryDateFromEvents(array $events): ?string
    {
        foreach ($events as $event) {
            if (stripos($event['status'], 'delivered') !== false) {
                try {
                    return Carbon::createFromFormat('m/d/Y g:i:s A', $event['date'] . ' ' . $event['time'])->toDateTimeString();
                } catch (\Exception $e) {
                    return null;
                }
            }
        }
        return null;
    }
}
