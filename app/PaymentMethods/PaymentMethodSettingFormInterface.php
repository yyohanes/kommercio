<?php

namespace Kommercio\PaymentMethods;

use Illuminate\Http\Request;

interface PaymentMethodSettingFormInterface
{
    public function saveForm(Request $request);
    public function settingForm();
    public static function additionalSettingValidation(Request $request);
}