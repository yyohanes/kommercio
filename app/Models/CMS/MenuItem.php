<?php

namespace Kommercio\Models\CMS;

use Dimsav\Translatable\Translatable;
use Illuminate\Database\Eloquent\Model;
use Kommercio\Facades\FrontendHelper;
use Kommercio\Traits\Model\ToggleDate;

class MenuItem extends Model
{
    use Translatable, ToggleDate {
        Translatable::setAttribute as translateableSetAttribute;
        ToggleDate::setAttribute insteadof Translatable;
    }

    protected $casts = [
        'active' => 'boolean',
    ];

    protected $fillable = ['name', 'menu_id', 'parent_id', 'active', 'menu_class', 'sort_order', 'url'];
    protected $toggleFields = ['active'];
    protected $types = [
        'product' => '\Kommercio\Models\Product',
        'product-category' => '\Kommercio\Models\ProductCategory',
        'page' => '\Kommercio\Models\CMS\Page'];

    public $translatedAttributes = ['name', 'url', 'data'];

    //Scope
    public function scopeActive($query)
    {
        $query->where('active', true);
    }

    //Relations
    public function menu()
    {
        return $this->belongsTo('Kommercio\Models\CMS\Menu');
    }

    public function parent()
    {
        return $this->belongsTo('Kommercio\Models\CMS\MenuItem', 'parent_id');
    }

    public function children()
    {
        return $this->hasMany('Kommercio\Models\CMS\MenuItem', 'parent_id')->orderBy('sort_order', 'ASC');
    }

    //Accessors
    public function getExternalPathAttribute()
    {
        if($this->url){
            $path = FrontendHelper::getUrl($this->url);
        }else{
            $path = '#';
        }

        return $path;
    }

    public function getTargetAttribute()
    {
        $target = $this->getTranslation()->getData('target', '_self');

        return $target;
    }

    public function getChildrenCountAttribute()
    {
        if(!$this->relationLoaded('children')){
            $this->load('children');
        }

        return $this->children->count();
    }

    public function getTypeAttribute()
    {
        $type = null;

        foreach($this->types as $slug => $availableType){
            if(strpos($this->url, $slug.'/') !== FALSE){
                $type = $slug;
                break;
            }
        }

        return $type;
    }

    //Methods
    public function getObject()
    {
        $type = $this->type;

        $id = str_replace($type.'/', '', $this->url);

        $model = call_user_func($this->types[$type].'::findOrFail', $id);

        return $model;
    }

    public function isTrailOf($path)
    {
        $trails = $this->menu->getTrails($path)->pluck('id');

        return $trails->contains($this->id);
    }

    //Statics
    public static function getLinkTargetOptions()
    {
        return [
            '_self' => 'Current Tab',
            '_blank' => 'New Tab'
        ];
    }

    public static function getRootPages()
    {
        return self::whereNull('parent_id')->orderBy('sort_order', 'ASC')->get();
    }

    public static function getPossibleParentOptions($menu_id = null, $exclude=null)
    {
        if(empty($exclude)){
            $exclude = [0];
        }

        $options = [];
        $qb = self::whereNotIn('id', [$exclude])->whereNull('parent_id')->orderBy('sort_order', 'ASC');

        if(!empty($menu_id)){
            $qb->where('menu_id', $menu_id);
        }

        $roots = $qb->get();

        self::_loopChildrenOptions($options, $roots, 0, $exclude);

        return $options;
    }

    private static function _loopChildrenOptions(&$options, $children, $level, $exclude=null)
    {
        foreach($children as $child){
            $options[$child->id] = str_pad($child->name, $level+strlen(trim($child->name)), '-', STR_PAD_LEFT);

            $grandChildren = $child->children()->whereNotIn('id', [$exclude])->get();

            self::_loopChildrenOptions($options, $grandChildren, $level+1, $exclude);
        }
    }
}
