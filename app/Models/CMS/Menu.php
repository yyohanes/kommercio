<?php

namespace Kommercio\Models\CMS;

use Kommercio\Facades\RuntimeCache;
use Kommercio\Models\Abstracts\SluggableModel;

class Menu extends SluggableModel
{
    protected $fillable = ['name', 'slug', 'description'];

    //Methods
    public function getTrails($path)
    {
        if(!RuntimeCache::has($this->slug.'.'.$path)){
            $bag = [];

            $this->getActiveMenuItems($path, $this->rootMenuItems, 99, 1, $bag);

            if($bag){
                $deepest = end($bag);

                $bag = [$deepest];

                $parent = $deepest->parent;

                while($parent){
                    $bag[] = $parent;
                    $parent = $parent->parent;
                }

                reset($bag);

                $bag = collect(array_reverse($bag));
            }

            RuntimeCache::set($this->slug.'.'.$path, $bag);
        }

        return RuntimeCache::get($this->slug.'.'.$path);
    }

    public function getMenuItemSiblings($menuitem)
    {
        $menu = $this;

        if(is_null($menuitem->parent_id)){
            $menuItems = $menu->rootMenuItems()->active()->pluck('parent_id')->all();
        }else{
            $menuItems = [$menuitem->parent_id];
        }

        $siblings = $menu->menuItems()->active()->whereIn('parent_id', $menuItems)->get();

        return $siblings;
    }

    private function getActiveMenuItems($path, $menuItems, $level, $currentLevel, array &$bag)
    {
        if($currentLevel <= $level){
            foreach($menuItems as $menuItem){
                if($menuItem->url == $path){
                    $bag[] = $menuItem;
                }

                if($menuItem->children->count() > 0){
                    $this->getActiveMenuItems($path, $menuItem->children, $level, $currentLevel + 1, $bag);
                }
            }
        }

        return null;
    }

    // Static
    public static function getBySlug($slug)
    {
        $qb = self::whereTranslation('slug', $slug);
        $menu = $qb->first();

        return $menu;
    }

    // Scope
    public function scopeActive($query)
    {
        $query->where('active', true);
    }

    // Relations
    public function menuItems()
    {
        return $this->hasMany('Kommercio\Models\CMS\MenuItem')->orderBy('sort_order', 'ASC');
    }

    public function rootMenuItems()
    {
        return $this->hasMany('Kommercio\Models\CMS\MenuItem')->whereNull('parent_id')->orderBy('sort_order', 'ASC');
    }
}
