<?php

namespace Kommercio\Models\CMS;

use Dimsav\Translatable\Translatable;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Support\Facades\Cache;
use Kommercio\Facades\FrontendHelper;
use Kommercio\Models\Interfaces\CacheableInterface;
use Kommercio\Models\Interfaces\SeoModelInterface;
use Kommercio\Models\Interfaces\UrlAliasInterface;
use Kommercio\Traits\Model\SeoTrait;
use Kommercio\Traits\Model\ToggleDate;

class Page extends Model implements UrlAliasInterface, SeoModelInterface, CacheableInterface
{
    use SeoTrait, Translatable, ToggleDate {
        Translatable::setAttribute as translateableSetAttribute;
        ToggleDate::setAttribute insteadof Translatable;

        Translatable::translations as translatableTranslations;
    }

    protected $casts = [
        'active' => 'boolean',
    ];

    public $translatedAttributes = ['name', 'slug', 'body', 'meta_title', 'meta_description', 'images'];
    public $fillable = ['name', 'slug', 'body', 'parent_id', 'meta_title', 'meta_description', 'images', 'active', 'sort_order'];
    protected $toggleFields = ['active'];
    protected $seoDefaultFields = [
        'meta_description' => 'body'
    ];

    private $_cachedRelationResults;

    //Relations
    public function parent()
    {
        return $this->belongsTo('Kommercio\Models\CMS\Page', 'parent_id');
    }

    public function children()
    {
        return $this->hasMany('Kommercio\Models\CMS\Page', 'parent_id')->orderBy('sort_order', 'ASC');
    }

    //Methods
    public function getExternalPath()
    {
        $path = $this->getInternalPathSlug().'/'.$this->id;

        return FrontendHelper::getUrl($path);
    }

    public function getUrlAlias()
    {
        $paths = [];

        $parent = $this->parent;
        while($parent){
            $paths[] = $parent->slug;
            $parent = $parent->parent;
        }
        $paths = array_reverse($paths);

        $paths[] = $this->slug;

        return implode('/', $paths);
    }

    public function getInternalPathSlug()
    {
        return 'page';
    }

    public function getBreadcrumbTrails()
    {
        $parent = $this->parent;

        $breadcrumbs = [];

        while($parent){
            $breadcrumbs[] = $parent;
            $parent = $parent->parent;
        }

        $breadcrumbs = array_reverse($breadcrumbs);

        return $breadcrumbs;
    }

    public function getMetaImage()
    {
        return $this->images->count() > 0?$this->images->get(0)->getImagePath('original'):null;
    }

    public function getCacheKeys()
    {
        $tableName = $this->getTable();
        $keys = [
            $tableName.'_'.$this->id,
            $tableName.'_'.$this->slug,
            $tableName.'_'.$this->id.'_translations',
            $tableName.'_'.$this->id.'_children',
        ];

        return $keys;
    }

    public function getTranslationsAttribute()
    {
        if (!isset($this->_cachedRelationResults['_translations'])) {
            $this->_cachedRelationResults['_translations'] = Cache::rememberForever($this->getTable().'_'.$this->id.'_translations', function () {
                return $this->translatableTranslations;
            });
        }

        return $this->_cachedRelationResults['_translations'];
    }

    //Accessors
    public function getChildrenCountAttribute()
    {
        if(!$this->relationLoaded('children')){
            $this->load('children');
        }

        return $this->children->count();
    }

    //Statics
    public static function findById($id)
    {
        $tableName = (new static)->getTable();
        $page = Cache::remember($tableName. '_' . $id, 3600, function() use ($id) {
            return static::find($id);
        });

        return $page;
    }

    public static function getBySlug($slug)
    {
        $tableName = (new static)->getTable();
        $page = Cache::remember($tableName. '_' . $slug, 3600, function() use ($slug) {
            return self::whereTranslation('slug', $slug)->first();
        });

        return $page;
    }

    public static function getRootPages()
    {
        return self::whereNull('parent_id')->orderBy('created_at', 'DESC')->get();
    }

    public static function getPossibleParentOptions($exclude=null)
    {
        if(empty($exclude)){
            $exclude = [0];
        }

        $options = [];
        $roots = self::whereNotIn('id', [$exclude])->whereNull('parent_id')->orderBy('created_at', 'DESC')->get();

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
