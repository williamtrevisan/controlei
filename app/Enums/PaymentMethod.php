<?php

namespace App\Enums;

enum PaymentMethod: string
{
    case Pix = 'pix';
    case CreditCard = 'credit_card';
}
