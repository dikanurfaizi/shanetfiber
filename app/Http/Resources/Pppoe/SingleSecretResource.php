<?php

namespace App\Http\Resources\Pppoe;

use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;

class SingleSecretResource extends JsonResource
{
    /**
     * Transform the resource into an array.
     *
     * @return array<string, mixed>
     */
    public function getSingleSecret(Request $request)
    {
        return [
            'id' => $this['.id'],
            'name' => $this['name'],
            'password' => $this['password'] ?? null,
            'profile' => $this['profile'] ?? null,
            'local-address' => $this['local-address'] ?? null,
            'remote-address' => $this['remote-address'] ?? null,
            'last-logged-out' => $this['last-logged-out'] ?? null,
            'disabled' => $this['disabled'] ?? null,
        ];
    }
}
