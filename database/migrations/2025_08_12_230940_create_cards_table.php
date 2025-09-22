<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('cards', function (Blueprint $table) {
            $table->id();
            $table->foreignId('account_id')->constrained();
            $table->string('last_four_digits', 4);
            $table->enum('type', ['credit', 'debit'])->nullable();
            $table->enum('brand', ['visa', 'mastercard'])->nullable();
            $table->unsignedBigInteger('limit')->default(0);
            $table->unsignedTinyInteger('due_day')->default(1);
            $table->string('matcher_regex')->nullable();
            $table->timestamps();
            
            $table->unique(['account_id', 'last_four_digits']);
        });
    }
};
