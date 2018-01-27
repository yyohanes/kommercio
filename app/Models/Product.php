<?php

namespace Kommercio\Models;

use Carbon\Carbon;
use Dimsav\Translatable\Translatable;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;
use Illuminate\Support\Facades\Cache;
use Illuminate\Support\Facades\DB;
use Kommercio\Facades\CurrencyHelper;
use Kommercio\Facades\FrontendHelper;
use Kommercio\Facades\ProductIndexHelper;
use Kommercio\Facades\ProjectHelper;
use Kommercio\Facades\RuntimeCache;
use Kommercio\Models\Interfaces\CacheableInterface;
use Kommercio\Models\Interfaces\SeoModelInterface;
use Kommercio\Models\Interfaces\UrlAliasInterface;
use Kommercio\Models\Order\LineItem;
use Kommercio\Models\Order\Order;
use Kommercio\Models\Order\OrderLimit;
use Kommercio\Models\ProductAttribute\ProductAttribute;
use Kommercio\Models\ProductAttribute\ProductAttributeValue;
use Kommercio\Models\RewardPoint\RewardRule;
use Kommercio\Traits\Frontend\ProductHelper as FrontendProductHelper;
use Kommercio\Traits\Model\OrderLimitTrait;
use Kommercio\Traits\Model\SeoTrait;
use Kommercio\Facades\PriceFormatter;

class Product extends Model implements UrlAliasInterface, SeoModelInterface, CacheableInterface
{
    use SoftDeletes, Translatable, SeoTrait, FrontendProductHelper, OrderLimitTrait;

    const TYPE_DEFAULT = 'default';

    const COMBINATION_TYPE_SINGLE = 'single';
    const COMBINATION_TYPE_VARIABLE = 'variable';
    const COMBINATION_TYPE_VARIATION = 'variation';

    protected $fillable = ['name', 'description_short', 'description', 'slug', 'manufacturer_id', '', 'meta_description', 'locale',
        'sku', 'type', 'width', 'length', 'depth', 'weight', 'combination_type'];
    protected $dates = ['deleted_at'];
    private $_warehouse;
    private $_store;
    private $_currency;
    private $_productDetail;
    private $_rewardPoints;

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

    public function productDetails()
    {
        return $this->hasMany('Kommercio\Models\ProductDetail');
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
        return $this->belongsToMany('Kommercio\Models\ProductAttribute\ProductAttributeValue', 'product_product_attribute')
            ->withPivot(['product_attribute_id'])
            ->orderBy('sort_order', 'ASC');
    }

    public function productFeatures()
    {
        return $this->belongsToMany('Kommercio\Models\ProductFeature\ProductFeature', 'product_product_feature')->withPivot(['product_feature_value_id'])->orderBy('sort_order', 'ASC');
    }

    public function productFeatureValues()
    {
        return $this->belongsToMany('Kommercio\Models\ProductFeature\ProductFeatureValue', 'product_product_feature')->withPivot(['product_feature_id'])->orderBy('sort_order', 'ASC');
    }

    public function productCompositeGroups()
    {
        return $this->belongsToMany('Kommercio\Models\Product\Composite\ProductCompositeGroup', 'product_composite_group_product')->withPivot('sort_order')->orderBy('sort_order', 'ASC');
    }

    public function productConfigurationGroups()
    {
        return $this->belongsToMany('Kommercio\Models\Product\Configuration\ProductConfigurationGroup', 'product_configuration_group_product')->withPivot('sort_order')->orderBy('sort_order', 'ASC');
    }

    public function priceRules()
    {
        if($this->combination_type == self::COMBINATION_TYPE_VARIATION){
            return $this->hasMany('Kommercio\Models\PriceRule', 'variation_id')->orderBy('created_at', 'DESC');
        }else{
            return $this->hasMany('Kommercio\Models\PriceRule')->orderBy('created_at', 'DESC');
        }
    }

    public function cartPriceRules()
    {
        return $this->belongsToMany('Kommercio\Models\PriceRule\CartPriceRule');
    }

    public function warehouses()
    {
        return $this->belongsToMany('Kommercio\Models\Warehouse')->withPivot('stock');
    }

    public function orderLimits()
    {
        return $this->morphToMany('Kommercio\Models\Order\OrderLimit', 'order_limitable');
    }

    public function crossSellTo()
    {
        return $this->belongsToMany('Kommercio\Models\Product', 'related_products', 'product_id', 'target_id')->withPivot(['sort_order', 'type'])->orderBy('sort_order', 'ASC')->wherePivot('type', 'cross_sell');
    }

    public function crossSellBy()
    {
        return $this->belongsToMany('Kommercio\Models\Product', 'related_products', 'target_id', 'product_id')->withPivot(['sort_order', 'type'])->orderBy('sort_order', 'ASC')->wherePivot('type', 'cross_sell');
    }

    public function bookmarks()
    {
        return $this->belongsToMany('Kommercio\Models\Customer\Bookmark');
    }

    //Methods
    public function getStoreProductDetailOrNew($store_id)
    {
        $productDetail = $this->productDetails()->where('store_id', $store_id)->first();

        if(!$productDetail){
            $productDetail = new ProductDetail([
                'store_id' => $store_id,
            ]);

            $productDetail->product()->associate($this);
        }

        return $productDetail;
    }

