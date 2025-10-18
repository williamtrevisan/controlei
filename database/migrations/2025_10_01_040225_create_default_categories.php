<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Collection;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    private array $categories = [
        'Alimentação',
        'Transporte',
        'Saúde',
        'Moradia',
        'Educação',
        'Lazer e entretenimento',
        'Vestuário',
        'Serviços pessoais',
        'Tecnologia e eletrônicos',
        'Pets',
        'Finanças e impostos',
        'Doações e presentes',
        'Receitas',
        'Investimentos',
        'Outros'
    ];

    public function up(): void
    {
        collect($this->categories)
            ->map(fn (string $category) => [
                'description' => $category,
                'active' => true,
                'created_at' => now(),
                'updated_at' => now(),
            ])
            ->pipe(fn (Collection $categories) => DB::table('categories')->insert($categories->toArray()));
    }
};
