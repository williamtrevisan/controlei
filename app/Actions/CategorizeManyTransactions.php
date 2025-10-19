<?php

namespace App\Actions;

use App\DataTransferObjects\CategorizedTransactionData;
use App\Models\Transaction;
use Illuminate\Support\Collection;
use Illuminate\Support\Facades\Http;

readonly class CategorizeManyTransactions
{
    public function __construct(
        private UpdateTransactionCategoryByCategorized $updateTransactionCategoryByCategorized,
    ) {
    }

    /**
     * @param Collection<int, Transaction> $transactions
     * @return void
     */
    public function execute(Collection $transactions): void
    {
        if ($transactions->isEmpty()) {
            return;
        }

        $response = Http::categorize()
            ->withOptions([
                'stream' => true,
                'curl' => [
                    CURLOPT_TCP_KEEPALIVE => 1,
                    CURLOPT_TCP_KEEPIDLE => 30,
                    CURLOPT_TCP_KEEPINTVL => 10,
                ],
            ])
            ->post('/categorize', [
                'transactions' => $transactions->map(fn (Transaction $transaction) => [
                    'id' => $transaction->id,
                    'description' => $transaction->description,
                    'direction' => $transaction->direction->value,
                    'amount' => $transaction->amount->getMinorAmount()->toInt(),
                    'kind' => $transaction->kind->value,
                    'payment_method' => $transaction->payment_method->value,
                    'total_installments' => $transaction->total_installments > 1 ? 
                        $transaction->total_installments : null,
                ]),
            ]);

        if ($response->failed()) {
            return;
        }

        $body = $response->toPsrResponse()->getBody();
        $buffer = '';

        try {
            while (! $body->eof()) {
                $chunk = $body->read(8192);

                if ($chunk === '') {
                    usleep(50000);

                    continue;
                }

                $buffer .= $chunk;
                $lines = explode("\n", $buffer);

                $buffer = array_pop($lines);

                if (empty($lines)) {
                    continue;
                }

                $categorizedTransactions = collect($lines)
                    ->filter(fn (string $line) => trim($line) !== '')
                    ->map(function (string $data) {
                        try {
                            return CategorizedTransactionData::from(json_decode($data, associative: true));
                        } catch (\Throwable $e) {
                            report($e);

                            return null;
                        }
                    })
                    ->filter();

                if ($categorizedTransactions->isNotEmpty()) {
                    $this->updateTransactionCategoryByCategorized->execute($categorizedTransactions);
                }
            }

            if (trim($buffer) !== '') {
                try {
                    $categorizedTransaction = CategorizedTransactionData::from(json_decode($buffer, associative: true));
                    $this->updateTransactionCategoryByCategorized->execute(collect([$categorizedTransaction]));
                } catch (\Throwable $e) {
                    report($e);
                }
            }
        } catch (\Throwable $e) {
            report($e);
        } finally {
            $body->close();
        }
    }
}
