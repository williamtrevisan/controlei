<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Concerns\HasUuids;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\MorphOne;
use Illuminate\Support\Carbon;

/**
 * @property string $id
 * @property string $charge_id
 * @property string $qrcode_text
 * @property ?string $qrcode_image
 * @property ?string $payer_name
 * @property ?string $payer_document
 * @property Carbon $created_at
 * @property Carbon $updated_at
 *
 * @property-read Payment $payment
 */
class PixPayment extends Model
{
    use HasFactory, HasUuids;

    protected $fillable = [
        'charge_id',
        'qrcode_text',
        'qrcode_image',
        'payer_name',
        'payer_document',
    ];

    public function payment(): MorphOne
    {
        return $this->morphOne(Payment::class, 'payable');
    }
}

