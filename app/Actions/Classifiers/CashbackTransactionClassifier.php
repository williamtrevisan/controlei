<?php

declare(strict_types=1);

namespace App\Actions\Classifiers;

use Banklink\Actions\Classifiers\Contracts\TransactionClassifier;
use Banklink\Enums\TransactionKind;

final class CashbackTransactionClassifier implements TransactionClassifier
{
    public function kind(): TransactionKind
    {
        return TransactionKind::Cashback;
    }

    public function matches(string $description): bool
    {
        return str($description)->isMatch('/PROGRAMA/i');
    }
}
