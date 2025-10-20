<?php

namespace App\Actions;

use App\DataTransferObjects\ChargeInputData;
use App\Models\Payment;
use App\Models\Subscription;
use App\Models\User;
use App\Repositories\Contracts\ChargeRepository;
use App\Repositories\Contracts\PixPaymentRepository;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Log;

readonly class CreatePixPayment
{
    public function __construct(
        private CreatePayment         $createPayment,
        private ChargeRepository      $chargeRepository,
        private PixPaymentRepository  $pixPaymentRepository,
        private UpdatePaymentPayable  $updatePaymentPayable,
        private UpdatePaymentAsFailed $updatePaymentAsFailed
    )
    {
    }

    public function execute(int $amount, User $user, Subscription $subscription): Payment
    {
        return DB::transaction(function () use ($amount, $user, $subscription) {
            return tap(
                $this->createPayment->execute($amount, $user, $subscription),
                function (Payment $payment) use ($amount, $user, $subscription) {
                    try {
                        $charge = $this->chargeRepository
                            ->charge(ChargeInputData::from($payment, $amount, $user, $subscription));

                        $pixPayment = $this->pixPaymentRepository->create([
                            'charge_id' => $payment->id,
                            'qrcode_text' => $charge->pixCode,
                            'qrcode_image' => $charge->pixQrCodeImage,
                        ]);

                        $this->updatePaymentPayable->execute($payment, $pixPayment);

                        Log::info('Charge created successfully', [
                            'payment_id' => $payment->id,
                            'user_id' => $user->id,
                        ]);
                    } catch (\Exception $exception) {
                        Log::error('Error creating charge', [
                            'payment_id' => $payment->id,
                            'error' => $exception->getMessage(),
                        ]);

                        $this->updatePaymentAsFailed->execute($payment);

                        throw $exception;
                    }
                }
            );
        });
    }
}
