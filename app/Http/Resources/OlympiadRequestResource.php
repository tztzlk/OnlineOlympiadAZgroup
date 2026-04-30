<?php

namespace App\Http\Resources;

use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;

class OlympiadRequestResource extends JsonResource
{
    public function toArray(Request $request): array
    {
        $paymentRecord = $this->paymentRecord;

        return [
            'id' => $this->public_id,
            'payment_reference' => $this->public_id,
            'payment_comment' => trim('Заявка ' . $this->public_id . ($this->subject?->name ? ' · ' . $this->subject->name : '')),
            'status' => $this->status,
            'payment_status' => $this->payment_status,
            'reconciliation_status' => $paymentRecord?->reconciliation_status ?? 'awaiting_payment',
            'paid_at' => optional($this->paid_at)->toISOString(),
            'completed' => (bool) $this->completed,
            'disqualified_at' => optional($this->disqualified_at)->toISOString(),
            'disqualification_reason' => $this->disqualification_reason,
            'first_name' => $this->first_name,
            'last_name' => $this->last_name,
            'grade' => $this->grade,
            'language' => $this->language,
            'parent_name' => $this->parent_name,
            'parent_phone' => $this->parent_phone,
            'parent_email' => $this->parent_email,
            'created_at' => $this->created_at,
            'child_profile_id' => $this->childProfile?->public_id,
            'payment_url' => config('services.kaspi.payment_url'),
            'subject' => $this->whenLoaded('subject', function () {
                return [
                    'id' => $this->subject?->public_id,
                    'name' => $this->subject?->name,
                ];
            }),
            'child' => $this->whenLoaded('childProfile', function () {
                return [
                    'id' => $this->childProfile?->public_id,
                    'full_name' => $this->childProfile?->full_name,
                    'grade' => $this->childProfile?->grade,
                ];
            }),
            'user' => $this->whenLoaded('user', function () {
                return [
                    'id' => $this->user?->public_id,
                    'name' => $this->user?->name,
                    'email' => $this->user?->email,
                ];
            }, [
                'id' => $this->user?->public_id,
            ]),
        ];
    }
}
