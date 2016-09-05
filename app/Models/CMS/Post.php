<?php

namespace Kommercio\Models\CMS;

use Dimsav\Translatable\Translatable;
use Illuminate\Database\Eloquent\Model;
use Kommercio\Models\Interfaces\SeoModelInterface;
use Kommercio\Models\Interfaces\UrlAliasInterface;
use Kommercio\Traits\Model\SeoTrait;
use Kommercio\Traits\Model\ToggleDate;

class Post extends Model implements UrlAliasInterface, SeoModelInterface
{
    use SeoTrait, Translatable, ToggleDate {
        Translatable::setAttribute as translateableSetAttribute;
        ToggleDate::setAttribute insteadof Translatable;
    }

    protected $casts = [
        'active' => 'boolean',
    ];

    public $fillable = ['name', 'slug', 'body', 'meta_title', 'meta_description', 'thumbnail', 'images', 'active'];
    public $translatedAttributes = ['name', 'slug', 'body', 'meta_title', 'meta_description', 'thumbnail', 'images'];
    protected $toggleFields = ['active'];
    protected $seoDefaultFields = [
        'meta_description' => 'body'
    ];

    //Relations
    public function categories()
    {
        return $this->belongsToMany('Kommercio\Models\CMS\PostCategory', 'post_post_category');
    }

    //Methods
    public function getExternalPath()
    {
        $path = $this->getInternalPathSlug().'/'.$this->id;

        return FrontendHelper::get_url($path);
    }

    public function getUrlAlias()
    {
        $paths = [];

        $category = $this->categories->count() > 0?$this->categories->get(0):null;

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

        $category = $this->categories->count() > 0?$this->categories->get(0):null;

        while($category){
            $breadcrumbs[] = $category;
            $category = $category->parent;
        }

        $breadcrumbs = array_reverse($breadcrumbs);

        return $breadcrumbs;
    }

    public function getMetaImage()
    {
        return $this->thumbnail->count() > 0?$this->thumbnail->get(0)->getImagePath('original'):null;
    }
}
