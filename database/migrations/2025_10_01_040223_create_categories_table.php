<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('categories', function (Blueprint $table) {
            $table->id();
            $table->string('description');
            $table->string('icon')->nullable();
            $table->string('color')->nullable();
            $table->boolean('active')->default(true);
            $table->timestamps();
        });
    }
};
