<?php

namespace Kommercio\Helpers;

use Illuminate\Support\Facades\DB;
use Kommercio\Facades\ProjectHelper as ProjectHelperFacade;
use Kommercio\Facades\RuntimeCache as RuntimeCacheFacade;
use Kommercio\Models\Catalog\FacetedNavigation;
use Kommercio\Models\Manufacturer;
use Kommercio\Models\ProductAttribute\ProductAttribute;
use Kommercio\Models\ProductAttribute\ProductAttributeValue;
use Kommercio\Models\ProductCategory;
use Kommercio\Models\ProductFeature\ProductFeature;
use Kommercio\Models\ProductFeature\ProductFeatureValue;

class ProductIndexHelper
{
    public function getProductIndexQuery($alias = true)
    {
        return DB::table('product_index'.($alias?' AS PI':''));
    }

    public function getProductIndexPriceQuery($alias = true)
    {
        return DB::table('product_index_price'.($alias?' AS PIP':''));
    }

    public function getModelByType($type, $id)
    {
        switch($type){
            case 'manufacturer':
                $model = RuntimeCacheFacade::getOrSet('manufacturer.'.$id, function() use ($id){
                    return Manufacturer::findOrFail($id);
                });
                break;
            case 'product_category':
                $model = RuntimeCacheFacade::getOrSet('product_category.'.$id, function() use ($id){
                    return ProductCategory::withTranslation()->where('id', $id)->firstOrFail();
                });
                break;
            case 'product_attribute':
                $model = RuntimeCacheFacade::getOrSet('product_attribute.'.$id, function() use ($id){
                    return ProductAttribute::withTranslation()->where('id', $id)->firstOrFail();
                });
                break;
            case 'product_attribute_value':
                $model = RuntimeCacheFacade::getOrSet('product_attribute_value.'.$id, function() use ($id){
                    return ProductAttributeValue::withTranslation()->where('id', $id)->firstOrFail();
                });
                break;
            case 'product_feature':
                $model = RuntimeCacheFacade::getOrSet('product_feature.'.$id, function() use ($id){
                    return ProductFeature::withTranslation()->where('id', $id)->firstOrFail();
                });
                break;
            case 'product_feature_value':
                $model = RuntimeCacheFacade::getOrSet('product_feature_value.'.$id, function() use ($id){
                    return ProductFeatureValue::withTranslation()->where('id', $id)->firstOrFail();
                });
                break;
        }

        return $model;
    }

    public function buildFacetedNavigation($options = [])
    {
        $facetedLayers = new FacetedNavigation($options);

        return $facetedLayers;
    }
}