    public function getExternalPath()
    {
        if($this->isVariation){
            $path = $this->getInternalPathSlug().'/'.$this->parent->id;
        }else{
            $path = $this->getInternalPathSlug().'/'.$this->id;
        }

        return FrontendHelper::getUrl($path);
    }

    public function getUrlAlias()
    {
        $paths = [];

        $category = $this->defaultCategory;

        if($category){
            $paths[] = $category->getUrlAlias();
        }

        $paths[] = $this->slug;

        return implode('/', $paths);
    }

    public function getInternalPathSlug()
    {
        return 'product';
    }

    public function getBreadcrumbTrails()
    {
        $defaultCategory = $this->defaultCategory;

        if($defaultCategory){
            $breadcrumbs = $defaultCategory->getBreadcrumbTrails();
            $breadcrumbs[] = $defaultCategory;
        }else{
            $breadcrumbs = [];
        }

        return $breadcrumbs;
    }

    public function hasCategory($category)
    {
        if(is_int($category)){
            foreach($this->categories as $categoryObj){
                if($categoryObj->id == $category){
                    return true;
                }
            }
        }elseif(is_string($category)){
            foreach($this->categories as $categoryObj){
                if($categoryObj->slug == $category){
                    return true;
                }
            }
        }else{
            foreach($this->categories as $categoryObj){
                if($categoryObj->id == $category->id){
                    return true;
                }
            }
        }

        return false;
    }

    public function hasProductAttribute($productAttribute)
    {
        if($this->isVariation){
            if(is_int($productAttribute)){
                foreach($this->productAttributes as $productAttributeObj){
                    if($productAttributeObj->id == $productAttribute){
                        return true;
                    }
                }
            }elseif(is_string($productAttribute)){
                foreach($this->productAttributes as $productAttributeObj){
                    if($productAttributeObj->slug == $productAttribute){
                        return true;
                    }
                }
            }else{
                foreach($this->productAttributes as $productAttributeObj){
                    if($productAttributeObj->id == $productAttribute->id){
                        return true;
                    }
                }
            }

            return true;
        }else{
            $availableAttributeValues = $this->getAvailableAttributeValues();

            if(is_int($productAttribute)){
                $identifier = $productAttribute;
            }elseif(is_string($productAttribute)){
                $object = ProductAttribute::whereTranslation('slug', $productAttribute)->first();
                $identifier = $object->id;
            }else{
                $identifier = $productAttribute->id;
            }

            return isset($availableAttributeValues[$identifier]);
        }
    }

    public function hasProductAttributeValue($productAttributeValue)
    {
        if($this->isVariation){
            if(is_int($productAttributeValue)){
                foreach($this->productAttributeValues as $productAttributeValueObj){
                    if($productAttributeValueObj->id == $productAttributeValue){
                        return true;
                    }
                }
            }elseif(is_string($productAttributeValue)){
                foreach($this->productAttributeValues as $productAttributeValueObj){
                    if($productAttributeValueObj->slug == $productAttributeValue){
                        return true;
                    }
                }
            }else{
                foreach($this->productAttributeValues as $productAttributeValueObj){
                    if($productAttributeValueObj->id == $productAttributeValue->id){
                        return true;
                    }
                }
            }

            return false;
        }else{
            $availableAttributeValues = [];

            foreach($this->getAvailableAttributeValues() as $attributeValues){
                $availableAttributeValues = array_merge($availableAttributeValues, array_keys($attributeValues));
            }

            if(is_int($productAttributeValue)){
                $identifier = $productAttributeValue;
            }elseif(is_string($productAttributeValue)){
                $object = ProductAttributeValue::whereTranslation('slug', $productAttributeValue)->first();
                $identifier = $object->id;
            }else{
                $identifier = $productAttributeValue->id;
            }

            return in_array($identifier, $availableAttributeValues);
        }
    }

    public function getRetailPrice($tax = false)
    {
        $retailPrice = Cache::rememberForever($this->getTable().'_'.$this->productDetail->id.'_'.$this->id.'.retail_price', function(){
            if($this->combination_type == self::COMBINATION_TYPE_VARIATION){
                $price = $this->productDetail->retail_price?$this->productDetail->retail_price:$this->parent->productDetail->retail_price;
            }else{
                $price = isset($this->productDetail)?$this->productDetail->retail_price:null;
            }

            $priceRules = $this->getSpecificPriceRules(FALSE);

            foreach($priceRules as $priceRule){
                $price = $priceRule->getValue($price);
            }

            return $price;
        });

        if($tax && $this->productDetail->taxable){
            $retailPrice += $this->calculateTax($retailPrice);
        }

        return $retailPrice;
    }

    public function getNetPrice($tax = false)
    {
        $netPrice = Cache::rememberForever($this->getTable().'_'.$this->productDetail->id.'_'.$this->id.'.net_price', function(){
            return $this->_calculateNetPrice();
        });

        if($tax && $this->productDetail->taxable){
            $netPrice += $this->calculateTax($netPrice);
        }

        return $netPrice;
    }

