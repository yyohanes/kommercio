<?php

namespace Kommercio\Models\Catalog;

use Illuminate\Support\Facades\Request;
use Kommercio\Facades\FrontendHelper;
use Kommercio\Facades\LanguageHelper;
use Kommercio\Facades\ProductIndexHelper;
use Kommercio\Facades\RuntimeCache;

class FacetedLayer
{
    public $type;
    public $label;
    public $pivot;
    protected $children;
    protected $rawChildren = [];

    public function __construct($layer)
    {
        $this->build($layer);
    }

    public function appendRawChild($child, $count)
    {
        $this->rawChildren[$child] = $count;
    }

    public function getChildren()
    {
        if(!isset($this->children)){
            foreach ($this->rawChildren as $rawChild => $count) {
                $this->children[] = [
                    'model' => ProductIndexHelper::getModelByType($this->getChildType(), $rawChild),
                    'product_count' => $count
                ];
            }
        }

        return $this->children;
    }

    public function getFormName()
    {
        switch($this->type){
            case 'manufacturer':
                $key = 'manufacturer';
                break;
            case 'product_category':
                $key = 'category';
                break;
            case 'product_attribute':
                $key = 'attribute['.$this->pivot->slug.']';
                break;
            case 'product_feature':
                $key = 'feature['.$this->pivot->slug.']';
                break;
        }

        return $key;
    }

    public function getFacetValue($option)
    {
        $key = $this->getValueStructure($option);

        $value = array_flatten($key);

        return array_shift($value);

        return $facetValue;
    }

    public function isActive($option)
    {
        $structure = array_dot($this->getValueStructure($option));

        $current = Request::input(key($structure), null);
        $current = self::parseCurrentOptions($current);

        return in_array($option['model']->slug, $current);
    }

    public function getSetPath($option)
    {
        $key = $this->getValueStructure($option);

        return FrontendHelper::getCurrentUrlWithQuery($key);
    }

    public function getUnsetPath($option)
    {
        $key = $this->getValueStructure($option, true);

        return FrontendHelper::getCurrentUrlWithQuery($key);
    }

    public function getTogglePath($option)
    {
        if($this->isActive($option)){
            return $this->getUnsetPath($option);
        }else{
            return $this->getSetPath($option);
        }
    }

    protected function getValueStructure($option, $unset = false)
    {
        $function = $unset?'tailOption':'topOption';

        switch($this->type){
            case 'manufacturer':
                $key = [
                    'manufacturer' => $this->$function($option['model']->slug, Request::input('manufacturer'))
                ];
                break;
            case 'product_category':
                $key = [
                    'category' => $this->$function($option['model']->slug, Request::input('category'))
                ];
                break;
            case 'product_attribute':
                $key = [
                    'attribute' => [
                        $this->pivot->slug => $this->$function($option['model']->slug, Request::input('attribute.'.$this->pivot->slug))
                    ]
                ];
                break;
            case 'product_feature':
                $key = [
                    'feature' => [
                        $this->pivot->slug => $this->$function($option['model']->slug, Request::input('feature.'.$this->pivot->slug))
                    ]
                ];
                break;
        }

        return $key;
    }

    protected function topOption($add, $current)
    {
        $current = self::parseCurrentOptions($current);

        if(!in_array($add, $current)){
            $current[] = $add;
        }

        return implode('--', $current);
    }

    protected function tailOption($add, $current)
    {
        $current = self::parseCurrentOptions($current);

        $position = array_search($add, $current);

        if(!is_bool($position)){
            unset($current[$position]);
        }

        return implode('--', $current);
    }

    protected function build($layer)
    {
        $this->type = $layer->type;
        $this->pivot = $layer->pivot?ProductIndexHelper::getModelByType($layer->type, ($layer->pivot?:$layer->value)):null;

        if($layer->type == 'manufacturer'){
            $this->label = trans(LanguageHelper::getTranslationKey('frontend.catalog.manufacturer'));
        }elseif($layer->type == 'product_category') {
            $this->label = trans(LanguageHelper::getTranslationKey('frontend.catalog.product_category'));
        }else{
            $this->label = $this->pivot->name;
        }
    }

    protected function getChildType()
    {
        $type = $this->type;

        switch($this->type){
            case 'product_attribute':
                $type = 'product_attribute_value';
                break;
            case 'product_feature':
                $type = 'product_feature_value';
                break;
        }

        return $type;
    }

    public static function parseCurrentOptions($current)
    {
        $current = $current?(is_string($current)?explode('--', $current):$current):[];

        return $current;
    }
}
