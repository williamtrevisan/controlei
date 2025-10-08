<?php

namespace App\Models;

use App\Casts\AsMoney;
use App\Casts\AsStatementPeriod;
use App\Enums\StatementStatus;
use App\ValueObjects\StatementPeriod;
use Illuminate\Database\Eloquent\Concerns\HasUuids;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Support\Carbon;
use Brick\Money\Money;
use Illuminate\Support\Collection;

/**
 * @property string $id
 * @property int $account_id
 * @property ?int $card_id
 * @property ?string $parent_statement_id
 * @property StatementPeriod $period
 * @property Carbon $closing_date
 * @property Carbon $due_date
 * @property StatementStatus $status
 * @property Money $amount
 * @property Carbon $created_at
 * @property Carbon $updated_at
 *
 * @property-read ?Account $account
 * @property-read ?Card $card
 * @property-read ?Statement $parent
 * @property-read ?Collection<int, Statement> $children
 * @property-read ?Collection<int, Transaction> $transactions
 */
class Statement extends Model
{
    use HasFactory;
    use HasUuids;

    protected $fillable = [
        'account_id',
        'card_id',
        'parent_statement_id',
        'period',
        'closing_date',
        'due_date',
        'status',
        'amount',
    ];

    protected $casts = [
        'closing_date' => 'date',
        'due_date' => 'date',
        'amount' => AsMoney::class,
        'status' => StatementStatus::class,
        'period' => AsStatementPeriod::class,
    ];

    public function period(Carbon $date): StatementPeriod
    {
        $bank = config('banklink.bank');

        $dueDate = $date->clone()
            ->addMonth()
            ->setDay($this->card?->due_day);

        $closingDate = $dueDate->clone()
            ->subDays(config()->integer("banklink.banks.$bank.closing_due_interval_days"));

        if ($date->greaterThanOrEqualTo($closingDate)) {
            return StatementPeriod::fromDate($dueDate->clone()->addMonth());
        }

        return StatementPeriod::fromDate($dueDate);
    }

    public function account(): BelongsTo
    {
        return $this->belongsTo(Account::class);
    }

    public function card(): BelongsTo
    {
        return $this->belongsTo(Card::class);
    }

    public function parent(): BelongsTo
    {
        return $this->belongsTo(Statement::class, 'parent_statement_id');
    }

    public function children(): HasMany
    {
        return $this->hasMany(Statement::class, 'parent_statement_id');
    }

    public function transactions(): HasMany
    {
        return $this->hasMany(Transaction::class);
    }
}
