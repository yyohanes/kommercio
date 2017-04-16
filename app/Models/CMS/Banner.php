<?php

namespace Kommercio\Models\CMS;

use Dimsav\Translatable\Translatable;
use Illuminate\Database\Eloquent\Model;
use Kommercio\Facades\ProjectHelper;
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
    public $translatedAttributes = ['name', 'body', 'image', 'images', 'videos'];
    protected $toggleFields = ['active'];

    public function render($imageStyle = 'original')
    {
        $view_name = ProjectHelper::findViewTemplate(['frontend.banner.view_'.$this->id, 'frontend.banner.view_'.$this->bannerGroup->slug, 'frontend.banner.view']);

        return view($view_name, [
            'banner' => $this,
            'link' => $this->getData('url'),
            'target' => $this->getData('target', '_self'),
            'callToAction' => $this->getData('call_to_action'),
            'class' => $this->getData('class'),
            'imageStyle' => $imageStyle
        ]);
    }

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
