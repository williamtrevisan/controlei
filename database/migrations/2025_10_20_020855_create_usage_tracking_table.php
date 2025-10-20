<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('usage_tracking', function (Blueprint $table) {
            $table->id();
            $table->foreignId('user_id')->constrained()->cascadeOnDelete();
            $table->string('resource_type');
            $table->integer('year');
            $table->integer('month');
            $table->integer('count')->default(1);
            $table->timestamp('created_at');

            $table->unique(['user_id', 'resource_type', 'year', 'month']);
        });
    }
};
