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

            'subject' => $this->whenLoaded('subject', function () {
                return [
                    'id' => $this->subject?->id,
                    'name' => $this->subject?->name,
                ];
            }),

            'user' => [
                'id' => $this->user_id,
            ],

            'first_name' => $this->first_name,
            'last_name' => $this->last_name,
            'grade' => $this->grade,
            'language' => $this->language,

            'created_at' => $this->created_at
        ];
    }
}