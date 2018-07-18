<?php

namespace Kommercio\Models\CMS;

use Dimsav\Translatable\Translatable;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Support\Facades\Cache;
use Kommercio\Facades\ProjectHelper;
use Kommercio\Models\Interfaces\CacheableInterface;
use Kommercio\Traits\Model\ToggleDate;

class Block extends Model implements CacheableInterface
{
    use Translatable, ToggleDate {
        Translatable::setAttribute as translateableSetAttribute;
        ToggleDate::setAttribute insteadof Translatable;

        Translatable::translations as translatableTranslations;
    }

    const TYPE_STATIC = 'static';

    public $fillable = ['name', 'body', 'machine_name', 'type', 'active'];
    public $translatedAttributes = ['name', 'body'];
    protected $toggleFields = ['active'];

    private $_cachedRelationResults;

    public function getCacheKeys()
    {
        $tableName = $this->getTable();
        $keys = [
            $tableName.'_'.$this->id,
            $tableName.'_'.$this->machine_name,
            $tableName.'_'.$this->id.'_translations',
        ];

        return $keys;
    }

    // Scope
    public function scopeActive($query)
    {
        $query->where('active', true);
    }

    public function render()
    {
        $view_name = ProjectHelper::findViewTemplate(['frontend.block.view_'.$this->machine_name, 'frontend.block.view']);

        return view($view_name, [
            'block' => $this,
        ]);
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

    // Statics
    public static function findById($id)
    {
        $tableName = (new static)->getTable();
        $bannerGroup = Cache::remember($tableName. '_' . $id, 3600, function() use ($id) {
            return static::find($id);
        });

        return $bannerGroup;
    }

    public static function getBySlug($machine_name)
    {
        $block = Cache::rememberForever(with(new self())->getTable().'_'.$machine_name, function () use ($machine_name) {
            $block = self::where('machine_name', $machine_name)->first();

            return $block;
        });

        return $block;
    }
}
