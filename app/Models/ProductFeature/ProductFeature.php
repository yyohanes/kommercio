<?php

namespace Kommercio\Models\ProductFeature;

use Dimsav\Translatable\Translatable;
use Illuminate\Database\Eloquent\Model;
use Kommercio\Models\Product;
use Kommercio\Models\ProductFeature\FeatureValuePivot;

class ProductFeature extends Model
{
    use Translatable;

    public $timestamps = FALSE;

    public $translatedAttributes = ['name', 'slug'];

    protected $guarded = [];

    //Methods
    public function newPivot(Model $parent, array $attributes, $table, $exists)
    {
        if ($parent instanceof Product) {
            return new FeatureValuePivot($parent, $attributes, $table, $exists);
        }

        return parent::newPivot($parent, $attributes, $table, $exists);
    }

    public function getValueOptions()
    {
        $options = [];

        foreach($this->values as $value){
            $options[$value->id] = $value->name;
        }

        return $options;
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
