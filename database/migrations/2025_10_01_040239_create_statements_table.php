<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('statements', function (Blueprint $table) {
            $table->uuid('id')->primary();
            $table->foreignUuid('account_id')->constrained();
            $table->foreignUuid('card_id')->nullable()->constrained();
            $table->foreignUuid('parent_statement_id')->nullable()->constrained('statements');
            $table->string('period');
            $table->date('closing_date')->index();
            $table->date('due_date')->index();
            $table->enum('status', ['open', 'closed', 'paid', 'overdue'])->default('open');
            $table->unsignedBigInteger('amount')->default(0);
            $table->timestamps();

            $table->unique(['account_id', 'card_id', 'period'], 'unique_account_card_period');
            $table->index(['period', 'status']);
        });
    }
};
