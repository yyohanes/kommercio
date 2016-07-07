<?php

namespace Kommercio\Models\CMS;

use Cviebrock\EloquentSluggable\SluggableInterface;
use Cviebrock\EloquentSluggable\SluggableTrait;
use Illuminate\Database\Eloquent\Model;

class Menu extends Model implements SluggableInterface
{
    use SluggableTrait;

    protected $fillable = ['name', 'description'];
    protected $sluggable = [
        'build_from' => 'name',
        'save_to'    => 'slug',
        'on_update' => TRUE,
    ];

    //Relations
    public function menuItems()
    {
        return $this->hasMany('Kommercio\Models\CMS\MenuItem')->orderBy('sort_order', 'ASC');
    }

    public function rootMenuItems()
    {
        return $this->hasMany('Kommercio\Models\CMS\MenuItem')->whereNull('parent_id')->orderBy('sort_order', 'ASC');
    }
}
