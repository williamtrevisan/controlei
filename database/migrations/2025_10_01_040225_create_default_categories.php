<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Collection;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    private array $categories = [
        ['description' => 'Alimentação', 'icon' => 'shopping-cart', 'color' => 'amber'],
        ['description' => 'Transporte', 'icon' => 'truck', 'color' => 'blue'],
        ['description' => 'Saúde', 'icon' => 'heart', 'color' => 'red'],
        ['description' => 'Moradia', 'icon' => 'home', 'color' => 'purple'],
        ['description' => 'Educação', 'icon' => 'academic-cap', 'color' => 'indigo'],
        ['description' => 'Lazer e entretenimento', 'icon' => 'film', 'color' => 'pink'],
        ['description' => 'Vestuário', 'icon' => 'sparkles', 'color' => 'cyan'],
        ['description' => 'Serviços pessoais', 'icon' => 'user-circle', 'color' => 'orange'],
        ['description' => 'Tecnologia e eletrônicos', 'icon' => 'device-phone-mobile', 'color' => 'slate'],
        ['description' => 'Pets', 'icon' => 'heart', 'color' => 'green'],
        ['description' => 'Finanças e impostos', 'icon' => 'banknotes', 'color' => 'yellow'],
        ['description' => 'Doações e presentes', 'icon' => 'gift', 'color' => 'rose'],
        ['description' => 'Receitas', 'icon' => 'arrow-trending-up', 'color' => 'emerald'],
        ['description' => 'Investimentos', 'icon' => 'chart-bar', 'color' => 'violet'],
        ['description' => 'Outros', 'icon' => 'ellipsis-horizontal-circle', 'color' => 'gray'],
    ];

    public function up(): void
    {
        collect($this->categories)
            ->map(fn (array $category) => array_merge($category, [
                'active' => true,
                'created_at' => now(),
                'updated_at' => now(),
            ]))
            ->pipe(fn (Collection $categories) => DB::table('categories')->insert($categories->toArray()));
    }
};
