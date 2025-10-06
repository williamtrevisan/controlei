<?php

namespace App\Casts;

use App\ValueObjects\StatementPeriod;
use Illuminate\Contracts\Database\Eloquent\CastsAttributes;
use Illuminate\Database\Eloquent\Model;

class AsStatementPeriod implements CastsAttributes
{
    public function get(Model $model, string $key, mixed $value, array $attributes): ?StatementPeriod
    {
        if ($value === null) {
            return null;
        }

        return new StatementPeriod($value);
    }

    public function set(Model $model, string $key, mixed $value, array $attributes): ?string
    {
        if ($value === null) {
            return null;
        }

        // Handle both string and StatementPeriod object
        if (is_string($value)) {
            return $value;
        }

        return $value->value();
    }
}
