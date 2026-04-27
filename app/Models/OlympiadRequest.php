<?php

namespace App\Models;

use App\Models\Concerns\HasPublicId;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class OlympiadRequest extends Model
{
    use HasFactory, HasPublicId;

    protected $fillable = [
        'public_id',
        'user_id',
        'child_profile_id',
        'subject_id',
        'first_name',
        'last_name',
        'birth_date',
        'grade',
        'language',
        'parent_name',
        'parent_phone',
        'parent_email',
        'status',
        'payment_status',
        'paid_at',
        'completed',
        'disqualified_at',
        'disqualification_reason',
        'attempt_started_at',
        'attempt_last_activity_at',
    ];

    protected $casts = [
        'completed' => 'boolean',
        'birth_date' => 'date',
        'paid_at' => 'datetime',
        'disqualified_at' => 'datetime',
        'attempt_started_at' => 'datetime',
        'attempt_last_activity_at' => 'datetime',
    ];

    protected $hidden = ['id', 'user_id', 'child_profile_id', 'subject_id'];

    public function user()
    {
        return $this->belongsTo(User::class);
    }

    public function subject()
    {
        return $this->belongsTo(Subject::class);
    }

    public function childProfile()
    {
        return $this->belongsTo(ChildProfile::class);
    }

    public function paymentRecords()
    {
        return $this->hasMany(PaymentRecord::class);
    }

    public function paymentRecord()
    {
        return $this->hasOne(PaymentRecord::class);
    }

    public function isPaymentConfirmed(): bool
    {
        return $this->payment_status === 'paid'
            && $this->paymentRecord?->reconciliation_status === 'matched';
    }

    // ── Status transition guards ──────────────────────────────────────────────

    // Allowed request status transitions
    protected const STATUS_TRANSITIONS = [
        'pending'  => ['approved', 'rejected'],
        'approved' => ['rejected'],
        'rejected' => ['approved'],
    ];

    // Allowed payment status transitions (paid is a terminal state for users)
    protected const PAYMENT_TRANSITIONS = [
        'pending' => ['paid', 'failed'],
        'failed'  => ['paid', 'pending'],
        'paid'    => [],                   // paid is terminal — no downgrades
    ];

    public function canTransitionTo(string $newStatus): bool
    {
        $allowed = self::STATUS_TRANSITIONS[$this->status] ?? [];
        return in_array($newStatus, $allowed, true);
    }

    public function canTransitionPaymentTo(string $newStatus): bool
    {
        $allowed = self::PAYMENT_TRANSITIONS[$this->payment_status] ?? [];
        return in_array($newStatus, $allowed, true);
    }

    // Allowed violation reasons — client cannot invent arbitrary strings
    public static function allowedViolationReasons(): array
    {
        return [
            'tab_hidden',        // document.visibilitychange → hidden
            'window_blur',       // window blur event
            'fullscreen_exit',   // fullscreenchange → not fullscreen
            'window_focus_lost', // legacy / generic fallback
            'tab_switch',
            'copy_paste',
            'devtools_open',
            'idle_timeout',
        ];
    }
}
