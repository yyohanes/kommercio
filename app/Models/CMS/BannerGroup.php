<?php

namespace Kommercio\Models\CMS;

use Kommercio\Models\Abstracts\SluggableModel;

class BannerGroup extends SluggableModel
{
    protected $fillable = ['name', 'slug', 'description'];

    //Relations
    public function banners()
    {
        return $this->hasMany('Kommercio\Models\CMS\Banner')->orderBy('sort_order', 'ASC');
    }

    //Methods
    public function getBanners()
    {
        return $this->banners()->active()->get();
    }
}
