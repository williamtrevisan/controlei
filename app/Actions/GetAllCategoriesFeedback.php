<?php

namespace App\Actions;

use App\Repositories\Contracts\TransactionCategoryFeedbackRepository;
use Illuminate\Support\Collection;

readonly class GetAllCategoriesFeedback
{
    public function __construct(
        private TransactionCategoryFeedbackRepository $transactionCategoryFeedbackRepository,
    ) {
    }

    public function execute(): Collection
    {
        return $this->transactionCategoryFeedbackRepository->all();
    }
}

