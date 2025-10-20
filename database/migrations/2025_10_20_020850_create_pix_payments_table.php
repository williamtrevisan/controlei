<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('pix_payments', function (Blueprint $table) {
            $table->uuid('id')->primary();
            $table->string('charge_id')->unique();
            $table->text('qrcode_text');
            $table->text('qrcode_image')->nullable();
            $table->string('payer_name')->nullable();
            $table->string('payer_document')->nullable();
            $table->timestamps();
        });
    }
};
