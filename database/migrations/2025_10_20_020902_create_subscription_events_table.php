<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('subscription_events', function (Blueprint $table) {
            $table->id();
            $table->foreignId('user_id')->constrained()->cascadeOnDelete();
            $table->foreignUuid('subscription_id')->nullable()->constrained()->nullOnDelete();
            $table->enum('event_type', ['subscribed', 'renewed', 'upgraded', 'downgraded', 'canceled', 'reactivated', 'expired']);
            $table->foreignId('from_id')->nullable()->constrained('plans');
            $table->foreignId('to_id')->nullable()->constrained('plans');
            $table->integer('monthly_recurring_revenue_change')->default(0);
            $table->timestamp('created_at');
            
            $table->index(['user_id', 'event_type', 'created_at']);
        });
    }
};
