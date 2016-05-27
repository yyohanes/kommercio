<?php

namespace Kommercio\Http\Requests\Backend\Order;

use Kommercio\Http\Requests\Request;

class OrderFormRequest extends Request
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
        $rules = [
            'store_id' => 'required|integer',
            'profile.email' => 'required|email',
            'profile.full_name' => 'required',
            'profile.phone_number' => 'required',
            'profile.address_1' => 'required',
            'shipping_profile.email' => 'required|email',
            'shipping_profile.full_name' => 'required',
            'shipping_profile.phone_number' => 'required',
            'shipping_profile.address_1' => 'required',
            'line_items' => 'required',
        ];

        foreach($this->input('line_items', []) as $idx => $lineItem){
            if($lineItem['line_item_type'] == 'product'){
                $rules['line_items.'.$idx.'.sku'] = 'product_sku|required_with:line_items.'.$idx.'.net_price,line_items.'.$idx.'.quantity';
                $rules['line_items.'.$idx.'.net_price'] = 'numeric|required_with:line_items.'.$idx.'.sku,line_items.'.$idx.'.quantity';
                $rules['line_items.'.$idx.'.quantity'] = 'numeric|min:0,required_with:line_items.'.$idx.'.sku,line_items.'.$idx.'.net_price';
            }elseif($lineItem['line_item_type'] == 'fee'){
                $rules['line_items.'.$idx.'.name'] = 'required_with:line_items.'.$idx.'.lineitem_total_amount';
                $rules['line_items.'.$idx.'.lineitem_total_amount'] = 'numeric|required_with:line_items.'.$idx.'.name';
            }
        }

        return $rules;
    }
}