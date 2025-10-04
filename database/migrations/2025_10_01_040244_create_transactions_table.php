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
            $table->foreignUuid('account_id')->constrained();
            $table->foreignUuid('card_id')->nullable()->constrained();
            $table->foreignUuid('income_source_id')->nullable()->constrained();
            $table->foreignUuid('expense_id')->nullable()->constrained();
            $table->foreignUuid('statement_id')->nullable()->constrained();
            $table->foreignUuid('parent_transaction_id')->nullable()->constrained('transactions');
            $table->date('date');
            $table->string('description');
            $table->unsignedBigInteger('amount')->default(0);
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
            $table->enum('status', ['scheduled', 'paid', 'canceled'])->default('paid');
            $table->string('matcher_regex')->nullable();
            $table->string('hash', 64)->unique();
            $table->timestamps();
        });
    }
};
