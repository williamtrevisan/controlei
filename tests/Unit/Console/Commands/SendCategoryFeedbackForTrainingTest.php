<?php

use App\Models\Category;
use App\Models\TransactionCategoryFeedback;
use Illuminate\Console\Command;
use Illuminate\Http\Client\Request;
use Illuminate\Http\Response;
use Illuminate\Support\Facades\Http;

it('successfully sends feedback data to training service', function () {
    TransactionCategoryFeedback::factory(count: 25)
        ->for(Category::factory()->create())
        ->createQuietly();

    Http::fake([
        '*/train' => Http::response([
            'message' => 'Training data received and processing started',
            'training_samples' => 20,
            'validation_samples' => 5,
            'task_id' => 'train-123abc',
        ]),
    ]);

    $this->artisan('model:train')
        ->expectsOutputToContain('Fetching category feedback data...')
        ->expectsOutputToContain('Training samples: 20')
        ->expectsOutputToContain('Validation samples: 5')
        ->expectsOutputToContain('Training request sent successfully!')
        ->assertExitCode(Command::SUCCESS);

    Http::assertSent(function (Request $request) {
        return $request->url() === config('services.lab.url') . '/train' &&
            $request->hasHeader('Authorization') &&
            $request->data()['training']->count() === 20 &&
            $request->data()['validation']->count() === 5;
    });
});

it('handles empty feedback table gracefully', function () {
    Http::fake();

    $this->artisan('model:train')
        ->expectsOutputToContain('Fetching category feedback data...')
        ->expectsOutputToContain('No feedback records found. Skipping training.')
        ->assertExitCode(Command::SUCCESS);

    Http::assertNothingSent();
});

it('fails when insufficient feedback records exist', function () {
    TransactionCategoryFeedback::factory(count: 24)
        ->for(Category::factory()->create())
        ->createQuietly();

    Http::fake();

    $this->artisan('model:train')
        ->expectsOutputToContain('Insufficient feedback records. Minimum 25 records required (10 train, 5 validation).')
        ->assertExitCode(Command::FAILURE);

    Http::assertNothingSent();
});

it('handles API authentication errors', function () {
    TransactionCategoryFeedback::factory(count: 25)
        ->for(Category::factory()->create())
        ->createQuietly();

    Http::fake([
        '*/train' => Http::response([
            'detail' => 'Invalid authentication token',
        ], status: Response::HTTP_UNAUTHORIZED),
    ]);

    $this->artisan('model:train')
        ->expectsOutputToContain('Training request failed.')
        ->expectsOutputToContain('Status: 401')
        ->assertExitCode(Command::FAILURE);
});

it('handles API server errors', function () {
    TransactionCategoryFeedback::factory(count: 25)
        ->for(Category::factory()->create())
        ->createQuietly();

    Http::fake([
        '*/train' => Http::response([
            'detail' => 'Internal server error',
        ], status: Response::HTTP_INTERNAL_SERVER_ERROR),
    ]);

    $this->artisan('model:train')
        ->expectsOutputToContain('Training request failed.')
        ->expectsOutputToContain('Status: 500')
        ->assertExitCode(Command::FAILURE);
});

it('handles network connection errors', function () {
    TransactionCategoryFeedback::factory(count: 25)
        ->for(Category::factory()->create())
        ->createQuietly();

    Http::fake(function () {
        throw new \Exception('Connection timeout');
    });

    $this->artisan('model:train')
        ->expectsOutputToContain('Failed to send training request.')
        ->expectsOutputToContain('Error: Connection timeout')
        ->assertExitCode(Command::FAILURE);
});

it('correctly splits data into training and validation sets', function () {
    TransactionCategoryFeedback::factory(count: 100)
        ->for(Category::factory()->create())
        ->createQuietly();

    Http::fake([
        '*/train' => Http::response([
            'message' => 'Training data received',
            'training_samples' => 80,
            'validation_samples' => 20,
        ]),
    ]);

    $this->artisan('model:train')
        ->assertExitCode(Command::SUCCESS);

    Http::assertSent(function (Request $request) {
        $trainingCount = count($request['training']);
        $validationCount = count($request['validation']);

        return $trainingCount === 80 &&
            $validationCount === 20 &&
            $trainingCount + $validationCount === 100;
    });
});

it('ensures all feedback records have required fields', function () {
    TransactionCategoryFeedback::factory(count: 25)
        ->for(Category::factory()->create())
        ->createQuietly();

    Http::fake([
        '*/train' => Http::response(['message' => 'Success']),
    ]);

    $this->artisan('model:train')
        ->assertExitCode(Command::SUCCESS);

    Http::assertSent(function (Request $request) {
        $allTrainingValid = collect($request['training'])->every(function ($item) {
            return isset($item['description']) &&
                isset($item['category_id']) &&
                is_string($item['description']) &&
                is_int($item['category_id']);
        });

        $allValidationValid = collect($request['validation'])->every(function ($item) {
            return isset($item['description']) &&
                isset($item['category_id']) &&
                is_string($item['description']) &&
                is_int($item['category_id']);
        });

        return $allTrainingValid && $allValidationValid;
    });
});

