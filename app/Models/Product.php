<?php

namespace Kommercio\Models;

use Dimsav\Translatable\Translatable;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;
use Kommercio\Facades\ProjectHelper;
use Kommercio\Models\ProductAttribute\ProductAttributeValue;

class Product extends Model
{
    use SoftDeletes, Translatable;

    const TYPE_DEFAULT = 'default';

    const COMBINATION_TYPE_SINGLE = 'single';
    const COMBINATION_TYPE_VARIABLE = 'variable';
    const COMBINATION_TYPE_VARIATION = 'variation';

    protected $fillable = ['name', 'description_short', 'description', 'slug', 'manufacturer_id', 'meta_title', 'meta_description', 'locale',
        'sku', 'type', 'width', 'length', 'depth', 'weight'];
    protected $casts = [
        'active' => 'boolean',
        'available' => 'boolean',
    ];
    protected $dates = ['deleted_at'];
    protected $with = ['productDetail'];
    private $_warehouse;
    private $_store;
    private $_assessedCatalogPriceRules = [];

    public $translatedAttributes = ['name', 'description_short', 'description', 'slug', 'meta_title', 'meta_description', 'locale', 'thumbnail', 'thumbnails', 'images'];

    //Relations
    public function defaultCategory()
    {
        return $this->belongsTo('Kommercio\Models\ProductCategory', 'default_category_id');
    }

    public function categories()
    {
        return $this->belongsToMany('Kommercio\Models\ProductCategory', 'category_product');
    }

    public function manufacturer()
    {
        return $this->belongsTo('Kommercio\Models\Manufacturer');
    }

    public function productDetail()
    {
        return $this->hasOne('Kommercio\Models\ProductDetail')->where('store_id', $this->store->id);
    }

    public function parent()
    {
        return $this->belongsTo('Kommercio\Models\Product', 'parent_id');
    }

    public function variations()
    {
        return $this->hasMany('Kommercio\Models\Product', 'parent_id')->where('combination_type', self::COMBINATION_TYPE_VARIATION);
    }

    public function productAttributes()
    {
        return $this->belongsToMany('Kommercio\Models\ProductAttribute\ProductAttribute', 'product_product_attribute')->withPivot(['product_attribute_value_id'])->orderBy('sort_order', 'ASC');
    }

    public function productAttributeValues()
    {
        return $this->belongsToMany('Kommercio\Models\ProductAttribute\ProductAttributeValue', 'product_product_attribute')->withPivot(['product_attribute_id'])->orderBy('sort_order', 'ASC');
    }

    public function productFeatures()
    {
        return $this->belongsToMany('Kommercio\Models\ProductFeature\ProductFeature', 'product_product_feature')->withPivot(['product_feature_value_id'])->orderBy('sort_order', 'ASC');
    }

    public function productFeatureValues()
    {
        return $this->belongsToMany('Kommercio\Models\ProductFeature\ProductFeatureValue', 'product_product_feature')->withPivot(['product_feature_id'])->orderBy('sort_order', 'ASC');
    }

    public function priceRules()
    {
        if($this->combination_type == self::COMBINATION_TYPE_VARIATION){
            return $this->hasMany('Kommercio\Models\PriceRule', 'variation_id')->orderBy('created_at', 'DESC');
        }else{
            return $this->hasMany('Kommercio\Models\PriceRule')->orderBy('created_at', 'DESC');
        }
    }

    public function warehouses()
    {
        return $this->belongsToMany('Kommercio\Models\Warehouse')->withPivot('stock');
    }

    //Methods
    public function hasThumbnail()
    {
        return $this->thumbnails->count() > 0;
    }

    public function getRetailPrice()
    {
        if($this->combination_type == self::COMBINATION_TYPE_VARIATION){
            $price = $this->productDetail->retail_price?$this->productDetail->retail_price:$this->parent->productDetail->retail_price;
        }else{
            $price = $this->productDetail->retail_price;
        }

        $priceRules = $this->getSpecificPriceRules(FALSE);

        foreach($priceRules as $priceRule){
            if($priceRule->validateProduct($this)){
                $price = $priceRule->getValue($price);
            }
        }

        return $price;
    }

    public function getNetPrice($options=[])
    {
        $catalogPriceRules = $this->getCatalogPriceRules();

        $price = $this->getRetailPrice();

        $specificDiscountPriceRules = $this->getSpecificPriceRules(TRUE);

        foreach($specificDiscountPriceRules as $specificDiscountPriceRule){
            if($specificDiscountPriceRule->validateProduct($this)){
                $price = $specificDiscountPriceRule->getValue($price);
            }
        }

        foreach($catalogPriceRules as $catalogPriceRule){
            if($catalogPriceRule->validateProduct($this)){
                $price = $catalogPriceRule->getValue($price);
            }
        }

        return $price;
    }

    public function getProductAttributeWithValues()
    {
        if(!$this->relationLoaded('productAttributes')){
            $this->load('productAttributes');
        }

        $array = [];

        foreach($this->productAttributes as $productAttribute){
            $array[$productAttribute->id] = $productAttribute->pivot->productAttributeValue->id;
        }

        return $array;
    }

