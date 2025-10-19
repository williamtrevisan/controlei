<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('transaction_category_feedback', function (Blueprint $table) {
            $table->id();
            $table->foreignUuid('transaction_id')->unique()->constrained();
            $table->string('description');
            $table->string('direction');
            $table->unsignedBigInteger('amount');
            $table->string('kind');
            $table->string('payment_method');
            $table->unsignedSmallInteger('total_installments')->nullable();
            $table->foreignId('category_id')->constrained();
            $table->timestamps();
        });
    }
};

