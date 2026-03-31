<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class ProcessedWebhook extends Model
{
    protected $fillable = [
        'provider',
        'event_id',
        'event_type',
        'payload_hash',
        'status',
        'olympiad_request_id',
        'payment_record_id',
        'received_at',
        'processed_at',
    ];

    protected $casts = [
        'received_at' => 'datetime',
        'processed_at' => 'datetime',
    ];

    public function olympiadRequest()
    {
        return $this->belongsTo(OlympiadRequest::class);
    }

    public function paymentRecord()
    {
        return $this->belongsTo(PaymentRecord::class);
    }
}
