<?php

namespace Kommercio\Models\CMS;

use Illuminate\Support\Facades\Cache;
use Kommercio\Facades\RuntimeCache;
use Kommercio\Models\Abstracts\SluggableModel;
use Kommercio\Models\Interfaces\CacheableInterface;

class Menu extends SluggableModel implements CacheableInterface
{
    public $fillable = ['name', 'slug', 'description'];

    //Methods
    public function getTrails($path)
    {
        if(!RuntimeCache::has($this->slug.'_'.$path)){
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

            RuntimeCache::set($this->slug.'_'.$path, $bag);
        }

        return RuntimeCache::get($this->slug.'_'.$path);
    }

    public function getMenuItemSiblings($menuitem)
    {
        $menu = $this;

        if(is_null($menuitem->parent_id)){
            $menuItems = $menu->rootMenuItems;

            return $menuItems;
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
            $tableName.'_'.$this->slug,
            $tableName.'_'.$this->id.'.root_menu_items',
            $tableName.'_'.$this->id.'.active_menu_items'
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
    public static function findById($id)
    {
        $tableName = (new static)->getTable();
        $menu = Cache::remember($tableName. '_' . $id, 3600, function() use ($id) {
            return static::find($id);
        });

        return $menu;
    }

    public static function getBySlug($slug)
    {
        $tableName = (new static)->getTable();

        return Cache::remember($tableName . '_' . $slug, 3600, function() use ($slug) {
            $qb = self::where('slug', $slug);
            $menu = $qb->first();

            return $menu;
        });

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
