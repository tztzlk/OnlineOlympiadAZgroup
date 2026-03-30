<?php

namespace App\Http\Resources;

use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;

class OlympiadRequestResource extends JsonResource
{
    public function toArray(Request $request): array
    {
        return [
            'id' => $this->id,
            'status' => $this->status,
            'payment_status' => $this->payment_status,
            'paid_at' => optional($this->paid_at)->toISOString(),
            'completed' => (bool) $this->completed,
            'first_name' => $this->first_name,
            'last_name' => $this->last_name,
            'grade' => $this->grade,
            'language' => $this->language,
            'parent_name' => $this->parent_name,
            'parent_phone' => $this->parent_phone,
            'parent_email' => $this->parent_email,
            'created_at' => $this->created_at,
            'subject' => $this->whenLoaded('subject', function () {
                return [
                    'id' => $this->subject?->id,
                    'name' => $this->subject?->name,
                ];
            }),
            'user' => $this->whenLoaded('user', function () {
                return [
                    'id' => $this->user?->id,
                    'name' => $this->user?->name,
                    'email' => $this->user?->email,
                ];
            }, [
                'id' => $this->user_id,
            ]),
        ];
    }
}
