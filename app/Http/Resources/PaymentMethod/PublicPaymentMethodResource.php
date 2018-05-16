<?php

namespace Kommercio\Http\Resources\PaymentMethod;

use Kommercio\Models\PaymentMethod\PaymentMethod;

class PublicPaymentMethodResource extends PaymentMethodResource {

    public function toArray($request) {
        /** @var PaymentMethod $paymentMethod */
        $paymentMethod = $this->resource;

        $data = parent::toArray($request);
        $data['data'] = $paymentMethod->getPublicData();

        return $data;
    }
}
