<?php

declare(strict_types=1);

namespace App\Actions\Classifiers;

use Banklink\Actions\Classifiers\Contracts\TransactionClassifier;
use Banklink\Enums\TransactionKind;

final class FeeTransactionClassifier implements TransactionClassifier
{
    public function kind(): TransactionKind
    {
        return TransactionKind::Fee;
    }

    public function matches(string $description): bool
    {
        return str($description)->isMatch('/IOF/i');
    }
}
