<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('transactions', function (Blueprint $table) {
            $table->uuid('id')->primary();
            $table->foreignId('account_id')->constrained();
            $table->foreignId('card_id')->nullable()->constrained();
            $table->foreignId('income_source_id')->nullable()->constrained();
            $table->date('date');
            $table->string('description');
            $table->decimal('amount', 14);
            $table->enum('direction', ['inflow', 'outflow']);
            $table->enum('kind', [
                'cashback',
                'fee',
                'invoice_payment',
                'purchase',
                'refund',
            ]);
            $table->enum('payment_method', ['credit', 'debit', 'pix', 'cash', 'ted', 'doc']);
            $table->unsignedSmallInteger('current_installment')->nullable();
            $table->unsignedSmallInteger('total_installments')->nullable();
            $table->string('statement_period')->nullable()->index();
            $table->string('hash', 64)->unique();
            $table->timestamps();
        });
    }
};
