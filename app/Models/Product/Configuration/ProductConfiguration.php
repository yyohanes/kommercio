<?php

namespace Kommercio\Models\Product\Configuration;

use Cviebrock\EloquentSluggable\SluggableInterface;
use Cviebrock\EloquentSluggable\SluggableTrait;
use Dimsav\Translatable\Translatable;
use Illuminate\Database\Eloquent\Model;
use Kommercio\Traits\Model\HasDataColumn;

class ProductConfiguration extends Model implements SluggableInterface
{
    use SluggableTrait, Translatable, HasDataColumn;

    const TYPE_TEXTFIELD = 'textfield';
    const TYPE_TEXTAREA = 'textarea';

    protected $fillable = ['name', 'slug', 'type'];
    protected $sluggable = [
        'build_from' => 'name',
        'save_to' => 'slug',
    ];

    public $translatedAttributes = ['name'];

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
