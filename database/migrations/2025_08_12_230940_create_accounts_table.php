<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('accounts', function (Blueprint $table) {
            $table->id();
            $table->foreignId('user_id')->constrained()->cascadeOnDelete();
            $table->enum('type', ['checking', 'savings', 'wallet', 'investment']);
            $table->string('bank');
            $table->string('agency', 4);
            $table->string('account', 5);
            $table->string('account_digit', 1);
            $table->timestamps();

            $table->unique(['user_id', 'bank', 'agency', 'account', 'account_digit']);
        });
    }
};
