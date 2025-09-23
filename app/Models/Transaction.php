<?php

namespace App\Models;

use App\Actions\GetAllTransactions;
use App\Casts\AsMoney;
use App\Casts\AsStatementPeriod;
use App\Enums\TransactionDirection;
use App\Enums\TransactionKind;
use App\Enums\TransactionPaymentMethod;
use App\Enums\TransactionStatus;
use App\Observers\TransactionObserver;
use App\ValueObjects\StatementPeriod;
use Banklink\Actions\Classifiers\Contracts\TransactionClassifier;
use Brick\Money\Money;
use Illuminate\Database\Eloquent\Attributes\ObservedBy;
use Illuminate\Database\Eloquent\Casts\Attribute;
use Illuminate\Database\Eloquent\Concerns\HasUuids;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Support\Carbon;

/**
 * @property string $id
 * @property ?int $account_id
 * @property ?int $card_id
 * @property ?int $income_source_id
 * @property ?string $parent_transaction_id
 * @property Carbon $date
 * @property string $description
 * @property Money $amount
 * @property TransactionDirection $direction
 * @property TransactionKind $kind
 * @property TransactionPaymentMethod $payment_method
 * @property int $current_installment
 * @property int $total_installments
 * @property StatementPeriod $statement_period
 * @property TransactionStatus $status
 * @property ?string $matcher_regex
 * @property string $hash
 * @property Carbon $created_at
 * @property Carbon $updated_at
 * @property-read string $remaining_installments
 * @property-read string $installments
 * @property-read ?Account $account
 * @property-read ?Card $card
 * @property-read ?IncomeSource $incomeSource
 * @property-read ?Transaction $parent
 * @property-read ?Transaction $child
 */
#[ObservedBy(TransactionObserver::class)]
class Transaction extends Model
{
    /** @use HasFactory<\Database\Factories\TransactionFactory> */
    use HasFactory;
    use HasUuids;

    protected ?int $lastPaidInstallment = null;

    protected $fillable = [
        'account_id',
        'card_id',
        'income_source_id',
        'parent_transaction_id',
        'date',
        'description',
        'amount',
        'direction',
        'kind',
        'payment_method',
        'current_installment',
        'total_installments',
        'statement_period',
        'status',
        'matcher_regex',
        'hash'
    ];

    protected function remainingInstallments(): Attribute
    {
        return Attribute::make(
            get: fn () => $this->total_installments - $this->current_installments,
        );
    }

    protected function installments(): Attribute
    {
        return Attribute::make(
            get: function () {
                if (is_null($this->current_installment)) {
                    return '';
                }

                return "$this->current_installment de $this->total_installments";
            },
        );
    }

    public function hash(): string
    {
        return hash('sha256', implode('|', [
            $this->account_id,
            $this->card_id,
            $this->date->format('Y-m-d'),
            $this->description,
            $this->amount,
            $this->current_installment,
        ]));
    }

    protected function casts(): array
    {
        return [
            'date' => 'datetime',
            'amount' => AsMoney::class,
            'direction' => TransactionDirection::class,
            'kind' => TransactionKind::class,
            'payment_method' => TransactionPaymentMethod::class,
            'statement_period' => AsStatementPeriod::class,
            'status' => TransactionStatus::class,
        ];
    }

    public function account(): BelongsTo
    {
        return $this->belongsTo(Account::class);
    }

    public function card(): BelongsTo
    {
        return $this->belongsTo(Card::class);
    }

    public function incomeSource(): BelongsTo
    {
        return $this->belongsTo(IncomeSource::class);
    }

    public function parent(): BelongsTo
    {
        return $this->belongsTo(Transaction::class, 'parent_transaction_id');
    }

    public function child(): HasMany
    {
        return $this->hasMany(Transaction::class, 'parent_transaction_id');
    }

    final public function classify(): self
    {
        $this->kind = TransactionKind::fromTransaction($this);

        return $this;
    }

    final public function isCashback(): bool
    {
        return $this->matches(kind: TransactionKind::Cashback);
    }

    final public function isFee(): bool
    {
        return $this->matches(kind: TransactionKind::Fee);
    }

    final public function isInvoicePayment(): bool
    {
        return $this->matches(kind: TransactionKind::InvoicePayment);
    }

    public function isRefund(): bool
    {
        return app()->make(GetAllTransactions::class)
            ->execute()
            ->reject(fn (Transaction $transaction): bool => $transaction->description === $this->description)
            ->some(function (Transaction $transaction): bool {
                return str($this->description)->contains($transaction->description)
                    && $transaction->amount === $this->amount
                    && $transaction->direction->isOutflow();
            });
    }

    private function matches(TransactionKind $kind): bool
    {
        $bank = config()->get('banklink.bank');

        return config()
            ->collection("banklink.banks.$bank.classifiers", [])
            ->some(function (string $classifierClass) use ($kind): bool {
                /** @var TransactionClassifier $classifier */
                $classifier = app()->make($classifierClass);

                return $classifier->kind()->is($kind->value)
                    && $classifier->matches($this->description);
            });
    }
}
