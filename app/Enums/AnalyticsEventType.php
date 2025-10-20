<?php

namespace App\Enums;

enum AnalyticsEventType: string
{
    case SubscriptionCreated = 'subscription_created';
    case SubscriptionRenewed = 'subscription_renewed';
    case SubscriptionUpgraded = 'subscription_upgraded';
    case SubscriptionDowngraded = 'subscription_downgraded';
    case SubscriptionCanceled = 'subscription_canceled';
    case PaymentSucceeded = 'payment_succeeded';
    case PaymentFailed = 'payment_failed';
    case PaymentRefunded = 'payment_refunded';
}

