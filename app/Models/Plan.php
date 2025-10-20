<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Support\Carbon;
use Illuminate\Support\Collection;

/**
 * @property int $id
 * @property string $name
 * @property string $slug
 * @property int $price
 * @property int $max_accounts
 * @property int $max_synchronizations_per_month
 * @property ?int $max_imports_per_month
 * @property ?int $history_days
 * @property bool $auto_classification
 * @property bool $expense_tracking
 * @property bool $expense_projections
 * @property bool $active
 * @property Carbon $created_at
 * @property Carbon $updated_at
 *
 * @property-read Collection<int, Subscription> $subscriptions
 * @property-read Collection<int, User> $users
 */
class Plan extends Model
{
    use HasFactory;

    protected $fillable = [
        'name',
        'slug',
        'price',
        'max_accounts',
        'max_synchronizations_per_month',
        'max_imports_per_month',
        'history_days',
        'auto_classification',
        'expense_tracking',
        'expense_projections',
        'active',
    ];

    protected function casts(): array
    {
        return [
            'auto_classification' => 'boolean',
            'expense_tracking' => 'boolean',
            'expense_projections' => 'boolean',
            'active' => 'boolean',
        ];
    }

    public function subscriptions(): HasMany
    {
        return $this->hasMany(Subscription::class);
    }

    public function users(): HasMany
    {
        return $this->hasMany(User::class);
    }
}

