<?php

namespace App\Http\Resources;

use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;
use App\Helpers\Format;

class RouterboardResource extends JsonResource
{
    /**
     * Transform the resource into an array.
     *
     * @return array<string, mixed>
     */
    public function toArray(Request $request): array
    {
        return [
            'uptime' => $this['uptime'],
            'version' => $this['version'],
            'freeMemory' => $this['free-memory'],
            'totalMemory' => $this['total-memory'],
            'cpu' => $this['cpu'],
            'cpuFrequency' => $this['cpu-frequency'],
            'cpuCount' => $this['cpu-count'],
            'cpuLoad' => $this['cpu-load'],
            'freeHdd' => $this['free-hdd-space'],
            'totalHdd' => $this['total-hdd-space'],
            'arc' => $this['architecture-name'],
            'boardName' => $this['board-name'],
            'freeMemorySpace'  => Format::byte($this['free-memory']),
            'totalMemorySpace' => Format::byte($this['total-memory']),
            'freeHddSpace'     => Format::byte($this['free-hdd-space']),
            'totalHddSpace'    => Format::byte($this['total-hdd-space']),
            'hddUsage'         => Format::byte($this['total-hdd-space'] - $this['free-hdd-space']),
            'memoryUsage'      => Format::byte($this['total-memory'] - $this['free-memory'])
        ];
    }
}
