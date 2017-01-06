<?php

namespace Kommercio\Models\CMS;

use Dimsav\Translatable\Translatable;
use Illuminate\Database\Eloquent\Model;
use Kommercio\Traits\Model\HasDataColumn;
use Kommercio\Traits\Model\ToggleDate;

class Banner extends Model
{
    use Translatable, ToggleDate, HasDataColumn{
        Translatable::setAttribute as translateableSetAttribute;
        ToggleDate::setAttribute insteadof Translatable;
    }

    protected $casts = [
        'active' => 'boolean',
    ];

    public $fillable = ['name', 'body', 'banner_group_id', 'menu_class', 'active', 'sort_order'];
    public $translatedAttributes = ['name', 'body', 'image', 'images'];
    protected $toggleFields = ['active'];

    //Relations
    public function bannerGroup()
    {
        return $this->belongsTo('Kommercio\Models\CMS\BannerGroup');
    }

    //Scopes
    public function scopeActive($query)
    {
        $query->where('active', true);
    }
}
