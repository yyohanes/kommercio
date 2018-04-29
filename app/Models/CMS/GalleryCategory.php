<?php

namespace Kommercio\Models\CMS;

use Dimsav\Translatable\Translatable;
use Illuminate\Database\Eloquent\Model;
use Kommercio\Facades\FrontendHelper;
use Kommercio\Models\Interfaces\SeoModelInterface;
use Kommercio\Models\Interfaces\UrlAliasInterface;
use Kommercio\Traits\Model\SeoTrait;

class GalleryCategory extends Model implements UrlAliasInterface, SeoModelInterface
{
    use Translatable, SeoTrait;

    public $fillable = ['name', 'body', 'parent_id', 'sort_order', 'slug', 'meta_title', 'meta_description'];
    public $translatedAttributes = ['name', 'slug', 'body', 'meta_title', 'meta_description'];

    private $_rootCategory;

    //Methods
    public function getName()
    {
        return $this->name.($this->parent?' ('.$this->parent->name.')':'');
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

    public function getExternalPath()
    {
        $path = $this->getInternalPathSlug().'/'.$this->id;

        return FrontendHelper::getUrl($path);
    }

    public function getInternalPathSlug()
    {
        return 'gallery-category';
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

    //Relations
    public function galleries()
    {
        return $this->BelongsToMany('Kommercio\Models\CMS\Gallery', 'gallery_gallery_category');
    }

    public function parent()
    {
        return $this->belongsTo('Kommercio\Models\CMS\GalleryCategory', 'parent_id');
    }

    public function children()
    {
        return $this->hasMany('Kommercio\Models\CMS\GalleryCategory', 'parent_id')->orderBy('sort_order', 'ASC');
    }

    //Methods
    public function getViewSuggestions()
    {
        $viewSuggestions = [];

        $viewSuggestions[] = 'frontend.gallery.category.view_'.$this->id;

        if($this->parent){
            $viewSuggestions[] = 'frontend.gallery.category.view_inherit_'.$this->parent->id;
        }

        $viewSuggestions[] = 'frontend.gallery.category.view';

        return $viewSuggestions;
    }

    public function getMetaImage()
    {
        return ($this->galleries->count() > 0 && $this->galleries->get(0)->thumbnail)?$this->galleries->get(0)->thumbnail->getImagePath('original'):null;
    }

    //Accessors
    public function getChildrenCountAttribute()
    {
        if(!$this->relationLoaded('children')){
            $this->load('children');
        }

        return $this->children->count();
    }

    public function getGalleryCountAttribute()
    {
        if(!$this->relationLoaded('galleries')){
            $this->load('galleries');
        }

        return $this->galleries->count();
    }

    public function getRootAttribute()
    {
        if(!isset($this->_rootCategory)){
            if($this->parent){
                $breadcrumbs = $this->getBreadcrumbTrails();
                $this->_rootCategory = $breadcrumbs[0];
            }else{
                $this->_rootCategory = $this;
            }
        }

        return $this->_rootCategory;
    }

    //Statics
    public static function getRootCategories()
    {
        return self::whereNull('parent_id')->orderBy('sort_order', 'ASC')->get();
    }

    public static function getPossibleParentOptions($exclude=null)
    {
        if(empty($exclude)){
            $exclude = [0];
        }

        $options = [];
        $roots = self::whereNotIn('id', [$exclude])->whereNull('parent_id')->orderBy('sort_order', 'ASC')->get();

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
