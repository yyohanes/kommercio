<?php

namespace Kommercio\Models\ProductAttribute;

use Illuminate\Database\Eloquent\Model;
use Dimsav\Translatable\Translatable;
use Kommercio\Facades\ProductIndexHelper;
use Kommercio\Models\Product;
use Kommercio\Traits\Model\MediaAttachable;

class ProductAttributeValue extends Model
{
    use Translatable, MediaAttachable;

    public $timestamps = FALSE;

    public $translatedAttributes = ['name', 'slug'];

    protected $fillable = ['name', 'slug', 'sort_order'];

    //Methods
    public function newPivot(Model $parent, array $attributes, $table, $exists, $using = null)
    {
        if ($parent instanceof Product) {
            return new AttributeValuePivot($parent, $attributes, $table, $exists, $using);
        }

        return parent::newPivot($parent, $attributes, $table, $exists, $using);
    }

    //Relations
    public function productAttribute()
    {
        return $this->belongsTo('Kommercio\Models\ProductAttribute\ProductAttribute');
    }

    public function products()
    {
        return $this->belongsToMany('Kommercio\Models\Product', 'product_product_attribute');
    }

    public function thumbnails()
    {
        return $this->media('thumbnail');
    }

    //Accessors
    public function getThumbnailAttribute()
    {
        if(!$this->relationLoaded('thumbnails')){
            $this->load('thumbnails');
        }

        return $this->thumbnails->first();
    }
}
