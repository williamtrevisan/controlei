<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('invites', function (Blueprint $table) {
            $table->uuid('id')->primary();
            $table->foreignUuid('inviter_id')->constrained('users');
            $table->foreignUuid('invitee_id')->constrained('users');
            $table->enum('status', ['pending', 'accepted', 'rejected', 'blocked'])->default('pending');
            $table->string('message', 500)->nullable();
            $table->timestamp('accepted_at')->nullable();
            $table->timestamps();

            $table->index(['inviter_id', 'status']);
            $table->index(['invitee_id', 'status']);

            $table->unique(['inviter_id', 'invitee_id']);
        });
    }
};
