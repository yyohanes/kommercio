<?php

namespace Kommercio\Models\CMS;

use Cviebrock\EloquentSluggable\SluggableInterface;
use Cviebrock\EloquentSluggable\SluggableTrait;
use Illuminate\Database\Eloquent\Model;

class BannerGroup extends Model implements SluggableInterface
{
    use SluggableTrait;

    protected $fillable = ['name', 'slug', 'description'];

    protected $sluggable = [
        'build_from' => 'name',
        'save_to'    => 'slug',
        'on_update' => TRUE,
    ];

    //Relations
    public function banners()
    {
        return $this->hasMany('Kommercio\Models\CMS\Banner')->orderBy('sort_order', 'ASC');
    }
}
