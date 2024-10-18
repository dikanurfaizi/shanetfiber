<?php

namespace App\Http\Resources\Pppoe;

use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;

class SecretResource extends JsonResource
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
            'password' => $this['password'] ?? null,
            'profile' => $this['profile'] ?? null,
            'routes' => $this['routes'] ?? null,
            'ipv6-routes' => $this['ipv6-routes'] ?? null,
            'local-address' => $this['local-address'] ?? null,
            'remote-address' => $this['remote-address'] ?? null,
            'last-logged-out' => $this['last-logged-out'] ?? null,
            'last-caller-id' => $this['last-caller-id'] ?? null,
            'last-disconnect-reason' => $this['last-disconnect-reason'] ?? null,
            'disabled' => $this['disabled'] ?? null,
            'comment' => $this['comment'] ?? null,
        ];
    }
}
