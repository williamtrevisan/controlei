<?php

declare(strict_types=1);

namespace App\Actions\Classifiers;

use App\Actions\GetAllCardMatcherRegex;
use Banklink\Actions\Classifiers\Contracts\TransactionClassifier;
use Banklink\Enums\TransactionKind;

final class InvoicePaymentTransactionClassifier implements TransactionClassifier
{
    public function __construct(
        private readonly GetAllCardMatcherRegex $getAllCardMatcherRegex
    ) {
    }

    public function kind(): TransactionKind
    {
        return TransactionKind::InvoicePayment;
    }

    public function matches(string $description): bool
    {
        return $this->getAllCardMatcherRegex
            ->execute()
            ->some(fn (string $pattern): bool => str($description)->isMatch($pattern));
    }
}
