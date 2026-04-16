<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class PaymentImportRow extends Model
{
    protected $fillable = [
        'provider',
        'fingerprint',
        'external_reference',
        'amount',
        'paid_at',
        'comment',
        'raw_payload',
        'matched_payment_record_id',
        'status',
    ];

    protected $casts = [
        'amount' => 'decimal:2',
        'paid_at' => 'datetime',
        'raw_payload' => 'array',
    ];

    public function matchedPaymentRecord()
    {
        return $this->belongsTo(PaymentRecord::class, 'matched_payment_record_id');
    }
}
