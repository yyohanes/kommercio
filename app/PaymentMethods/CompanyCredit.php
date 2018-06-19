<?php

namespace Kommercio\PaymentMethods;

use Illuminate\Http\Request;
use Kommercio\Models\PaymentMethod\PaymentMethod;

class CompanyCredit extends PaymentMethodAbstract
{
    /**
     * @inheritdoc
     */
    public function availableLocations()
    {
        return [PaymentMethod::LOCATION_BACKOFFICE];
    }
}
