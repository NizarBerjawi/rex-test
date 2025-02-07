<?php

namespace Domain\Contact\Resources;

use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;

class ContactResource extends JsonResource
{
    /**
     * Transform the resource into an array.
     *
     * @return array<string, mixed>
     */
    public function toArray(Request $request): array
    {
        return [
            'uuid' => $this->uuid,
            'first_name' => $this->first_name,
            'last_name' => $this->last_name,
            'created_at' => $this->created_at,
            'updated_at' => $this->updated_at,
            $this->mergeWhen(
                ! empty($this->resource->getRelations()),
                fn () => [
                    'included' => [
                        'emails' => EmailCollection::make($this->whenLoaded('emails')),
                        'phones' => PhoneCollection::make($this->whenLoaded('phones')),
                    ]]),
        ];
    }
}
