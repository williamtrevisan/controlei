<?php

namespace App\DataTransferObjects;

use Illuminate\Contracts\Support\Arrayable;

readonly class Usage implements Arrayable
{
    public function __construct(
        public int $current,
        public ?int $limit,
        public float $percentage,
    ) {}

    public function toArray(): array
    {
        return [
            'current' => $this->current,
            'limit' => $this->limit,
            'percentage' => $this->percentage,
        ];
    }
}

