<?php

namespace Kommercio\Models\ProductAttribute;

use Dimsav\Translatable\Translatable;
use Illuminate\Database\Eloquent\Model;
use Kommercio\Models\Interfaces\ProductIndexInterface;
use Kommercio\Models\Product;

class ProductAttribute extends Model implements ProductIndexInterface
{
    use Translatable;

    public $timestamps = FALSE;

    public $translatedAttributes = ['name', 'slug'];

    protected $guarded = [];

    //Methods
    public function newPivot(Model $parent, array $attributes, $table, $exists)
    {
        if ($parent instanceof Product) {
            return new AttributeValuePivot($parent, $attributes, $table, $exists);
        }

        return parent::newPivot($parent, $attributes, $table, $exists);
    }

    public function getProductIndexType()
    {
        return 'product_attribute';
    }

    public function getProductIndexRows()
    {
        $rows = $this->values;

        return $rows;
    }

    //Relations
    public function values()
    {
        return $this->hasMany('Kommercio\Models\ProductAttribute\ProductAttributeValue')->orderBy('sort_order', 'ASC');
    }

    public function products()
    {
        return $this->belongsToMany('Kommercio\Models\Product', 'product_product_attribute');
    }

    //Accessors
    public function getValueCountAttribute()
    {
        if(!$this->relationLoaded('values')){
            $this->load('values');
        }

        return $this->values->count();
    }

    //Statics
    public static function getProductAttributeWithValueOptions()
    {
        $options = [];

        $productAttributes = self::withTranslation()->with('values')->orderBy('sort_order', 'ASC')->get();

        foreach($productAttributes as $productAttribute){
            $values = [];

            foreach($productAttribute->values as $productAttributeValue){
                $values[$productAttributeValue->id] = $productAttributeValue->name;
            }

            $options[$productAttribute->name] = $values;
        }

        return $options;
    }
}
