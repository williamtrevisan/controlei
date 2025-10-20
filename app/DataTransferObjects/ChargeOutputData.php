<?php

namespace App\DataTransferObjects;

use Illuminate\Support\Collection;

readonly class ChargeOutputData
{
    public function __construct(
        public string $pixCode,
        public ?string $pixQrCodeImage,
    ) {}

    public static function fromCollection(Collection $data): self
    {
        return new self(
            pixCode: $data->get('brCode'),
            pixQrCodeImage: $data->get('qrCodeImage'),
        );
    }
}

