<?php

namespace Kommercio\Models\Product\Configuration;

use Dimsav\Translatable\Translatable;
use Kommercio\Facades\ProjectHelper;
use Kommercio\Models\Abstracts\SluggableModel;
use Kommercio\Models\Product;
use Kommercio\Traits\Model\HasDataColumn;

class ProductConfiguration extends SluggableModel
{
    use Translatable, HasDataColumn;

    const TYPE_TEXTFIELD = 'textfield';
    const TYPE_TEXTAREA = 'textarea';

    protected $fillable = ['name', 'slug', 'type'];

    public $translatedAttributes = ['name'];

    //Methods
    public function getFieldView()
    {
        $view_suggestions = [
            'frontend.catalog.product_configuration.'.$this->type.'_'.$this->id,
            'frontend.catalog.product_configuration.'.$this->type
        ];

        return ProjectHelper::findViewTemplate($view_suggestions);
    }

    public function buildRules()
    {
        $rules = [];

        if($this->pivot->required){
            $rules[] = 'required';
        }

        $savedRules = $this->getData('rules');

        if($this->getData('rules.min', 0)){
            $rules[] = 'min:'.$this->getData('rules.min');
        }

        if($this->getData('rules.max', 0) > 0){
            $rules[] = 'max:'.$this->getData('rules.max');
        }

        return implode('|', $rules);
    }

    //Relations
    public function groups()
    {
        return $this->belongsToMany('Kommercio\Models\Product\Configuration\ProductConfigurationGroup');
    }

    //Statics
    public static function getTypeOptions($option=null)
    {
        $array = [
            self::TYPE_TEXTFIELD => 'Textfield',
            self::TYPE_TEXTAREA => 'Textarea',
        ];

        if(empty($option)){
            return $array;
        }

        return (isset($array[$option]))?$array[$option]:$array;
    }

    public static function getTypeRules($option)
    {
        $array = [
            self::TYPE_TEXTFIELD => [
                'min' => 'integer',
                'max' => 'integer',
            ],
            self::TYPE_TEXTAREA => [
                'min' => 'integer',
                'max' => 'integer',
            ],
        ];

        return $array[$option];
    }
}
