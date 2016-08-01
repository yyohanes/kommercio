<?php

namespace Kommercio\Facades;

use Illuminate\Support\Facades\Facade;

class NewsletterSubscriptionHelper extends Facade
{
    protected static function getFacadeAccessor()
    {
        return 'newsletter_subscription_helper';
    }
}