    public function getVariationsByAttributes($attributes, $attributeValues)
    {
        $variationsQb = $this->variations();

        $join = with(new self())->productAttributes();

        foreach($attributes as $attribute){
            $variationsQb->leftJoin($join->getTable().' AS A'.$attribute, 'A'.$attribute.'.product_id', '=', $join->getQualifiedParentKeyName());
            $variationsQb->where('A'.$attribute.'.product_attribute_value_id', $attributeValues[$attribute]);
        }

        $variations = $variationsQb->get();

        return $variations;
    }

    public function getProductFeatureValue($feature_id)
    {
        $features = $this->getProductFeaturesWithValues();

        $value = null;
        if(isset($features[$feature_id])){
            $value = $features[$feature_id];
        }

        return $value;
    }

    public function getProductFeaturesWithValues()
    {
        if(!$this->relationLoaded('productFeatures')){
            $this->load('productFeatures');
        }

        $array = [];

        foreach($this->productFeatures as $productFeature){
            $array[$productFeature->id] = $productFeature->pivot->productFeatureValue->id;
        }

        return $array;
    }

    public function getStock($warehouse_id=null)
    {
        $warehouses = $this->warehouses;

        if(!$warehouse_id){
            $warehouse_id = $this->warehouse->id;
        }

        $warehouse = $warehouses->find($warehouse_id);

        return $warehouse?$warehouse->pivot->stock+0:0;
    }

    public function saveStock($stock, $warehouse_id=null)
    {
        if(!is_null($stock)){
            $this->warehouses()->sync([
                $warehouse_id => ['stock' => $stock]
            ]);
        }
    }

    public function getSpecificPriceRules($is_discount = NULL)
    {
        $qb = $this->priceRules()->active();

        if($is_discount === TRUE){
            $qb->isDiscount();
        }elseif($is_discount === FALSE){
            $qb->isNotDiscount();
        }

        return $qb->get();
    }

    public function getCatalogPriceRules()
    {
        $qb = PriceRule::notProductSpecific()->active()->orderBy('sort_order', 'ASC');

        $qb->where(function($qb){
            $categories = $this->categories;
            $manufacturer = $this->manufacturer_id;
            $features = $this->productFeatureValues;
            $attributeValueIds = [];

            if($this->isVariation){
                $attributeValues = $this->productAttributeValues;
                $attributeValueIds = $attributeValues->pluck('id')->all();
            }else{
                if($this->variations->count() > 0){
                    $attributeValues = ProductAttributeValue::whereHas('products', function($query){
                        $query->whereIn('product_id', $this->variations->pluck('id')->all());
                    })->get();
                    $attributeValueIds = $attributeValues->pluck('id')->all();
                }
            }

            $firstValidation = true;

            if($categories->count() > 0){
                $validationFunction = $firstValidation?'whereHas':'orWhereHas';

                $qb->$validationFunction('priceRuleOptionGroups.categories', function($query) use ($categories){
                    $query->whereIn('id', $categories->pluck('id')->all());
                });
                $firstValidation = false;
            }

            if($features->count() > 0){
                $validationFunction = $firstValidation?'whereHas':'orWhereHas';

                $qb->$validationFunction('priceRuleOptionGroups.featureValues', function($query) use ($features){
                    $query->whereIn('id', $features->pluck('id')->all());
                });
                $firstValidation = false;
            }

            if($manufacturer){
                $validationFunction = $firstValidation?'whereHas':'orWhereHas';

                $qb->$validationFunction('priceRuleOptionGroups.manufacturers', function($query) use ($manufacturer){
                    $query->whereIn('id', [$manufacturer]);
                });
                $firstValidation = false;
            }

            if($attributeValueIds){
                $validationFunction = $firstValidation?'whereHas':'orWhereHas';

                $qb->$validationFunction('priceRuleOptionGroups.attributeValues', function($query) use ($attributeValueIds){
                    $query->whereIn('id', $attributeValueIds);
                });
                $firstValidation = false;
            }
        });

        $includedPriceRules = $qb->get();

        return $includedPriceRules;

        /*
        $includedPriceRules = [];

        //By Categories
        $categories = $this->categories;
        $categoryPriceRules = [];
        if($categories->count() > 0){
            $categoryPriceRules = PriceRule::notProductSpecific()->whereHas('priceRuleOptionGroups.categories', function($query) use ($categories){
                $query->whereIn('id', $categories->pluck('id')->all());
            })->get();

            foreach($categoryPriceRules as $categoryPriceRule){
                if(!isset($includedPriceRules[$categoryPriceRule->id])){
                    $includedPriceRules[$categoryPriceRule->id] = $categoryPriceRule;
                }
            }
        }

        //By manufacturer
        $manufacturer = $this->manufacturer_id;
        $manufacturerPriceRules = [];
        if($manufacturer){
            $manufacturerPriceRules = PriceRule::notProductSpecific()->whereHas('priceRuleOptionGroups.manufacturers', function($query) use ($manufacturer){
                $query->whereIn('id', [$manufacturer]);
            })->get();

            foreach($manufacturerPriceRules as $manufacturerPriceRule){
                if(!isset($includedPriceRules[$manufacturerPriceRule->id])){
                    $includedPriceRules[$manufacturerPriceRule->id] = $manufacturerPriceRule;
                }
            }
        }

        //By attribute
        $attributePriceRules = [];
        $attributeValueIds = [];
        if($this->isVariation){
            $attributeValues = $this->productAttributeValues;
            $attributeValueIds = $attributeValues->pluck('id')->all();
        }else{
            if($this->variations->count() > 0){
                $attributeValues = ProductAttributeValue::whereHas('products', function($query){
                    $query->whereIn('product_id', $this->variations->pluck('id')->all());
                })->get();
                $attributeValueIds = $attributeValues->pluck('id')->all();
            }
        }

        if(count($attributeValueIds) > 0){
            $attributePriceRules = PriceRule::notProductSpecific()->whereHas('priceRuleOptionGroups.attributeValues', function($query) use ($attributeValueIds){
                $query->whereIn('id', $attributeValueIds);
            })->get();

            foreach($attributePriceRules as $attributePriceRule){
                if(!isset($includedPriceRules[$attributePriceRule->id])){
                    $includedPriceRules[$attributePriceRule->id] = $attributePriceRule;
                }
            }
        }

        //By features
        $features = $this->productFeatureValues;
        $featurePriceRules = [];
        if($features->count() > 0){
            $featurePriceRules = PriceRule::notProductSpecific()->whereHas('priceRuleOptionGroups.featureValues', function($query) use ($features){
                $query->whereIn('id', $features->pluck('id')->all());
            })->get();

            foreach($featurePriceRules as $featurePriceRule){
                if(!isset($includedPriceRules[$featurePriceRule->id])){
                    $includedPriceRules[$featurePriceRule->id] = $featurePriceRule;
                }
            }
        }

        return $includedPriceRules;
        */
    }

