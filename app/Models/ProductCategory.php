<?php

namespace Kommercio\Models;

use Carbon\Carbon;
use Dimsav\Translatable\Translatable;
use Illuminate\Database\Eloquent\Model;
use Kommercio\Facades\FrontendHelper;
use Kommercio\Models\Interfaces\ProductIndexInterface;
use Kommercio\Models\Interfaces\SeoModelInterface;
use Kommercio\Models\Interfaces\UrlAliasInterface;
use Kommercio\Models\Order\LineItem;
use Kommercio\Models\Order\OrderLimit;
use Kommercio\Traits\AuthorSignature;
use Kommercio\Traits\Model\OrderLimitTrait;
use Kommercio\Traits\Model\SeoTrait;

class ProductCategory extends Model implements UrlAliasInterface, SeoModelInterface, ProductIndexInterface
{
    use Translatable, SeoTrait, OrderLimitTrait;

    protected $fillable = ['name', 'description', 'parent_id', 'active', 'sort_order', 'slug', 'meta_title', 'meta_description'];
    protected $casts = [
        'active' => 'boolean'
    ];
    public $translatedAttributes = ['name', 'slug', 'description', 'meta_title', 'meta_description'];

    private $_rootCategory;

    //Methods
    public function getName()
    {
        return $this->name.($this->parent?' ('.$this->parent->name.')':'');
    }

    public function hasProduct($product)
    {
        if(!is_int($product)){
            $product_id = $product;
        }

        $product_id = $product->id;

        return $this->products()->where('id', $product_id)->count() > 0;
    }

    public function getUrlAlias()
    {
        $paths = [];

        $parent = $this->parent;
        while($parent){
            $paths[] = $parent->slug;
            $parent = $parent->parent;
        }
        $paths = array_reverse($paths);

        $paths[] = $this->slug;

        return implode('/', $paths);
    }

    public function getExternalPath()
    {
        $path = $this->getInternalPathSlug().'/'.$this->id;

        return FrontendHelper::get_url($path);
    }

    public function getInternalPathSlug()
    {
        return 'product-category';
    }

    public function getBreadcrumbTrails()
    {
        $parent = $this->parent;

        $breadcrumbs = [];

        while($parent){
            $breadcrumbs[] = $parent;
            $parent = $parent->parent;
        }

        $breadcrumbs = array_reverse($breadcrumbs);

        return $breadcrumbs;
    }

    public function getProductIndexType()
    {
        return 'product_category';
    }

    public function getProductIndexRows()
    {
        $rows = collect([$this]);
        $rows->merge($this->children);

        return $rows;
    }

    /**
     * Get number of purchased per product category
     * We will go through category's products and get each product's order count
     * If product order count has been cached, this will be fast process, otherwise, product order count will be calculated.
     *
     * @TODO Rethink this function logic. Imagine if category has lots of products, this function can crash
     *
     * @param array $options Possible options are store_id, checkout_at, delivery_date, exclude_order_id
     * @return float|int
     */
    public function getOrderCount($options = [])
    {
        $orderCount = 0;

        foreach($this->products as $product){
            $orderCount += $product->getOrderCount($options);
        }

        return $orderCount;
    }

    public function getPerOrderLimit($options = [])
    {
        $store = isset($options['store'])?$options['store']:null;
        $date = isset($options['date'])?Carbon::createFromFormat('Y-m-d', $options['date']):null;

        //Per Order Limit
        $orderLimits = OrderLimit::getOrderLimits([
            'limit_type' => OrderLimit::LIMIT_PER_ORDER,
            'date' => $date,
            'store' => $store,
            'type' => OrderLimit::TYPE_PRODUCT_CATEGORY,
            'category' => $this
        ]);

        $orderLimit = (count($orderLimits) > 0)?$this->extractOrderLimit($orderLimits):null;

        return $orderLimit?['limit_type' => $orderLimit->type, 'limit' => $orderLimit->limit, 'object' => $orderLimit]:null;
    }

    //Relations
    public function parent()
    {
        return $this->belongsTo('Kommercio\Models\ProductCategory', 'parent_id');
    }

