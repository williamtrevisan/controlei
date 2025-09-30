<?php

namespace App\Actions;

use App\Models\Expense;
use Illuminate\Support\Collection;

class GetAllExpenses
{
    public function execute(): Collection
    {
        return Expense::query()
            ->where('user_id', auth()->id())
            ->where('active', true)
            ->orderBy('description')
            ->get();
    }
}
