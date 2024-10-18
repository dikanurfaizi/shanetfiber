<?php

namespace App\Http\Resources;

use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;

class FirewallAddressListResource extends JsonResource
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
            'list' => $this['list'],
            'address' => $this['address'],
            'disabled' => $this['disabled'],

        ];
    }
}
