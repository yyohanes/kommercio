<?php

namespace Kommercio\Models;

use Dimsav\Translatable\Translatable;
use Illuminate\Database\Eloquent\Model;
use Kommercio\Traits\AuthorSignature;
use Kommercio\Models\Interfaces\AuthorSignatureInterface;

class ProductCategory extends Model
{
    use Translatable;

    protected $fillable = ['name', 'description', 'parent_id', 'active', 'sort_order', 'slug', 'meta_title', 'meta_description'];
    protected $casts = [
        'active' => 'boolean'
    ];
    public $translatedAttributes = ['name', 'slug', 'description', 'meta_title', 'meta_description', 'thumbnail', 'images'];

    //Relations
    public function parent()
    {
        return $this->belongsTo('Kommercio\Models\ProductCategory', 'parent_id');
    }

    public function children()
    {
        return $this->hasMany('Kommercio\Models\ProductCategory', 'parent_id')->orderBy('sort_order', 'ASC');
    }

    //Accessors
    public function getChildrenCountAttribute()
    {
        if(!$this->relationLoaded('children')){
            $this->load('children');
        }

        return $this->children->count();
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
