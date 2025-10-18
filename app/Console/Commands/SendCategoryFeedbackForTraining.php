<?php

namespace App\Console\Commands;

use App\Actions\GetAllCategoriesFeedback;
use App\Models\TransactionCategoryFeedback;
use Illuminate\Console\Command;
use Illuminate\Support\Facades\Http;
use Illuminate\Support\Facades\Log;

class SendCategoryFeedbackForTraining extends Command
{
    protected $signature = 'model:train';

    protected $description = 'Send transaction category feedback to the ML training service';

    public function __construct(
        private readonly GetAllCategoriesFeedback $getAllCategoriesFeedback,
    ) {
        parent::__construct();
    }

    public function handle(): int
    {
        $this->info('Fetching category feedback data...');

        if (($feedback = $this->getAllCategoriesFeedback->execute())->isEmpty()) {
            $this->warn('No feedback records found. Skipping training.');
            Log::info('Category feedback training skipped: no data available');

            return self::SUCCESS;
        }

        if ($feedback->count() < 25) {
            $this->warn('Insufficient feedback records. Minimum 25 records required (10 train, 5 validation).');
            Log::warning('Category feedback training skipped: insufficient data', [
                'count' => $feedback->count(),
                'required' => 25,
            ]);

            return self::FAILURE;
        }

        $splitPoint = (int) ceil(($shuffled = $feedback->shuffle())->count() * 0.8);

        $trainingData = $shuffled->take($splitPoint);
        $validationData = $shuffled->skip($splitPoint);

        if ($validationData->isEmpty()) {
            $this->warn('Validation dataset is empty after split. Minimum 5 records required.');
            Log::warning('Category feedback training skipped: empty validation set');

            return self::FAILURE;
        }

        $this->info("Training samples: {$trainingData->count()}");
        $this->info("Validation samples: {$validationData->count()}");

        try {
            $response = Http::lab()
                ->post('/train', [
                    'training' => $trainingData
                        ->map(fn (TransactionCategoryFeedback $feedback) => [
                            'description' => $feedback->description,
                            'direction' => $feedback->direction,
                            'amount' => $feedback->amount,
                            'kind' => $feedback->kind,
                            'payment_method' => $feedback->payment_method,
                            'total_installments' => $feedback->total_installments,
                            'category_id' => $feedback->category_id,
                        ])
                        ->values(),
                    'validation' => $validationData
                        ->map(fn (TransactionCategoryFeedback $feedback) => [
                            'description' => $feedback->description,
                            'direction' => $feedback->direction,
                            'amount' => $feedback->amount,
                            'kind' => $feedback->kind,
                            'payment_method' => $feedback->payment_method,
                            'total_installments' => $feedback->total_installments,
                            'category_id' => $feedback->category_id,
                        ])
                        ->values(),
                ]);

            if ($response->successful()) {
                $data = $response->json();

                $this->info('Training request sent successfully!');
                $this->line("Message: {$data['message']}");

                if (isset($data['task_id'])) {
                    $this->line("Task ID: {$data['task_id']}");
                }

                Log::info('Category feedback training initiated successfully', [
                    'training_samples' => $trainingData->count(),
                    'validation_samples' => $validationData->count(),
                    'response' => $data,
                ]);

                return self::SUCCESS;
            }

            $this->error('Training request failed.');
            $this->error("Status: {$response->status()}");
            $this->error("Response: {$response->body()}");

            Log::error('Category feedback training failed', [
                'status' => $response->status(),
                'response' => $response->body(),
            ]);

            return self::FAILURE;
        } catch (\Exception $exception) {
            $this->error('Failed to send training request.');
            $this->error("Error: {$exception->getMessage()}");

            Log::error('Category feedback training exception', [
                'error' => $exception->getMessage(),
                'trace' => $exception->getTraceAsString(),
            ]);

            return self::FAILURE;
        }
    }
}

