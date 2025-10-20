<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Support\Facades\DB;

return new class extends Migration
{
    public function up(): void
    {
        DB::table('plans')->insert([
            [
                'name' => 'Free',
                'slug' => 'free',
                'price' => 0,
                'max_accounts' => 1,
                'max_synchronizations_per_month' => 1,
                'max_imports_per_month' => 5,
                'history_days' => 30,
                'auto_classification' => false,
                'expense_tracking' => false,
                'expense_projections' => false,
                'active' => true,
                'created_at' => now(),
                'updated_at' => now(),
            ],
            [
                'name' => 'Basic',
                'slug' => 'basic',
                'price' => 990,
                'max_accounts' => 2,
                'max_synchronizations_per_month' => 1,
                'max_imports_per_month' => null,
                'history_days' => 60,
                'auto_classification' => true,
                'expense_tracking' => true,
                'expense_projections' => false,
                'active' => true,
                'created_at' => now(),
                'updated_at' => now(),
            ],
        ]);
    }
};