    private function _calculateNetPrice()
    {
        $catalogPriceRules = $this->getCatalogPriceRules();

        $price = $this->getRetailPrice();

        $specificDiscountPriceRules = $this->getSpecificPriceRules(TRUE);

        foreach($catalogPriceRules as $catalogPriceRule){
            if ($catalogPriceRule->validateProduct($this)) {
                $price = $catalogPriceRule->getValue($price);
            }
        }

        foreach($specificDiscountPriceRules as $specificDiscountPriceRule){
            $price = $specificDiscountPriceRule->getValue($price);
        }

        return $price;
    }

    public function getOldPrice($tax = false)
    {
        if($this->getRetailPrice($tax) - $this->getNetPrice($tax) == 0){
            return FALSE;
        }

        return $this->getRetailPrice($tax);
    }

    public function getRewardPoints()
    {
        if(!isset($this->_rewardPoints)){
            $data = [
                'currency' => $this->currency['code'],
                'store_id' => $this->store->id,
            ];

            $rewardRules = RewardRule::getRewardRules($data);

            $rewardPoints = 0;

            foreach($rewardRules as $rewardRule){
                $rewardPoints += $rewardRule->calculateProductRewardPoint($this);
            }

            $this->_rewardPoints = $rewardPoints;
        }

        return $this->_rewardPoints;
    }

    public function getAvailableAttributeValues()
    {
        $array = [];

        if($this->combination_type == self::COMBINATION_TYPE_VARIABLE){
            foreach($this->variations as $variation){
                foreach($variation->productAttributeValues as $productAttributeValue){
                    $array[$productAttributeValue->product_attribute_id][$productAttributeValue->id] = $productAttributeValue;
                }
            }
        }else{
            foreach($this->productAttributeValues as $productAttributeValue){
                $array[$productAttributeValue->product_attribute_id][$productAttributeValue->id] = $productAttributeValue;
            }
        }

        foreach($array as &$productAttribute){
            foreach($productAttribute as &$productAttributeValues){
                $productAttributeValues = collect($productAttributeValues)->sortBy('sort_order');
            }
        }

        return $array;
    }

    public function getProductAttributeValue($attribute)
    {
        $productAttributeValue = null;

        if(is_string($attribute)){
            $attribute = $this->attributes->where('slug', $attribute)->first();
            $attribute = $attribute->id;
        }elseif(is_object($attribute)){
            $attribute = $attribute->id;
        }

        foreach($this->productAttributeValues as $productAttributeValue){
            if($attribute == $productAttributeValue->product_attribute_id){
                return $productAttributeValue;
            }
        }

        return $productAttributeValue;
    }

