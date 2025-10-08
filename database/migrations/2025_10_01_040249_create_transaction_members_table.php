<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('transaction_members', function (Blueprint $table) {
            $table->uuid('id')->primary();
            $table->foreignUuid('transaction_id')->constrained();
            $table->foreignUuid('owner_id')->constrained('users');
            $table->foreignUuid('member_id')->constrained('users');
            $table->timestamp('shared_at');
            $table->timestamps();

            $table->unique(['transaction_id', 'member_id']);

            $table->index(['member_id', 'shared_at']);
            $table->index(['owner_id', 'shared_at']);
        });
    }
};

