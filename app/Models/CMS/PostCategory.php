<?php

namespace Kommercio\Models\CMS;

use Dimsav\Translatable\Translatable;
use Illuminate\Database\Eloquent\Model;
use Kommercio\Facades\FrontendHelper;
use Kommercio\Models\Interfaces\SeoModelInterface;
use Kommercio\Models\Interfaces\UrlAliasInterface;
use Kommercio\Traits\Model\SeoTrait;

class PostCategory extends Model implements UrlAliasInterface, SeoModelInterface
{
    use Translatable, SeoTrait;

    protected $fillable = ['name', 'body', 'parent_id', 'sort_order', 'slug', 'meta_title', 'meta_description'];
    public $translatedAttributes = ['name', 'slug', 'body', 'meta_title', 'meta_description', 'images'];

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
        return 'post-category';
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
    public function posts()
    {
        return $this->BelongsToMany('Kommercio\Models\CMS\Post', 'post_post_category');
    }

    public function parent()
    {
        return $this->belongsTo('Kommercio\Models\CMS\PostCategory', 'parent_id');
    }

    public function children()
    {
        return $this->hasMany('Kommercio\Models\CMS\PostCategory', 'parent_id')->orderBy('sort_order', 'ASC');
    }

    //Methods
    public function getViewSuggestions()
    {
        $viewSuggestions = [];

        $viewSuggestions[] = 'frontend.post.category.view_'.$this->id;

        if($this->parent){
            $viewSuggestions[] = 'frontend.post.category.view_inherit_'.$this->parent->id;
        }

        $viewSuggestions[] = 'frontend.post.category.view';

        return $viewSuggestions;
    }

    public function getMetaImage()
    {
        return $this->images->count() > 0?$this->images->get(0)->getImagePath('original'):null;
    }

    //Accessors
    public function getChildrenCountAttribute()
    {
        if(!$this->relationLoaded('children')){
            $this->load('children');
        }

        return $this->children->count();
    }

    public function getPostCountAttribute()
    {
        if(!$this->relationLoaded('posts')){
            $this->load('posts');
        }

        return $this->posts->count();
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
