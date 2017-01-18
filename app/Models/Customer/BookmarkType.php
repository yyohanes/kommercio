<?php

namespace Kommercio\Models\Customer;

use Cviebrock\EloquentSluggable\SluggableInterface;
use Cviebrock\EloquentSluggable\SluggableTrait;
use Illuminate\Database\Eloquent\Model;

class BookmarkType extends Model implements SluggableInterface
{
    use SluggableTrait;

    protected $fillable = ['name', 'default'];
    protected $casts = [
        'default' => 'boolean'
    ];
    protected $sluggable = [
        'build_from' => 'name',
        'save_to' => 'slug',
        'on_update' => TRUE
    ];

    //Relations
    public function bookmarks()
    {
        return $this->hasMany('Kommercio\Models\Customer\Bookmark');
    }
}