    //Accessors
    public function getIsVariationAttribute()
    {
        return $this->combination_type == self::COMBINATION_TYPE_VARIATION;
    }

    public function getStoreAttribute()
    {
        if(!$this->_store){
            $this->_store = ProjectHelper::getActiveStore();
        }

        return $this->_store;
    }

    public function getWarehouseAttribute()
    {
        if(!$this->_warehouse){
            $store = $this->store;

            $this->_warehouse = $store->getDefaultWarehouse();
        }

        return $this->_warehouse;
    }

    //Mutators
    public function setStoreAttribute($store_id)
    {
        $store = Store::find($store_id);
        $this->_store = $store;
    }

    public function setWarehouseAttribute($warehouse_id)
    {
        $warehouse = Warehouse::find($warehouse_id);

        $this->_warehouse = $warehouse;
    }

    //Scopes
    public function scopeProductEntity($query)
    {
        $query->whereNotIn('combination_type', [self::COMBINATION_TYPE_VARIATION]);
    }

    public function scopeProductSelection($query)
    {
        $query->whereNotIn('combination_type', [self::COMBINATION_TYPE_VARIABLE]);
    }

    public function scopeJoinTranslation($query, $locale=null)
    {
        $locale = $locale?$locale:$this->locale();

        $query
            ->leftJoin($this->getTranslationsTable().' as T', function($join) use ($locale){
                $join->on('T.product_id', '=', $this->getTable().'.id')
                    ->where('T.'.$this->getLocaleKey(), '=', $locale);
            });
    }

    public function scopeJoinDetail($query, $store=null)
    {
        $store = $store?$store:ProjectHelper::getActiveStore()->id;

        $productDetailTable = $this->productDetail()->getRelated()->getTable();

        $query->leftJoin($productDetailTable.' AS D', function($join) use ($productDetailTable, $store){
            $join->on('D.'.$this->productDetail()->getPlainForeignKey(), '=', $this->getTable().'.id')
                ->where('D.store_id', '=', $store);
        });
    }

    public function scopeWithDetail($query, $store=null)
    {
        $store = $store?$store:ProjectHelper::getActiveStore()->id;

        $query->with(['productDetail' => function($query) use ($store){
            $query->where('store_id', $store);
        }]);
    }

    public function scopeWhereDetail($query, $key, $value, $operator='=', $store=null)
    {
        $store = $store?$store:ProjectHelper::getActiveStore()->id;

        $query->whereHas('productDetail', function($query) use ($key, $value, $operator, $store){
            $query->where('store_id', $store)->where($key, $operator, $value);
        });
    }

    //Statics
    public static function getTypeOptions($option=null)
    {
        $array = [
            self::TYPE_DEFAULT => 'Default',
        ];

        if(empty($option)){
            return $array;
        }

        return (isset($array[$option]))?$array[$option]:$array;
    }

    public static function getCombinationTypeOptions($option=null)
    {
        $array = [
            self::COMBINATION_TYPE_SINGLE => 'Single Product',
            self::COMBINATION_TYPE_VARIABLE => 'Variable Product',
        ];

        if(empty($option)){
            return $array;
        }

        return (isset($array[$option]))?$array[$option]:$array;
    }
}
