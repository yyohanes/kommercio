<?php

namespace Kommercio\ShippingMethods;

use Illuminate\Http\Request;
use Kommercio\Models\Address\Address;

interface ShippingMethodSettingsInterface
{
    public function renderSettingView(Address $address);
    public function processSettings(Request $request, Address $address);
}