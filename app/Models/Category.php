<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\HasMany;

/**
 * @property int $id
 * @property string $description
 * @property string|null $icon
 * @property string|null $color
 * @property bool $active
 */
class Category extends Model
{
    use HasFactory;

    protected $fillable = [
        'description',
        'icon',
        'color',
        'active',
    ];

    protected function casts(): array
    {
        return [
            'active' => 'boolean',
        ];
    }

    public function transactions(): HasMany
    {
        return $this->hasMany(Transaction::class);
    }

    public function expenses(): HasMany
    {
        return $this->hasMany(Expense::class);
    }
}
