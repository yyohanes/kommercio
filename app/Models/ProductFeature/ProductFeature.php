<?php

namespace Kommercio\Models\ProductFeature;

use Dimsav\Translatable\Translatable;
use Illuminate\Database\Eloquent\Model;
use Kommercio\Models\Interfaces\ProductIndexInterface;
use Kommercio\Models\Product;
use Kommercio\Models\ProductFeature\FeatureValuePivot;

class ProductFeature extends Model implements ProductIndexInterface
{
    use Translatable;

    public $timestamps = FALSE;

    public $translatedAttributes = ['name', 'slug'];

    protected $guarded = [];

    //Methods
    public function newPivot(Model $parent, array $attributes, $table, $exists, $using = null)
    {
        if ($parent instanceof Product) {
            return new FeatureValuePivot($parent, $attributes, $table, $exists, $using);
        }

        return parent::newPivot($parent, $attributes, $table, $exists, $using);
    }

    public function getValueOptions()
    {
        $options = [];

        foreach($this->values as $value){
            $options[$value->id] = $value->name;
        }

        return $options;
    }

    public function getProductIndexType()
    {
        return 'product_feature';
    }

    public function getProductIndexRows()
    {
        $rows = $this->values;

        return $rows;
    }

    //Relations
    public function values()
    {
        return $this->hasMany('Kommercio\Models\ProductFeature\ProductFeatureValue')->orderBy('sort_order', 'ASC');
    }

    public function products()
    {
        return $this->belongsToMany('Kommercio\Models\Product', 'product_product_feature');
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
    public static function getProductFeatureWithValueOptions()
    {
        $options = [];

        $productFeatures = self::withTranslation()->with('values')->orderBy('sort_order', 'ASC')->get();

        foreach($productFeatures as $productFeature){
            $values = [];

            foreach($productFeature->values as $productFeatureValue){
                $values[$productFeatureValue->id] = $productFeatureValue->name;
            }

            $options[$productFeature->name] = $values;
        }

        return $options;
    }
}
