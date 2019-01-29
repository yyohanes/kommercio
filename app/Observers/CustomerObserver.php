<?php

namespace Kommercio\Observers;

use Kommercio\Models\Customer;

class CustomerObserver
{
    public function created(Customer $customer)
    {
        $customer->reference = $customer->generateReference();
        $customer->save();
    }
}
