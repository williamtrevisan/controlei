<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('analytics', function (Blueprint $table) {
            $table->id();
            $table->foreignId('user_id')->constrained()->cascadeOnDelete();
            $table->morphs('analyticsable');
            $table->enum('event_type', ['subscription_created', 'subscription_renewed', 'subscription_upgraded', 'subscription_downgraded', 'subscription_canceled', 'payment_succeeded', 'payment_failed', 'payment_refunded']);
            $table->integer('amount')->nullable();
            $table->json('data');
            $table->timestamp('event_at');
            $table->timestamps();
            
            $table->index(['user_id', 'event_type', 'event_at']);
        });
    }
};
