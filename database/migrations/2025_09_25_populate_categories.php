<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Support\Facades\DB;

return new class extends Migration
{
    public function up(): void
    {
        $categories = [
            'Alimentação',
            'Transporte', 
            'Saúde',
            'Entretenimento',
            'Educação',
            'Casa',
            'Serviços',
            'Outros',
        ];

        foreach ($categories as $category) {
            DB::table('categories')->insert([
                'description' => $category,
                'active' => true,
                'created_at' => now(),
                'updated_at' => now(),
            ]);
        }
    }
};
