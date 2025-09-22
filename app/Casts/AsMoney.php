<?php

namespace App\Casts;

use Brick\Math\BigNumber;
use Brick\Math\RoundingMode;
use Brick\Money\Money;
use Illuminate\Contracts\Database\Eloquent\CastsAttributes;
use Illuminate\Database\Eloquent\Model;

class AsMoney implements CastsAttributes
{
    public function get(Model $model, string $key, mixed $value, array $attributes): ?Money
    {
        return money()
            ->ofMinor($this->normalize($value), currency: 'BRL', roundingMode: RoundingMode::HALF_EVEN);
    }

    public function set(Model $model, string $key, mixed $value, array $attributes): ?int
    {
        if ($value instanceof Money) {
            return $value
                ->getMinorAmount()
                ->toInt();
        }

        return Money::of($this->normalize($value), currency: 'BRL', roundingMode: RoundingMode::HALF_EVEN)
            ->getMinorAmount()
            ->toInt();
    }

    private function normalize(BigNumber|float|int|string $amount): BigNumber|float|int|string
    {
        if (! is_string($amount)) {
            return $amount;
        }

        return str($amount)
            ->replace(['.', ','], ['', '.'])
            ->value();
    }
}
