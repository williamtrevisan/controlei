<?php

namespace App\Pipelines\TransactionSynchronization\Actions;

use App\Actions\CreateManyStatements;
use App\Actions\FindManyStatementsByCardIdAndPeriod;
use App\Actions\GetAllUserCards;
use App\DataTransferObjects\StatementData;
use App\DataTransferObjects\SynchronizationData;
use App\Models\Statement;
use Banklink\Entities\Card as BankCard;
use Banklink\Entities\CardStatement;
use Closure;
use Illuminate\Support\Collection;

final readonly class FindOrCreateManyStatements
{
    public function __construct(
        private FindManyStatementsByCardIdAndPeriod $findManyStatementsByCardIdAndPeriod,
        private CreateManyStatements $createManyStatements,
        private GetAllUserCards $getAllUserCards,
    ) {
    }

    public function handle(SynchronizationData $data, Closure $next): SynchronizationData
    {
        $cards = $this->getAllUserCards->execute();

        $statements = $data->bank->account()
            ->cards()->all()
            ->flatMap(fn (BankCard $card): Collection => $card->statements()->all())
            ->map(function (CardStatement $statement) use ($cards): StatementData {
                $card = $cards->firstWhere('last_four_digits', $statement->card()->lastFourDigits());

                return StatementData::from($statement, $card);
            });

        $existingStatements = $this->findManyStatementsByCardIdAndPeriod->execute($statements);

        $statements
            ->reject(function (StatementData $data) use ($existingStatements): bool {
                return $existingStatements->contains(function (Statement $statement) use ($data): bool {
                    return $statement->card_id === $data->cardId
                        && $statement->period->value() === $data->period;
                });
            })
            ->pipe(fn (Collection $statements) => $this->createManyStatements->execute($statements));

        return $next($data);
    }
}