    public function getSiblingByAttribute($attribute, $attributeValue, $isActive = true)
    {
        if($this->combination_type == self::COMBINATION_TYPE_VARIATION){
            $variations = $this->parent->variations;
        }else{
            $variations = $this->variations;
        }

        $sibling = null;

        $compareableValues = [];

        foreach($this->productAttributeValues as $productAttributeValue){
            if($productAttributeValue->pivot->product_attribute_id != $attribute){
                $compareableValues[] = $productAttributeValue->id;
            }else{
                $compareableValues[] = $attributeValue;
            }
        }

        $compareableValuesCount = count($compareableValues);

        foreach($variations as $variation){
            if($variation->productDetail->active && count(array_intersect($compareableValues, $variation->productAttributeValues->pluck('id')->all())) == $compareableValuesCount){
                $sibling = $variation;
                break;
            }
        }

        return $sibling;
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

    public function getVariationsByAttribute($attribute)
    {
        if(is_string($attribute)){
            $attribute = ProductAttribute::whereTranslation('slug', $attribute)->firstOrFail();
        }elseif(is_int($attribute)){
            $attribute = ProductAttribute::findOrFail($attribute);
        }

        $variationsQb = $this->variations();

        $join = with(new self())->productAttributes();

        $variationsQb->leftJoin($join->getTable().' AS A'.$attribute->id, 'A'.$attribute->id.'.product_id', '=', $join->getQualifiedParentKeyName());
        $variationsQb->where('A'.$attribute->id.'.product_attribute_id', $attribute->id);

        $variations = $variationsQb->get();

        return $variations;
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

    public function getVariationsByAttributeValue($attributeValue)
    {
        if(is_string($attributeValue)){
            $attributeValue = ProductAttributeValue::whereTranslation('slug', $attributeValue)->firstOrFail();
        }elseif(is_int($attributeValue)){
            $attributeValue = ProductAttributeValue::findOrFail($attributeValue);
        }

        $variationsQb = $this->variations();

        $join = with(new self())->productAttributeValues();

        $variationsQb->join($join->getTable().' AS A'.$attributeValue->id, 'A'.$attributeValue->id.'.product_id', '=', $join->getQualifiedParentKeyName());
        $variationsQb->where('A'.$attributeValue->id.'.product_attribute_value_id', $attributeValue->id);

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

    protected function calculateTax($price)
    {
        return PriceFormatter::calculateTax($price, [
            'store' => $this->store
        ]);
    }

    public function getStock($warehouse_id=null)
    {
        if($this->productDetail && !$this->productDetail->manage_stock){
            return null;
        }

        if(!$warehouse_id){
            $warehouse_id = $this->warehouse->id;
        }

        $warehouses = $this->warehouses;

        $warehouse = $warehouses->find($warehouse_id);

        return $warehouse?$warehouse->pivot->stock+0:0;
    }

    public function checkStock($amount, $warehouse_id=null)
    {
        $productDetail = $this->productDetail;

        if(!$warehouse_id){
            $defaultWarehouse = $this->store->getDefaultWarehouse();
            $warehouse_id = $defaultWarehouse?$defaultWarehouse->id:null;
        }

        if($productDetail->manage_stock && $warehouse_id){
            $existingStock = $this->getStock($warehouse_id);

            return $existingStock - $amount >= 0;
        }

        return TRUE;
    }

    public function increaseStock($amount, $warehouse_id=null)
    {
        if(!$warehouse_id){
            $defaultWarehouse = $this->store->getDefaultWarehouse();
            $warehouse_id = $defaultWarehouse?$defaultWarehouse->id:null;
        }

        $productDetail = $this->productDetail;

        if($productDetail->manage_stock && $warehouse_id){
            $existingStock = $this->getStock($warehouse_id);

            $this->saveStock(($existingStock + $amount + 0), $warehouse_id);
        }
    }

    public function reduceStock($amount, $warehouse_id=null)
    {
        if(!$warehouse_id){
            $defaultWarehouse = $this->store->getDefaultWarehouse();
            $warehouse_id = $defaultWarehouse?$defaultWarehouse->id:null;
        }

        $productDetail = $this->productDetail;

        if($productDetail->manage_stock && $warehouse_id){
            $existingStock = $this->getStock($warehouse_id);

            $this->saveStock(($existingStock - $amount + 0), $warehouse_id);
        }
    }

    public function saveStock($stock, $warehouse_id=null)
    {
        if(!is_null($stock)){
            if(!$warehouse_id){
                $defaultWarehouse = $this->store->getDefaultWarehouse();
                $warehouse_id = $defaultWarehouse?$defaultWarehouse->id:null;
            }

            if($warehouse_id){
                $this->warehouses()->sync([
                    $warehouse_id => ['stock' => $stock]
                ]);
            }
        }
    }

    public function getSpecificPriceRules($is_discount = NULL)
    {
        //Get parent all attributes price rules if variation
        if($this->combination_type == self::COMBINATION_TYPE_VARIATION){
            $qb = $this->parent->priceRules()
                ->whereNull('variation_id')
                ->where(function($query){
                    $query->whereNull('currency');

                    $query->orWhere('currency', $this->currency['code']);
                })
                ->where(function($query){
                    $query->whereNull('store_id');

                    $query->orWhere('store_id', $this->store->id);
                })
                ->active();

            if($is_discount === TRUE){
                $qb->isDiscount();
            }elseif($is_discount === FALSE){
                $qb->isNotDiscount();
            }

            $parentPriceRules = $qb->get();
        }

        $qb = $this->priceRules()
            ->where(function($query){
                $query->whereNull('currency');

                $query->orWhere('currency', $this->currency['code']);
            })
            ->where(function($query){
                $query->whereNull('store_id');

                $query->orWhere('store_id', $this->store->id);
            })
            ->active();

        if($is_discount === TRUE){
            $qb->isDiscount();
        }elseif($is_discount === FALSE){
            $qb->isNotDiscount();
        }

        $priceRules = $qb->get();

        if(isset($parentPriceRules)){
            $priceRules = $priceRules->merge($parentPriceRules);
        }

        return $priceRules;
    }

    public function getCatalogPriceRules()
    {
        /*
         * OR per Option Group
         */
        $qb = PriceRuleOptionGroup::query();

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

        $qb->where(function($query) use ($categories){
            $query->whereDoesntHave('categories');

            if($categories->count() > 0){
                $query->orWhereHas('categories', function($query) use ($categories){
                    $query->whereIn('id', $categories->pluck('id')->all());
                });
            }
        });

        $qb->where(function($query) use ($manufacturer){
            $query->whereDoesntHave('manufacturers');

            if($manufacturer){
                $query->orWhereHas('manufacturers', function($query) use ($manufacturer){
                    $query->whereIn('id', [$manufacturer]);
                });
            }
        });

        $qb->where(function($query) use ($attributeValueIds){
            $query->whereDoesntHave('attributeValues');

            if(count($attributeValueIds) > 0){
                $query->orWhereHas('attributeValues', function($query) use ($attributeValueIds){
                    $query->whereIn('id', $attributeValueIds);
                });
            }
        });

        $qb->where(function($query) use ($features){
            $query->whereDoesntHave('featureValues');

            if($features->count() > 0){
                $query->orWhereHas('featureValues', function($query) use ($features){
                    $query->whereIn('id', $features->pluck('id')->all());
                });
            }
        });

        $priceRuleIds = $qb->pluck('price_rule_id')->all();

        $priceRuleQb = PriceRule::where(function($query){
                $query->whereNull('currency');

                $query->orWhere('currency', $this->currency['code']);
            })
            ->where(function($query){
                $query->whereNull('store_id');

                $query->orWhere('store_id', $this->store->id);
            })
            ->notProductSpecific()
            ->active()
            ->orderBy('sort_order', 'ASC');

        if(count($priceRuleIds) > 0){
            $priceRuleQb->whereIn('id', $priceRuleIds);
        }

        $priceRules = $priceRuleQb->get();

        return $priceRules;
    }

    /**
     * Get number of products purchased
     *
     * @param array $options Possible options are store_id, checkout_at, delivery_date, exclude_order_id
     * @return float
     */
    public function getOrderCount($options = [])
    {
        // If both delivery_date and checkout_at are not present, return 0
        if (empty($countOptions['delivery_date']) && empty($countOptions['checkout_at'])) {
            return 0;
        }

        $countOptions = $options + ['product_id' => $this->id];

        if(isset($countOptions['exclude_order_id'])){
            unset($countOptions['exclude_order_id']);
        }

        $total = Cache::rememberForever('product_order_count_' . ProjectHelper::flattenArrayToKey($countOptions), function() use ($countOptions){
            $lineItemQb = LineItem::isProduct($this->id)
                ->isRoot()
                ->join('orders as O', 'O.id', '=', 'line_items.order_id')
                ->whereIn('O.status', Order::getUsageCountedStatus());

            if(!empty($countOptions['store_id'])){
                $lineItemQb->where('O.store_id', $countOptions['store_id']);
            }

            if(!empty($countOptions['delivery_date'])){
                $lineItemQb->whereRaw('DATE_FORMAT(O.delivery_date, \'%Y-%m-%d\') = ?', [$countOptions['delivery_date']]);
            }

            if(!empty($countOptions['checkout_at'])){
                $lineItemQb->whereRaw('DATE_FORMAT(O.checkout_at, \'%Y-%m-%d\') = ?', [$countOptions['checkout_at']]);
            }

            $updatedCount = $lineItemQb->sum('quantity');

            return $updatedCount;
        });

        if(!empty($options['exclude_order_id'])){
            $excludedOrder = Order::findOrFail($options['exclude_order_id']);
            $total -= $excludedOrder->getProductQuantity($this->id);
        }

        return $total;
    }

    public function getPerOrderLimit($options = [])
    {
        $store = isset($options['store'])?$options['store']:null;
        $date = isset($options['date'])?Carbon::createFromFormat('Y-m-d', $options['date']):null;

        // Per Order Limit
        $orderLimits = OrderLimit::getOrderLimits([
            'limit_type' => OrderLimit::LIMIT_PER_ORDER,
            'date' => $date,
            'store' => $store,
            'type' => OrderLimit::TYPE_PRODUCT,
            'product' => $this
        ]);

        $orderLimit = (count($orderLimits) > 0)?$this->extractOrderLimit($orderLimits):null;

        return $orderLimit?['limit_type' => $orderLimit->type, 'limit' => $orderLimit->limit, 'object' => $orderLimit]:null;
    }

    public function getOrderLimit($options = [])
    {
        if (ProjectHelper::cacheIsTaggable()) {
            $hash = ProjectHelper::flattenArrayToKey($options);

            $orderLimit = Cache::tags(['order_limits'])->rememberForever($hash, function() use ($options) {
                return $this->_getOrderLimit($options);
            });

            return $orderLimit;
        }

        return $this->_getOrderLimit($options);
    }

    protected function _getOrderLimit($options = [])
    {
        $store = !empty($options['store'])?$options['store']:null;
        $date = !empty($options['date'])?Carbon::createFromFormat('Y-m-d', $options['date']):null;
        $deliveryDate = !empty($options['delivery_date'])?Carbon::createFromFormat('Y-m-d', $options['delivery_date']):null;

        $deliveryOrderLimit = null;

        if($deliveryDate){
            // Delivery Limit
            $deliveryOrderLimits = OrderLimit::getOrderLimits([
                'limit_type' => OrderLimit::LIMIT_DELIVERY_DATE,
                'date' => $deliveryDate,
                'store' => $store,
                'type' => isset($options['type'])?$options['type']:OrderLimit::TYPE_PRODUCT,
                'product' => $this
            ]);

            $deliveryOrderLimit = (count($deliveryOrderLimits) > 0)?$this->extractOrderLimit($deliveryOrderLimits):null;
        }

        // Order Total Limit
        $totalOrderLimit = null;
        if($date){
            $totalOrderLimits = OrderLimit::getOrderLimits([
                'limit_type' => OrderLimit::LIMIT_ORDER_DATE,
                'date' => $date,
                'store' => $store,
                'type' => isset($options['type'])?$options['type']:OrderLimit::TYPE_PRODUCT,
                'product' => $this
            ]);

            $totalOrderLimit = (count($totalOrderLimits) > 0)?$this->extractOrderLimit($totalOrderLimits):null;
        }

        $orderLimits = [
            'delivery_date' => $deliveryOrderLimit,
            'checkout_at' => $totalOrderLimit
        ];

        foreach($orderLimits as $idx=>$orderLimit){
            if(is_null($orderLimit)){
                unset($orderLimits[$idx]);
            }
        }

        if(isset($orderLimits['checkout_at']) && (!$deliveryOrderLimit || $totalOrderLimit->limit <= $deliveryOrderLimit->limit)){
            $limitObj = $totalOrderLimit;
            $limitType = OrderLimit::LIMIT_ORDER_DATE;
        }elseif(isset($orderLimits['delivery_date'])){
            $limitObj = $deliveryOrderLimit;
            $limitType = OrderLimit::LIMIT_DELIVERY_DATE;
        }

        return isset($limitObj)?['limit_type' => $limitType, 'limit' => $orderLimits[$limitType]->limit, 'object' => $limitObj]:null;
    }

    public function getUnavailableDeliveryDates($options)
    {
        $disabledDates = [];
        $quantity = !empty($options['quantity'])?$options['quantity']:0;
        $saved_quantity = !empty($options['saved_quantity'])?$options['saved_quantity']:0;
        $saved_delivery_date = !empty($options['saved_delivery_date'])?$options['saved_delivery_date']:null;
        $quantity = !empty($options['quantity'])?$options['quantity']:0;
        $store_id = !empty($options['store_id'])?$options['store_id']:null;
        $months = !empty($options['months'])?$options['months']:[];
        $format = !empty($options['format'])?$options['format']:'Y-m-d';
        $order = !empty($options['order'])?$options['order']:null;
        $store = $store_id?Store::findOrFail($store_id):null;

        if($order){
            $productLineItems = $order->getProductLineItems();
        }else{
            $productLineItems = [];
        }

        if(!$months){
            throw new \Exception('You need to specify months.');
        }

        foreach($months as $month){
            $dayToRun = Carbon::createFromFormat('j-n-Y', '1-'.$month);
            $dayToRun->setTime(12, 0, 0);

            $lastDayOfMonth = clone $dayToRun;
            $lastDayOfMonth->modify('last day of this month');

            $lastThreeDays = clone $lastDayOfMonth;
            $lastThreeDays->modify('-3 days');

            $now = Carbon::now()->setTime(0, 0, 0);

            // If last 3 days of month, start searching next month
            if ($lastThreeDays->lte($now) && $lastDayOfMonth->gte($now)) {
                $dayToRun->modify('+1 month');
                $lastDayOfMonth->modify('last day of next month');
            }

            $lastDayOfMonth->modify('+10 days');

            $dayToRun->modify('-10 days');

            while($dayToRun->lte($lastDayOfMonth)){
                if($store && !$store->isOpen(Carbon::createFromFormat('Y-m-d H:i:s',$dayToRun))){
                    $disabledDates[] = $dayToRun->format($format);
                }else{
                    $dayOrderCount = $this->getOrderCount([
                        'delivery_date' => $dayToRun->format('Y-m-d'),
                        'store_id' => $store_id,
                    ]);

                    if($dayToRun->format('j-n-Y') == $saved_delivery_date){
                        $dayOrderCount -= $saved_quantity;
                    }

                    // Product Limit
                    $dayProductOrderLimit = $this->getOrderLimit([
                        'delivery_date' => $dayToRun->format('Y-m-d'),
                        'store' => $store_id,
                        'type' => OrderLimit::TYPE_PRODUCT
                    ]);

                    if(is_array($dayProductOrderLimit) && ($dayProductOrderLimit['limit'] == 0 || $dayOrderCount + $quantity > $dayProductOrderLimit['limit'])){
                        $disabledDates[] = $dayToRun->format($format);
                    }

                    // Category Limit
                    $dayCategoryOrderLimit = $this->getOrderLimit([
                        'delivery_date' => $dayToRun->format('Y-m-d'),
                        'store' => $store_id,
                        'type' => OrderLimit::TYPE_PRODUCT_CATEGORY
                    ]);

                    if(is_array($dayCategoryOrderLimit)){
                        foreach($productLineItems as $productLineItem){
                            if($dayCategoryOrderLimit['object']->productRulesPassed($productLineItem->product)){
                                $dayCategoryOrderLimit['object']->total += $productLineItem->quantity;
                            }
                        }

                        foreach($dayCategoryOrderLimit['object']->productCategories as $productCategory){
                            $dayCategoryOrderCount = $productCategory->getOrderCount([
                                'delivery_date' => $dayToRun->format('Y-m-d'),
                                'store_id' => $store_id,
                            ]);
                            if($dayCategoryOrderLimit['limit'] == 0 || $dayCategoryOrderCount + $quantity > $dayCategoryOrderLimit['limit']){
                                $disabledDates[] = $dayToRun->format($format);
                            }
                        }
                    }
                }

                $dayToRun->addDay();
            }
        }

        return $disabledDates;
    }

    public function getCompositeConfiguration($composite, $like = false)
    {
        if(is_string($composite)){
            if($like){
                $composite = $this->composites->filter(function($item, $key) use ($composite){
                    return strpos($item->slug, $composite) === 0;
                })->first();
            }else{
                $composite = $this->composites->where('slug', $composite)->first();
            }
        }elseif(is_int($composite)){
            $composite = $this->composites->where('id', $composite)->first();
        }

        return $composite;
    }

    public function hasCompositeConfiguration($composite, $like = false)
    {
        if(is_string($composite)){
            if($like){
                $count = $this->composites->filter(function($item, $key) use ($composite){
                    return strpos($item->slug, $composite) === 0;
                })->count();
            }else{
                $count = $this->composites->where('slug', $composite)->count();
            }
        }elseif(is_int($composite)){
            $count = $this->composites->where('id', $composite)->count();
        }

        return $count > 0;
    }

    /*
     *
     */
    public function getCompositeConfigurationRules($configs)
    {
        $rules = [];

        foreach($this->composites as $composite){

        }
    }

    public function getViewSuggestions()
    {
        $viewSuggestions = [];

        if($this->defaultCategory){
            $viewSuggestions[] = 'frontend.catalog.product.view_category_'.$this->defaultCategory->id;
        }

        $viewSuggestions += ['frontend.catalog.product.view_'.$this->id, 'frontend.catalog.product.view'];

        return $viewSuggestions;
    }

    public function getMetaImage()
    {
        return $this->thumbnail?$this->thumbnail->getImagePath('original'):null;
    }

    /**
     * @inheritdoc
     */
    public function getCacheKeys()
    {
        $tableName = $this->getTable();
        $keys = [
            $tableName.'_'.$this->productDetail->id.'_'.$this->id.'.retail_price',
            $tableName.'_'.$this->productDetail->id.'_'.$this->id.'.net_price',
        ];

        return $keys;
    }

    //Accessors
    public function getProductDetailAttribute()
    {
        if(!isset($this->_productDetail)){
            $this->_productDetail = $this->productDetails()->where('store_id', $this->store->id)->first();
        }

        if(!$this->_productDetail){
            $this->_productDetail = new ProductDetail();
        }

        return $this->_productDetail;
    }

    public function getIsVariationAttribute()
    {
        return $this->combination_type == self::COMBINATION_TYPE_VARIATION;
    }

    public function getIsPurchaseableAttribute()
    {
        return in_array($this->combination_type, [self::COMBINATION_TYPE_VARIATION, self::COMBINATION_TYPE_SINGLE]);
    }

    public function getStoreAttribute()
    {
        if(!$this->_store){
            $this->_store = ProjectHelper::getActiveStore();

            //Check if has productDetail with this store. Otherwise, get default
            if(!$this->productDetails()->where('store_id', $this->_store->id)->count()){
                $this->_store = ProjectHelper::getDefaultStore();

                //Another check if has productDetail with default store. Otherwise, get available
                if(!$this->productDetails()->where('store_id', $this->_store->id)->count()){
                    if($this->productDetails->count() > 0){
                        $this->_store = $this->productDetails->get(0)->store;
                    }
                }
            }
        }

        return $this->_store;
    }

    public function getCurrencyAttribute()
    {
        if(!$this->_currency){
            $this->_currency = CurrencyHelper::getCurrentCurrency();
        }

        return $this->_currency;
    }

    public function getWarehouseAttribute()
    {
        if(!$this->_warehouse){
            $store = $this->store;

            $this->_warehouse = $store->getDefaultWarehouse();
        }

        return $this->_warehouse;
    }

    public function getProductConfigurationGroupAttribute()
    {
        return $this->productConfigurationGroups->first();
    }

    public function getProductConfigurationsAttribute()
    {
        $configurations = RuntimeCache::getOrSet('product_'.$this->id.'_configurations', function(){
            $configurations = collect([]);

            foreach($this->productConfigurationGroups as $productConfigurationGroup){
                $configurations = $configurations->merge($productConfigurationGroup->configurations);
            }

            return $configurations;
        });

        return $configurations;
    }

    public function getProductCompositeGroupAttribute()
    {
        return $this->productCompositeGroups->first();
    }

    public function getCompositesAttribute()
    {
        $composites = RuntimeCache::getOrSet('product_'.$this->id.'_composites', function(){
            $composites = collect([]);

            foreach($this->productCompositeGroups as $productCompositeGroup){
                $composites = $composites->merge($productCompositeGroup->composites);
            }

            return $composites;
        });

        return $composites;
    }

    /*
     * Save product to index for facet search
     *
     * @boolean $to_parent flag to determine whether to save child attributes & features to parent
     */
    public function saveToIndex($to_parent = false)
    {
        //Clear old index
        if(!$to_parent){
            ProductIndexHelper::getProductIndexQuery(false)->where('product_id', $this->id)->where('store_id', $this->store->id)->delete();
        }

        // Load fresh Product Detail in case saving new record
        unset($this->_productDetail);

        if(!$to_parent){
            $categoryIndex = [];
            foreach($this->categories as $category){
                $categoryIndex[] = [
                    'root_product_id' => $this->parent?$this->parent->id:$this->id,
                    'product_id' => $this->id,
                    'type' => $category->getProductIndexType(),
                    'value' => $category->id,
                    'store_id' => $this->store->id
                ];
            }

            ProductIndexHelper::saveToIndex($categoryIndex);

            $manufacturerIndex = [];
            if($this->manufacturer){
                $manufacturerIndex[] = [
                    'root_product_id' => $this->parent?$this->parent->id:$this->id,
                    'product_id' => $this->id,
                    'type' => $this->manufacturer->getProductIndexType(),
                    'value' => $this->manufacturer->id,
                    'store_id' => $this->store->id
                ];

                ProductIndexHelper::saveToIndex($manufacturerIndex);
            }
        }

        $attributeIndex = [];
        foreach($this->productAttributeValues as $attributeValue){
            $attributeIndex[] = [
                'root_product_id' => $this->parent?$this->parent->id:$this->id,
                'product_id' => $to_parent?$this->parent->id:$this->id,
                'type' => $attributeValue->productAttribute->getProductIndexType(),
                'value' => $attributeValue->id,
                'pivot' => $attributeValue->productAttribute->id,
                'store_id' => $this->store->id
            ];
        }

        ProductIndexHelper::saveToIndex($attributeIndex);

        $featureIndex = [];
        foreach($this->productFeatureValues as $featureValue){
            $featureIndex[] = [
                'root_product_id' => $this->parent?$this->parent->id:$this->id,
                'product_id' => $to_parent?$this->parent->id:$this->id,
                'type' => $featureValue->productFeature->getProductIndexType(),
                'value' => $featureValue->id,
                'pivot' => $featureValue->productFeature->id,
                'store_id' => $this->store->id
            ];
        }

        ProductIndexHelper::saveToIndex($featureIndex);

        if(!$to_parent){
            $this->saveToPriceIndex();

            foreach($this->variations as $variation){
                $variation->saveToIndex(true);
            }
        }
    }

    public function saveToPriceIndex()
    {
        ProductIndexHelper::getProductIndexPriceQuery(false)->where('product_id', $this->id)->where('store_id', $this->store->id)->delete();

        $netPrice = $this->_calculateNetPrice();
        $currency = $this->parent?$this->parent->productDetail->currency:$this->productDetail->currency;
        $priceIndex = [
            [
                'root_product_id' => $this->parent?$this->parent->id:$this->id,
                'product_id' => $this->id,
                'value' => is_null($netPrice)?0:$netPrice,
                'currency' => $currency ? : $this->currency,
                'store_id' => $this->store->id
            ]
        ];

        ProductIndexHelper::saveToIndex($priceIndex, 'product_price');
    }

    //Mutators
    public function setProductDetailAttribute($productDetail)
    {
        $this->_productDetail = $productDetail;
    }

    public function setStoreAttribute($store)
    {
        if(is_int($store)){
            $store = Store::find($store);
        }

        $this->_store = $store;

        //Unset cached Product Detail
        unset($this->_productDetail);
    }

    public function setWarehouseAttribute($warehouse_id)
    {
        $warehouse = Warehouse::find($warehouse_id);

        $this->_warehouse = $warehouse;
    }

    //Scopes
    public function scopeActive($query)
    {
        $store = ProjectHelper::getActiveStore();

        $query->whereHas('productDetails', function($query) use ($store){
            $query->where('active', true)->where('store_id', $store->id);
        });
    }

    public function scopeIsNew($query)
    {
        $store = ProjectHelper::getActiveStore();

        $query->whereHas('productDetails', function($query) use ($store){
            $query->where('new', true)->where('store_id', $store->id);
        });
    }

    public function scopeCatalogVisible($query)
    {
        $store = ProjectHelper::getActiveStore()->id;

        $query->whereHas('productDetails', function($query){
            $query->whereIn('visibility', [ProductDetail::VISIBILITY_CATALOG, ProductDetail::VISIBILITY_EVERYWHERE]);
        });
    }

    public function scopeSearchVisible($query)
    {
        $store = ProjectHelper::getActiveStore()->id;

        $query->whereHas('productDetails', function($query, $store){
            $query->whereIn('visibility', [ProductDetail::VISIBILITY_SEARCH, ProductDetail::VISIBILITY_EVERYWHERE]);
        });
    }

    public function scopeProductEntity($query)
    {
        $query->whereNotIn('combination_type', [self::COMBINATION_TYPE_VARIATION]);
    }

    public function scopeProductSelection($query)
    {
        $query->whereNotIn('combination_type', [self::COMBINATION_TYPE_VARIABLE]);
    }

    public function scopeStickyLineItem($query)
    {
        $query->where('sticky_line_item', 1);
    }

    public function scopeSelectSelf($query)
    {
        $query->selectRaw($this->getTable().'.*');
    }

    public function scopeJoinTranslation($query, $locale=null)
    {
        $locale = $locale?$locale:$this->locale();

        $query
            ->leftJoin($this->getTranslationsTable().' as T', function($join) use ($locale){
                $join->on('T.product_id', '=', $this->getTable().'.id')
                    ->where('T.'.$this->getLocaleKey(), '=', $locale);
            });

        $query->addSelect(DB::raw('T.*'));
    }

    public function scopeJoinDetail($query, $store=null)
    {
        $store = $store?$store:ProjectHelper::getActiveStore()->id;

        $productDetailTable = $this->productDetails()->getRelated()->getTable();

        $query->leftJoin($productDetailTable.' AS D', function($join) use ($productDetailTable, $store){
            $join->on('D.'.$this->productDetails()->getForeignKeyName(), '=', $this->getTable().'.id')
                ->where('D.store_id', '=', $store);
        });

        $query->addSelect(DB::raw('D.*', 'D.id AS detail_id'));
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

    public static function getCombinationTypeOptions($option=null, $all = true)
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
