<?php

namespace Kommercio\Models;

use Dimsav\Translatable\Translatable;
use Illuminate\Database\Eloquent\Model;
use Kommercio\Models\Interfaces\UrlAliasInterface;
use Kommercio\Traits\AuthorSignature;
use Kommercio\Traits\Model\SeoTrait;

class ProductCategory extends Model implements UrlAliasInterface
{
    use Translatable, SeoTrait;

    protected $fillable = ['name', 'description', 'parent_id', 'active', 'sort_order', 'slug', 'meta_title', 'meta_description'];
    protected $casts = [
        'active' => 'boolean'
    ];
    public $translatedAttributes = ['name', 'slug', 'description', 'meta_title', 'meta_description', 'thumbnail', 'images'];

    //Methods
    public function getName()
    {
        return $this->name.($this->parent?' ('.$this->parent->name.')':'');
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

    public function getInternalPathSlug()
    {
        return 'product-category';
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
    public function getViewSuggestions()
    {
        $viewSuggestions = [];

        $viewSuggestions[] = 'frontend.catalog.product_category.view_'.$this->id;

        if($this->parent){
            $viewSuggestions[] = 'frontend.catalog.product_category.view_'.$this->parent->id;
        }

        $viewSuggestions[] = 'frontend.catalog.product_category.view';

        return $viewSuggestions;
    }

    //Accessors
    public function getChildrenCountAttribute()
    {
        if(!$this->relationLoaded('children')){
            $this->load('children');
        }

        return $this->children->count();
    }

    //Scopes
    public function scopeActive($query)
    {
        $query->where('active', true);
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

    private static function _loopChildrenOptions(&$options, $children, $level, $exclude=null)
    {
        foreach($children as $child){
            $options[$child->id] = str_pad($child->name, $level+strlen(trim($child->name)), '-', STR_PAD_LEFT);

            $grandChildren = $child->children()->whereNotIn('id', [$exclude])->get();

            self::_loopChildrenOptions($options, $grandChildren, $level+1, $exclude);
        }
    }
}
