<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('subscriptions', function (Blueprint $table) {
            $table->uuid('id')->primary();
            $table->foreignId('user_id')->constrained()->cascadeOnDelete();
            $table->foreignId('plan_id')->constrained();
            $table->enum('status', ['active', 'canceled', 'expired']);
            $table->timestamp('started_at');
            $table->timestamp('ended_at');
            $table->timestamp('canceled_at')->nullable();
            $table->timestamps();
            
            $table->index(['user_id', 'status']);
        });
    }
};
