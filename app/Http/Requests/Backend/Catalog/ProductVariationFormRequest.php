<?php

namespace Kommercio\Http\Requests\Backend\Catalog;

use Kommercio\Http\Requests\Request;
use Kommercio\Models\ProductAttribute\ProductAttribute;

class ProductVariationFormRequest extends Request
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
        $productId = $this->route('id');
        $productVariationId = $this->route('variation_id');

        $rules = [
            'variation.sku' => 'required|unique:products,sku'.(!empty($productVariationId)?','.$productVariationId:''),
            'variation.width' => 'numeric',
            'variation.length' => 'numeric',
            'variation.depth' => 'numeric',
            'variation.weight' => 'numeric',
            'variation.productDetail.available_date_from' => 'date_format:Y-m-d H:i',
            'variation.productDetail.available_date_to' => 'date_format:Y-m-d H:i',
            'variation.productDetail.active_date_from' => 'date_format:Y-m-d H:i',
            'variation.productDetail.active_date_to' => 'date_format:Y-m-d H:i',
            'variation.productDetail.retail_price' => 'numeric',
        ];

        $attributeCount = 0;
        foreach($this->input('variation.attributes', []) as $attributeId => $attribute){
            $attributeCount += 1;

            $productAttribute = ProductAttribute::findOrFail($attributeId);
            $allowedAttributeOptions = $productAttribute->values->pluck('id')->all();
            $allowedAttributeOptions = implode(',', $allowedAttributeOptions);

            $rules['variation.attributes.'.$attributeId] = 'required|in:'.$allowedAttributeOptions;
        }

        if($this->has('variation.attributes')){
            $rules['variation.attributes'] = 'product_attributes:'.$productId.(!empty($productVariationId)?','.$productVariationId:'');
        }

        return $rules;
    }

    public function all()
    {
        $attributes = parent::all();

        if(!$this->has('variation.productDetail.active')){
            $attributes['variation']['productDetail']['active'] = 0;
        }
        if(!$this->has('variation.productDetail.available')){
            $attributes['variation']['productDetail']['available'] = 0;
        }

        if(!$this->has('variation.productDetail.retail_price')){
            $attributes['variation']['productDetail']['retail_price'] = null;
        }


        $this->replace($attributes);

        return parent::all();
    }
}