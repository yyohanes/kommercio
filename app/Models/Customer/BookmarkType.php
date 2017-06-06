<?php

namespace Kommercio\Models\Customer;

use Kommercio\Models\Abstracts\SluggableModel;

class BookmarkType extends SluggableModel
{
    protected $fillable = ['name', 'default'];
    protected $casts = [
        'default' => 'boolean'
    ];

    //Relations
    public function bookmarks()
    {
        return $this->hasMany('Kommercio\Models\Customer\Bookmark');
    }
}
