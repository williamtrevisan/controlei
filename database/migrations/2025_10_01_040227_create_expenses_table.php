<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('expenses', function (Blueprint $table) {
            $table->uuid('id')->primary();
            $table->foreignUuid('user_id')->constrained()->cascadeOnDelete();
            $table->foreignId('category_id')->nullable()->constrained()->nullOnDelete();
            $table->string('description');
            $table->enum('frequency', ['monthly', 'quarterly', 'annually', 'occasionally'])->default('monthly');
            $table->string('matcher_regex');
            $table->unsignedBigInteger('average_amount')->default(0);
            $table->boolean('active')->default(true);
            $table->timestamps();
        });
    }
};
