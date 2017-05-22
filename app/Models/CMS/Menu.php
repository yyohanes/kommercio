<?php

namespace Kommercio\Models\CMS;

use Illuminate\Support\Facades\Cache;
use Kommercio\Facades\RuntimeCache;
use Kommercio\Models\Abstracts\SluggableModel;
use Kommercio\Models\Interfaces\CacheableInterface;

class Menu extends SluggableModel implements CacheableInterface
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
            $menuItems = $menu->rootMenuItems->filter(function($value){
                return $value->active;
            })->pluck('parent_id')->all();
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

    /**
     * @inheritdoc
     */
    public function getCacheKeys()
    {
        $tableName = $this->getTable();

        $keys = [
            $tableName.'_'.$this->id.'.root_menu_items'
        ];

        return $keys;
    }

    // Accessors
    public function getRootMenuItemsAttribute()
    {
        $rootMenuItems = Cache::rememberForever($this->getTable().'_'.$this->id.'.root_menu_items', function(){
            return $this->menuItems->filter(function($value, $key){
                return empty($value->parent_id);
            });
        });

        return $rootMenuItems;
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
}
