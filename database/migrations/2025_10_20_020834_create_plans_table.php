<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('plans', function (Blueprint $table) {
            $table->id();
            $table->string('name');
            $table->string('slug')->unique();
            $table->integer('price')->default(0);
            $table->integer('max_accounts')->default(1);
            $table->integer('max_synchronizations_per_month')->default(1);
            $table->integer('max_imports_per_month')->nullable();
            $table->integer('history_days')->nullable();
            $table->boolean('auto_classification')->default(false);
            $table->boolean('expense_tracking')->default(false);
            $table->boolean('expense_projections')->default(false);
            $table->boolean('active')->default(true);
            $table->timestamps();
        });
    }
};
