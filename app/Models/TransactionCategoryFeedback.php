<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

/**
 * @property int $id
 * @property string $transaction_id
 * @property string $description
 * @property string $direction
 * @property int $amount
 * @property string $kind
 * @property string $payment_method
 * @property ?int $total_installments
 * @property int $category_id
 * @property \Illuminate\Support\Carbon $created_at
 * @property \Illuminate\Support\Carbon $updated_at
 *
 * @property-read Transaction $transaction
 * @property-read Category $category
 */
class TransactionCategoryFeedback extends Model
{
    /** @use HasFactory<\Database\Factories\TransactionCategoryFeedbackFactory> */
    use HasFactory;

    protected $table = 'transaction_category_feedback';

    protected $fillable = [
        'transaction_id',
        'description',
        'direction',
        'amount',
        'kind',
        'payment_method',
        'total_installments',
        'category_id',
    ];

    public function transaction(): BelongsTo
    {
        return $this->belongsTo(Transaction::class);
    }

    public function category(): BelongsTo
    {
        return $this->belongsTo(Category::class);
    }
}

