<?php

namespace Kommercio\Http\Requests\Backend\Catalog;

use Kommercio\Http\Requests\Request;
use Kommercio\Models\Manufacturer;

class ProductFormRequest extends Request
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
        $allowedManufacturerOptions = implode(',', array_keys(Manufacturer::getOptions()));

        $rules = [
            'sku' => 'required|unique:products,sku'.(!empty($productId)?','.$productId:''),
            'combination_type' => (!empty($productId)?'':'required'),
            'name' => 'required',
            'slug' => 'required',
            'default_category' => 'required_with:categories',
            'manufacturer_id' => 'nullable|in:'.$allowedManufacturerOptions,
            'width' => 'nullable|numeric',
            'length' => 'nullable|numeric',
            'depth' => 'nullable|numeric',
            'weight' => 'nullable|numeric',
            'store_id' => 'required|integer',
            'productDetail.visibility' => 'required',
            'productDetail.available_date_from' => 'nullable|date_format:Y-m-d H:i',
            'productDetail.available_date_to' => 'nullable|date_format:Y-m-d H:i',
            'productDetail.active_date_from' => 'nullable|date_format:Y-m-d H:i',
            'productDetail.active_date_to' => 'nullable|date_format:Y-m-d H:i',
            'productDetail.retail_price' => 'required|numeric',
            'productDetail.manage_stock' => 'boolean',
            'productDetail.taxable' => 'boolean',
            'productDetail.sort_order' => 'nullable|integer',
            'stock' => 'nullable|numeric',
            'variation.*.stock' => 'nullable|numeric',
            'variation.*.productDetail.manage_stock' => 'boolean',
            'product_composite_group.*' => 'exists:product_composite_groups,id',
            'product_configuration_group.*' => 'exists:product_configuration_groups,id'
        ];

        if($this->filled('compositeConfigurations')){
            $rules['compositeConfigurations.*.name'] = 'required';
            $rules['compositeConfigurations.*.minimum'] = 'required|numeric|min:0';
            $rules['compositeConfigurations.*.maximum'] = 'required|numeric|min:0';

            foreach($this->input('compositeConfigurations', []) as $idx => $compositeConfiguration){
                $rules['composite_products_'.$idx.'_product'] = 'required|array';
                $rules['composite_products_'.$idx.'_product.*'] = 'nullable|numeric|exists:products,id';
            }
        }

        return $rules;
    }

    public function all($keys = null)
    {
        $attributes = parent::all($keys);

        if(!$this->filled('productDetail.active')){
            $attributes['productDetail']['active'] = 0;
        }
        if(!$this->filled('productDetail.available')){
            $attributes['productDetail']['available'] = 0;
        }
        if(!$this->filled('productDetail.new')){
            $attributes['productDetail']['new'] = 0;
        }
        if(!$this->filled('manufacturer_id')){
            $attributes['manufacturer_id'] = NULL;
        }
        if(!$this->filled('productDetail.taxable')){
            $attributes['productDetail']['taxable'] = 0;
        }
        if(!$this->filled('productDetail.sticky_line_item')){
            $attributes['productDetail']['sticky_line_item'] = 0;
        }
        if(!$this->filled('productDetail.manage_stock')){
            $attributes['productDetail']['manage_stock'] = 0;
        }
        if(!$this->input('productDetail.sort_order', null)){
            $attributes['productDetail']['sort_order'] = 0;
        }


        $this->replace($attributes);

        return parent::all($keys);
    }
}
