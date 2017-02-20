<?php

namespace Kommercio\Models\Product\Composite;

use Cviebrock\EloquentSluggable\SluggableInterface;
use Cviebrock\EloquentSluggable\SluggableTrait;
use Dimsav\Translatable\Translatable;
use Illuminate\Database\Eloquent\Model;
use Kommercio\Models\Product;

class ProductComposite extends Model implements SluggableInterface
{
    use Translatable, SluggableTrait;

    public $translatedAttributes = ['label'];

    protected $fillable = ['name', 'slug', 'label', 'minimum', 'maximum', 'sort_order', 'free'];
    protected $sluggable = [
        'build_from' => 'name',
        'save_to'    => 'slug',
        'on_update' => TRUE,
    ];
    protected $casts = [
        'free' => 'boolean'
    ];

    private $_isSingle;

    //Methods
    public function getProductSelection()
    {
        $includedProductIds = [];

        foreach($this->products as $configuredProduct){
            if($configuredProduct->isPurchaseable){
                $includedProductIds[] = $configuredProduct->id;
            }else{
                $includedProductIds = array_merge($includedProductIds, $configuredProduct->variations->pluck('id')->all());
            }
        }

        if($this->productCategories->count() > 0){
            $categoryProducts = Product::whereHas('categories', function($query){
                $query->whereIn('id', $this->productCategories->pluck('id')->all());
            });

            $includedProductIds = array_merge($includedProductIds, $categoryProducts->pluck('id')->all());
        }

        $qb = Product::with('parent')->productSelection()->active();

        if($includedProductIds){
            $qb->whereIn('products.id', $includedProductIds);
        }

        $results = $qb->get();

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

    public function products()
    {
        return $this->belongsToMany('Kommercio\Models\Product')->withPivot('sort_order')->orderBy('sort_order', 'ASC');
    }

    public function productCategories()
    {
        return $this->belongsToMany('Kommercio\Models\ProductCategory');
    }
}
