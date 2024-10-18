<?php

namespace App\Http\Resources\Pppoe;

use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;

class SecretActiveResource extends JsonResource
{
    /**
     * Transform the resource into an array.
     *
     * @return array<string, mixed>
     */
    public function toArray(Request $request): array
    {
        return [
            'id' => $this['.id'],
            'name' => $this['name'],
            'service' => $this['service'],
            'caller-id' => $this['caller-id'],
            'address' => $this['address'],
            'uptime' => $this['uptime'],
            'encoding' => $this['encoding'] ?? null,
            'session-id' => $this['session-id'] ?? null,
            'limit-bytes-in' => $this['limit-bytes-in'] ?? null,
            'limit-bytes-out' => $this['limit-bytes-out'] ?? null,
            'radius' => $this['radius'] ?? null,
            'comment' => $this['comment'] ?? null,

        ];
    }
}
