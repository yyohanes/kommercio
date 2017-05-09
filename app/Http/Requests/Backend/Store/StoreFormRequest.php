<?php

namespace Kommercio\Http\Requests\Backend\Store;

use Kommercio\Http\Requests\Request;
use Kommercio\Models\Store;
use Kommercio\Models\Warehouse;

class StoreFormRequest extends Request
{
    /**
     * Determine if the user is authorized to make this request.
     *
     * @return bool
     */
    public function authorize()
    {
        return true;
    }

    /**
     * Get the validation rules that apply to the request.
     *
     * @return array
     */
    public function rules()
    {
        $typeAllowedOptions = array_keys(Store::getTypeOptions());
        $warehouseAllowedOptions = array_keys(Warehouse::getWarehouseOptions());

        $rules = [
            'name' => 'required',
            'code' => 'required|unique:stores,code'.($this->route('id')?','.$this->route('id'):null),
            'type' => 'required|in:'.implode(',', $typeAllowedOptions),
            'warehouses' => 'required',
            'warehouses.*' => 'nullable|in:'.implode(',', $warehouseAllowedOptions),
            'contacts.*.name' => 'required_with:contacts.*.email',
            'contacts.*.email' => 'required_with:contacts.*.name',
        ];

        foreach($this->input('contacts') as $contactIdx => $contactField){

        }

        return $rules;
    }
}