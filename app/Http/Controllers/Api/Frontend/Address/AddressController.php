<?php

namespace Kommercio\Http\Controllers\Api\Frontend\Address;

use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Kommercio\Http\Controllers\AddressController as BaseAddressController;
use Kommercio\Http\Resources\Address\AddressOptionResource;

class AddressController extends BaseAddressController {

    /**
     * @param Request $request
     * @param string $type
     * @return JsonResponse
     */
    public function options(Request $request, string $type) {
        $options = $this->getOptions($request, $type);

        $addressOptions = collect([]);
        foreach ($options as $addressId => $addressName) {
            $addressOptions[] = [
                'id' => $addressId,
                'name' => $addressName,
            ];
        }

        $response = AddressOptionResource::collection(collect($addressOptions));

        return $response->response();
    }
}
