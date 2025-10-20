<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('revenue_snapshots', function (Blueprint $table) {
            $table->id();
            $table->date('date');
            $table->enum('period', ['daily', 'weekly', 'monthly', 'yearly']);
            $table->integer('monthly_recurring_revenue');
            $table->integer('total_revenue');
            $table->integer('total_customers');
            $table->integer('paying_customers');
            $table->integer('new_customers');
            $table->integer('churned_customers');
            $table->integer('successful_payments');
            $table->integer('failed_payments');
            $table->decimal('monthly_recurring_revenue_growth_rate', 5)->nullable();
            $table->decimal('churn_rate', 5)->nullable();
            $table->timestamps();

            $table->unique(['date', 'period']);
        });
    }
};