    public function children()
    {
        return $this->hasMany('Kommercio\Models\ProductCategory', 'parent_id')->orderBy('sort_order', 'ASC');
    }

    public function orderLimits()
    {
        return $this->morphedByMany('Kommercio\Models\Order\OrderLimit', 'order_limitable');
    }

    public function products()
    {
        return $this->belongsToMany('Kommercio\Models\Product', 'category_product')->productEntity();
    }

    //Methods
    public function getActiveChildren()
    {
        $children = $this->children->reject(function($value){
            return !$value->active;
        });

        return $children;
    }

    public function getProducts($options = [])
    {
        $defaultOptions = [
            'visibility' => ProductDetail::VISIBILITY_EVERYWHERE,
            'active' => TRUE,
            'available' => NULL
        ];

        $options = array_merge($defaultOptions, $options);

        $products = $this->products;
        $products = $products->reject(function($value) use ($options){
            $visibility = [ProductDetail::VISIBILITY_EVERYWHERE, ProductDetail::VISIBILITY_CATALOG, ProductDetail::VISIBILITY_SEARCH];

            if($options['visibility'] != ProductDetail::VISIBILITY_EVERYWHERE){
                $visibility = is_array($options['visibility'])?$options['visibility']:[$options['visibility']];
            }

            if(!in_array($value->productDetail->visibility, $visibility)){
                return TRUE;
            }

            if($options['active'] !== null && $value->productDetail->active != $options['active']){
                return TRUE;
            }

            if($options['available'] !== null && $value->productDetail->available != $options['available']){
                return TRUE;
            }

            return FALSE;
        });

        return $products;
    }

    public function getViewSuggestions()
    {
        $viewSuggestions = [];

        $viewSuggestions[] = 'frontend.catalog.product_category.view_'.$this->id;

        if($this->parent){
            $viewSuggestions[] = 'frontend.catalog.product_category.view_inherit_'.$this->parent->id;
        }

        $viewSuggestions[] = 'frontend.catalog.product_category.view';

        return $viewSuggestions;
    }

    public function getMetaImage()
    {
        return $this->thumbnail?$this->thumbnail->getImagePath('original'):null;
    }

    //Accessors
    public function getChildrenCountAttribute()
    {
        if(!$this->relationLoaded('children')){
            $this->load('children');
        }

        return $this->children->count();
    }

    public function getProductCountAttribute()
    {
        if(!$this->relationLoaded('products')){
            $this->load('products');
        }

        return $this->products->count();
    }

    public function getRootAttribute()
    {
        if(!isset($this->_rootCategory)){
            if($this->parent){
                $breadcrumbs = $this->getBreadcrumbTrails();
                $this->_rootCategory = $breadcrumbs[0];
            }else{
                $this->_rootCategory = $this;
            }
        }

        return $this->_rootCategory;
    }

    //Scopes
    public function scopeActive($query)
    {
        $query->where('active', true);
    }

    public function scopeIsRoot($query)
    {
        $query->whereNull('parent_id');
    }

    //Statics
    public static function getRootCategories()
    {
        return self::whereNull('parent_id')->orderBy('sort_order', 'ASC')->get();
    }

    public static function getPossibleParentOptions($exclude=null)
    {
        if(empty($exclude)){
            $exclude = [0];
        }

        $options = [];
        $roots = self::whereNotIn('id', [$exclude])->whereNull('parent_id')->orderBy('sort_order', 'ASC')->get();

        self::_loopChildrenOptions($options, $roots, 0, $exclude);

        return $options;
    }

    public static function getBySlug($slug)
    {
        $qb = self::whereTranslation('slug', $slug);
        $category = $qb->first();

        return $category;
    }

    private static function _loopChildrenOptions(&$options, $children, $level, $exclude=null)
    {
        foreach($children as $child){
            $options[$child->id] = str_pad($child->name, $level+strlen(trim($child->name)), '-', STR_PAD_LEFT);

            $grandChildren = $child->children()->whereNotIn('id', [$exclude])->get();

            self::_loopChildrenOptions($options, $grandChildren, $level+1, $exclude);
        }
    }
}
