<?php

namespace App\Enums;

enum SubscriptionEventType: string
{
    case Subscribed = 'subscribed';
    case Renewed = 'renewed';
    case Upgraded = 'upgraded';
    case Downgraded = 'downgraded';
    case Canceled = 'canceled';
    case Reactivated = 'reactivated';
    case Expired = 'expired';
}
