<?php

namespace Kommercio\Http\Requests\Backend\Order;

use Kommercio\Http\Requests\Request;
use Kommercio\Models\Product;
use Kommercio\Models\Product\Composite\ProductComposite;
use Kommercio\Models\Product\Configuration\ProductConfiguration;

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
            //'profile.address_1' => 'required',
            'shipping_profile.email' => 'required|email',
            'shipping_profile.full_name' => 'required',
            'shipping_profile.phone_number' => 'required',
            //'shipping_profile.address_1' => 'required',
            'line_items' => 'required',
            'shipping' => 'required',
            'payment_method' => 'required',
            'invoices.*.due_date' => 'nullable|date_format:Y-m-d'
        ];

        if(config('project.enable_delivery_date', FALSE)){
            $rules['delivery_date'] = 'required|date_format:Y-m-d';
        }

        foreach($this->input('line_items', []) as $idx => $lineItem){
            if($lineItem['line_item_type'] == 'product'){
                $rules['line_items.'.$idx.'.sku'] = 'product_sku|nullable|required_with:line_items.'.$idx.'.net_price,line_items.'.$idx.'.quantity';
                $rules['line_items.'.$idx.'.net_price'] = 'numeric|nullable|required_with:line_items.'.$idx.'.sku,line_items.'.$idx.'.quantity';
                $rules['line_items.'.$idx.'.quantity'] = 'numeric|nullable|min:0,required_with:line_items.'.$idx.'.sku,line_items.'.$idx.'.net_price';

                foreach($this->input('line_items.'.$idx.'.children', []) as $compositeId => $compositeLineItems){
                    $quantity = 0;
                    $productComposite = ProductComposite::findOrFail($compositeId);
                    foreach($compositeLineItems as $compositeLineItemIdx => $compositeLineItem){
                        $quantity += $compositeLineItem['quantity'];
                        $rules['line_items.'.$idx.'.children.'.$compositeId.'.'.$compositeLineItemIdx.'.sku'] = ($productComposite->minimum>0?'required|':'').'product_sku|nullable';

                        if(isset($compositeLineItem['product_configuration'])){
                            $product = Product::findOrFail($compositeLineItem['line_item_id']);

                            foreach($compositeLineItem['product_configuration'] as $productConfigurationId => $configuration){
                                $productConfiguration = $product->productConfigurationGroup->configurations->filter(function($row) use ($productConfigurationId){
                                    return $row->id == $productConfigurationId;
                                })->first();

                                if($productConfiguration){
                                    $rules['line_items.'.$idx.'.children.'.$compositeId.'.'.$compositeLineItemIdx.'.product_configuration.'.$productConfigurationId] = $productConfiguration->buildRules();
                                }
                            }
                        }
                    }
                }
            }elseif($lineItem['line_item_type'] == 'fee'){
                $rules['line_items.'.$idx.'.name'] = 'nullable|required_with:line_items.'.$idx.'.lineitem_total_amount';
                $rules['line_items.'.$idx.'.lineitem_total_amount'] = 'numeric|nullable|required_with:line_items.'.$idx.'.name';
            }
        }

        return $rules;
    }

    public function all($keys = null)
    {
        $attributes = parent::all($keys);

        foreach($this->input('line_items', []) as $idx => $lineItem){
            if($lineItem['line_item_type'] == 'shipping'){
                $attributes['shipping'][] = $lineItem['line_item_id'];
            }
        }

        $this->replace($attributes);

        return parent::all($keys);
    }
}
