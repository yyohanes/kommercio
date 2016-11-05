<?php

namespace Kommercio\Models\ProductAttribute;

use Cviebrock\EloquentSluggable\SluggableInterface;
use Cviebrock\EloquentSluggable\SluggableTrait;
use Illuminate\Database\Eloquent\Model;

class ProductAttributeValueTranslation extends Model implements SluggableInterface
{
    use SluggableTrait;

    protected $sluggable = [
        'build_from' => 'name',
        'save_to'    => 'slug',
        'on_update' => TRUE,
    ];

    public $timestamps = FALSE;

    //Relations
    public function productAttributeValue()
    {
        return $this->belongsTo('Kommercio\Models\ProductAttribute\ProductAttributeValue');
    }
}
