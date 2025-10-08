<?php

namespace Tests\Support\Factories;

use Illuminate\Support\Collection;

abstract class Factory
{
    protected int $count = 1;

    protected array $attributes = [];

    protected array $sequence = [];

    public static function new(): static
    {
        return new static;
    }

    public function count(int $count): static
    {
        $this->count = $count;

        return $this;
    }

    public function sequence(array $sequence): static
    {
        $this->sequence = $sequence;

        return $this;
    }

    public function create(int $count = 1, array $attributes = []): mixed
    {
        $this->count = $count;
        $this->attributes = array_merge($this->attributes, $attributes);

        return collect(range(1, $this->count ?? $count))
            ->map(function (int $quantity): mixed {
                $attributes = $this->attributes;

                if (isset($this->sequence[$quantity - 1])) {
                    $attributes = array_merge($attributes, $this->sequence[$quantity - 1]);
                }

                return $this->make($attributes);
            })
            ->when($this->count === 1, fn (Collection $items) => $items->first());
    }

    abstract protected function make(array $attributes): mixed;
}

