<?php

namespace App\Actions;

use App\Repositories\Contracts\CategoryRepository;
use Illuminate\Support\Collection;

class GetAllCategories
{
    public function __construct(
        private readonly CategoryRepository $categoryRepository
    ) {
    }

    /**
     * @return Collection<int, Category>
     */
    public function execute(): Collection
    {
        return $this->categoryRepository->actives();
    }
}
