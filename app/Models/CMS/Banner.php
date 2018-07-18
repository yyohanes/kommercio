<?php

namespace Kommercio\Models\CMS;

use Dimsav\Translatable\Translatable;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Support\Facades\Cache;
use Kommercio\Facades\ProjectHelper;
use Kommercio\Models\Interfaces\CacheableInterface;
use Kommercio\Traits\Model\HasDataColumn;
use Kommercio\Traits\Model\ToggleDate;

class Banner extends Model implements CacheableInterface
{
    use Translatable, ToggleDate, HasDataColumn{
        Translatable::setAttribute as translateableSetAttribute;
        ToggleDate::setAttribute insteadof Translatable;

        Translatable::translations as translatableTranslations;
    }

    protected $casts = [
        'active' => 'boolean',
    ];

    public $fillable = ['name', 'body', 'banner_group_id', 'active', 'sort_order'];
    public $translatedAttributes = ['name', 'body', 'image', 'images', 'videos'];
    protected $toggleFields = ['active'];

    private $_cachedRelationResults;

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

    public function getCacheKeys()
    {
        $tableName = $this->getTable();
        $keys = [
            $tableName.'_'.$this->id,
            $tableName.'_'.$this->id.'_translations',
        ];

        return $keys;
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

    // Accessors
    public function getTranslationsAttribute()
    {
        if (!isset($this->_cachedRelationResults['_translations'])) {
            $this->_cachedRelationResults['_translations'] = Cache::rememberForever($this->getTable().'_'.$this->id.'_translations', function () {
                return $this->translatableTranslations;
            });
        }

        return $this->_cachedRelationResults['_translations'];
    }
}
