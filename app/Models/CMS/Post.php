<?php

namespace Kommercio\Models\CMS;

use Dimsav\Translatable\Translatable;
use Illuminate\Database\Eloquent\Model;
use Kommercio\Facades\FrontendHelper;
use Kommercio\Models\Interfaces\SeoModelInterface;
use Kommercio\Models\Interfaces\UrlAliasInterface;
use Kommercio\Traits\Model\SeoTrait;
use Kommercio\Traits\Model\ToggleDate;
use Illuminate\Support\Facades\DB;

class Post extends Model implements UrlAliasInterface, SeoModelInterface
{
    use SeoTrait, Translatable, ToggleDate {
        Translatable::setAttribute as translateableSetAttribute;
        ToggleDate::setAttribute insteadof Translatable;
    }

    protected $casts = [
        'active' => 'boolean',
    ];

    public $fillable = ['name', 'slug', 'body', 'teaser', 'meta_title', 'meta_description', 'active', 'created_at'];
    public $translatedAttributes = ['name', 'slug', 'body', 'teaser', 'meta_title', 'meta_description', 'thumbnail', 'images'];
    protected $toggleFields = ['active'];
    protected $seoDefaultFields = [
        'meta_description' => 'body'
    ];

    //Relations
    public function postCategories()
    {
        return $this->belongsToMany('Kommercio\Models\CMS\PostCategory', 'post_post_category');
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

        $category = $this->postCategories->count() > 0?$this->postCategories->get(0):null;

        while($category){
            $paths[] = $category->slug;
            $category = $category->parent;
        }
        $paths = array_reverse($paths);

        $paths[] = $this->slug;

        return implode('/', $paths);
    }

    public function getInternalPathSlug()
    {
        return 'post';
    }

    public function getBreadcrumbTrails()
    {
        $breadcrumbs = [];

        $category = $this->postCategories->count() > 0?$this->postCategories->get(0):null;

        while($category){
            $breadcrumbs[] = $category;
            $category = $category->parent;
        }

        $breadcrumbs = array_reverse($breadcrumbs);

        return $breadcrumbs;
    }

    //Methods
    public function getViewSuggestions()
    {
        $viewSuggestions = [];

        $viewSuggestions[] = 'frontend.post.view_'.$this->id;

        if($this->postCategories){
            $viewSuggestions[] = 'frontend.post.view_inherit_'.$this->postCategories->get(0)->id;
        }

        $viewSuggestions[] = 'frontend.post.view';

        return $viewSuggestions;
    }

    public function getMetaImage()
    {
        return $this->thumbnail?$this->thumbnail->getImagePath('original'):null;
    }

    //Scopes
    public function scopeActive($query)
    {
        $query->where('active', true);
    }

    public function scopeSelectSelf($query)
    {
        $query->selectRaw($this->getTable().'.*');
    }

    public function scopeJoinTranslation($query, $locale=null)
    {
        $locale = $locale?$locale:$this->locale();

        $query
            ->leftJoin($this->getTranslationsTable().' as T', function($join) use ($locale){
                $join->on('T.post_id', '=', $this->getTable().'.id')
                    ->where('T.'.$this->getLocaleKey(), '=', $locale);
            });

        $query->addSelect(DB::raw('T.*'));
    }
}
