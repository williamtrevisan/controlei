<?php

namespace App\Models;

use App\Enums\RevenuePeriod;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Support\Carbon;

/**
 * @property int $id
 * @property Carbon $date
 * @property RevenuePeriod $period
 * @property int $monthly_recurring_revenue
 * @property int $total_revenue
 * @property int $total_customers
 * @property int $paying_customers
 * @property int $new_customers
 * @property int $churned_customers
 * @property int $successful_payments
 * @property int $failed_payments
 * @property ?float $monthly_recurring_revenue_growth_rate
 * @property ?float $churn_rate
 * @property Carbon $created_at
 * @property Carbon $updated_at
 */
class RevenueSnapshot extends Model
{
    use HasFactory;

    protected $fillable = [
        'date',
        'period',
        'monthly_recurring_revenue',
        'total_revenue',
        'total_customers',
        'paying_customers',
        'new_customers',
        'churned_customers',
        'successful_payments',
        'failed_payments',
        'monthly_recurring_revenue_growth_rate',
        'churn_rate',
    ];

    protected function casts(): array
    {
        return [
            'date' => 'date',
            'period' => RevenuePeriod::class,
            'monthly_recurring_revenue_growth_rate' => 'decimal:2',
            'churn_rate' => 'decimal:2',
        ];
    }
}

