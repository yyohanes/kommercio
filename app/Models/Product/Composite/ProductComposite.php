<?php

namespace Kommercio\Models\Product\Composite;

use Dimsav\Translatable\Translatable;
use Illuminate\Support\Facades\Cache;
use Kommercio\Facades\RuntimeCache;
use Kommercio\Models\Abstracts\SluggableModel;
use Kommercio\Models\Interfaces\CacheableInterface;
use Kommercio\Models\Product;

class ProductComposite extends SluggableModel implements CacheableInterface
{
    use Translatable;

    public $translatedAttributes = ['label'];

    public $fillable = ['name', 'slug', 'label', 'minimum', 'maximum', 'sort_order', 'free'];
    protected $casts = [
        'free' => 'boolean'
    ];

    private $_isSingle;

    public function getCacheKeys()
    {
        $tableName = $this->getTable();

        return [
            $tableName . '_' . $this->id . '_default_products',
            $tableName . '_' . $this->id . '_product_selections',
        ];
    }

    //Methods
    public function getProductSelection()
    {
        $tableName = $this->getTable();

        $results = Cache::remember(
            $tableName . '_' . $this->id . '_product_selections',
            3600,
            function() {
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

                foreach ($includedProducts as $idx => $includedProduct) {
                    if (!$includedProduct->productDetail->active){
                        $includedProducts->forget($idx);
                    }
                }

                return $includedProducts;
            }
        );

        return $results;
    }

    public function getDefaultProducts()
    {
        $tableName = $this->getTable();

        $results = Cache::remember(
            $tableName . '_' . $this->id . '_default_products',
            3600,
            function() {
                $includedProducts = $this->isSingle?$this->products:$this->defaultProducts;

                foreach($includedProducts as $idx => $includedProduct){
                    if(!$includedProduct->productDetail->active){
                        $includedProducts->forget($idx);
                    }
                }

                return $includedProducts;
            }
        );

        return $results;
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
