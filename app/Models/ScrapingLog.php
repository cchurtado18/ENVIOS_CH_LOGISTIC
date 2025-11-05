<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class ScrapingLog extends Model
{
    protected $fillable = [
        'source_url',
        'scraper_type',
        'status',
        'records_found',
        'records_processed',
        'records_errors',
        'error_message',
        'response_data',
        'response_time_ms',
        'user_agent',
        'ip_address',
        'started_at',
        'completed_at',
    ];

    protected $casts = [
        'records_found' => 'integer',
        'records_processed' => 'integer',
        'records_errors' => 'integer',
        'response_time_ms' => 'integer',
        'started_at' => 'datetime',
        'completed_at' => 'datetime',
        'response_data' => 'array',
    ];
}
