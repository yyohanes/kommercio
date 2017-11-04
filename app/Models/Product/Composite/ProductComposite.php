<?php

namespace Kommercio\Models\Product\Composite;

use Dimsav\Translatable\Translatable;
use Kommercio\Facades\RuntimeCache;
use Kommercio\Models\Abstracts\SluggableModel;
use Kommercio\Models\Product;

class ProductComposite extends SluggableModel
{
    use Translatable;

    public $translatedAttributes = ['label'];

    protected $fillable = ['name', 'slug', 'label', 'minimum', 'maximum', 'sort_order', 'free'];
    protected $casts = [
        'free' => 'boolean'
    ];

    private $_isSingle;

    //Methods
    public function getProductSelection()
    {
        $results = RuntimeCache::getOrSet('product_composite_'.$this->id.'_products', function(){
            $includedProducts = collect([]);

            foreach($this->products as $configuredProduct){
                if($configuredProduct->isPurchaseable){
                    $includedProducts->push($configuredProduct);
                }else{
                    $includedProducts = $includedProducts->merge($configuredProduct->variations);
                }
            }

            if($this->productCategories->count() > 0){
                $categoryProducts = Product::joinDetail()->selectSelf()->whereHas('categories', function($query){
                    $query->whereIn('id', $this->productCategories->pluck('id')->all());
                });

                $includedProducts = $includedProducts->merge($categoryProducts->get());
            }

            foreach($includedProducts as $idx => $includedProduct){
                if(!$includedProduct->productDetail->active){
                    $includedProducts->forget($idx);
                }
            }

            return $includedProducts;
        });

        return $results;
    }

    public function getDefaultProducts()
    {
        $includedProducts = $this->isSingle?$this->products:$this->defaultProducts;

        foreach($includedProducts as $idx => $includedProduct){
            if(!$includedProduct->productDetail->active){
                $includedProducts->forget($idx);
            }
        }

        return $includedProducts;
    }

    //Accessors
    public function getIsSingleAttribute()
    {
        if(!isset($this->_isSingle)){
            if($this->productCategories->count() == 0 && $this->products->count() == 1 && $this->products->get(0)->isPurchaseable && $this->minimum > 0 && $this->minimum == $this->maximum){
                $this->_isSingle = true;
            }
        }

        return $this->_isSingle;
    }

    public function getProductAttribute()
    {
        return $this->products->first();
    }

    //Relations
    public function groups()
    {
        return $this->belongsToMany('Kommercio\Models\Product\Composite\ProductCompositeGroup');
    }

    public function defaultProducts()
    {
        return $this->belongsToMany('Kommercio\Models\Product', 'product_composite_default_product')->withPivot('sort_order', 'quantity')->orderBy('sort_order', 'ASC');
    }

    public function products()
    {
        return $this->belongsToMany('Kommercio\Models\Product')->withPivot('sort_order')->orderBy('sort_order', 'ASC');
    }

    public function productCategories()
    {
        return $this->belongsToMany('Kommercio\Models\ProductCategory');
    }
}
