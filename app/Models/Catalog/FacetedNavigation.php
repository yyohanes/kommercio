<?php

namespace Kommercio\Models\Catalog;

use Illuminate\Support\Facades\DB;
use Kommercio\Facades\LanguageHelper;
use Kommercio\Facades\ProductIndexHelper;
use Kommercio\Facades\ProjectHelper;
use Kommercio\Facades\RuntimeCache;
use Kommercio\Models\Manufacturer;
use Kommercio\Models\ProductAttribute\ProductAttribute;
use Kommercio\Models\ProductAttribute\ProductAttributeValue;
use Kommercio\Models\ProductCategory;
use Kommercio\Models\ProductFeature\ProductFeatureValue;

class FacetedNavigation
{
    protected $facetedLayers = [];

    public function __construct($options)
    {
        $this->process($options);
    }

    public function getFacetedLayers()
    {
        return $this->facetedLayers;
    }

    protected function process($options)
    {
        $qb = ProductIndexHelper::getProductIndexQuery()
            ->select(DB::raw('PI.*, COUNT(*) AS product_count'))
            ->whereRaw('PI.root_product_id = PI.product_id')
            ->where('PI.store_id', ProjectHelper::getActiveStore()->id);

        //Filter by products
        if(isset($options['products'])){
            $qb->whereIn('product_id', $options['products']->pluck('id')->all());
        }

        $qb->groupBy('PI.type', 'pivot');
        $qb->orderBy('product_count', 'DESC');
        $layers = $qb->get();

        foreach($layers as $layer){
            $this->buildLayer($layer, $options, $qb);
        }
    }

    protected function processOptions($options, $qb, $layer = null)
    {
        $qb->join('products AS P', function($query){
            $query->on('P.id', '=', 'product_id');
        });

        if(isset($options['manufacturer']) && $layer != 'manufacturer_0'){
            $manufacturers = [];
            foreach(FacetedLayer::parseCurrentOptions($options['manufacturer']) as $manufacturerSlug){
                $manufacturer = RuntimeCache::getOrSet('manufacturer['.$manufacturerSlug.']', function() use ($manufacturerSlug){
                    return Manufacturer::where('slug', $manufacturerSlug)->first();
                });

                if($manufacturer){
                    $manufacturers[] = $manufacturer->id;
                }else{
                    $manufacturers[] = 'wrong filter';
                }
            }

            $qb->whereIn('P.manufacturer_id', $manufacturers);
        }

        if(isset($options['category']) && $layer != 'product_category_0'){
            $categories = [];
            foreach(FacetedLayer::parseCurrentOptions($options['category']) as $categorySlug){
                $category = RuntimeCache::getOrSet('product_category['.$categorySlug.']', function() use ($categorySlug){
                    return ProductCategory::whereTranslation('slug', $categorySlug)->first();
                });

                if($category){
                    $categories[] = $category->id;
                }else{
                    $categories[] = 'wrong filter';
                }
            }

            $qb->whereExists(function($query) use ($categories){
                $query
                    ->select(DB::raw('1'))
                    ->from('category_product AS C')
                    ->whereRaw('C.product_id = P.id')
                    ->whereIn('C.product_category_id', $categories);
            });
        }

        if(isset($options['attribute'])){
            foreach($options['attribute'] as $attribute => $attributeParameter){
                $attributeValues = [];

                if($layer != 'product_attribute_'.$attribute){
                    foreach(FacetedLayer::parseCurrentOptions($attributeParameter) as $attributeValue){
                        $attributeValue = RuntimeCache::getOrSet('product_attribute_value['.$attributeValue.']', function() use ($attributeValue){
                            return ProductAttributeValue::whereTranslation('slug', $attributeValue)->first();
                        });

                        if($attributeValue){
                            $attributeValues[] = $attributeValue->id;
                        }
                    }

                    if(count($attributeValues) > 0){
                        $qb->whereExists(function($query) use ($attributeValues){
                            $query
                                ->select(DB::raw('1'))
                                ->from('product_product_attribute AS PA')
                                ->leftJoin('products AS P2', 'P2.id', '=', 'PA.product_id')
                                ->whereRaw('(PA.product_id = P.id OR P2.parent_id = P.id)')
                                ->whereIn('PA.product_attribute_value_id', $attributeValues);
                        });
                    }
                }
            }
        }

        if(isset($options['feature'])){
            foreach($options['feature'] as $feature => $featureParameter){
                $attributeValues = [];

                if($layer != 'product_feature_'.$feature){
                    foreach(FacetedLayer::parseCurrentOptions($featureParameter) as $featureValue){
                        $featureValue = RuntimeCache::getOrSet('product_feature_value['.$featureValue.']', function() use ($featureValue){
                            return ProductFeatureValue::whereTranslation('slug', $featureValue)->first();
                        });

                        if($featureValue){
                            $featureValues[] = $featureValue->id;
                        }
                    }

                    if(count($featureValues) > 0){
                        $qb->whereExists(function($query) use ($featureValues){
                            $query
                                ->select(DB::raw('1'))
                                ->from('product_product_feature AS PF')
                                ->leftJoin('products AS P2', 'P2.id', '=', 'PF.product_id')
                                ->whereRaw('(PF.product_id = P.id OR P2.parent_id = P.id)')
                                ->whereIn('PF.product_attribute_value_id', $featureValues);
                        });
                    }
                }
            }
        }
    }

    protected function buildLayer($layer, $options, $qb)
    {
        if($layer->pivot){
            $pivot = ProductIndexHelper::getModelByType($layer->type, $layer->pivot);
        }else{
            $pivot = 0;
        }

        if(!isset($this->facetedLayers[$layer->type])){
            $this->facetedLayers[$layer->type] = [];
        }

        $facetedLayer = new FacetedLayer($layer);

        $layerQb = clone $qb;
        $layerQb->where('PI.type', $layer->type)->groupBy('value');

        if($layer->pivot){
            $layerQb->where('PI.pivot', $layer->pivot);
        }else{
            $layerQb->whereNull('PI.pivot');
        }

        $this->processOptions($options, $layerQb, $layer->type.'_'.($pivot?$pivot->slug:0));

        foreach($layerQb->get() as $option){
            $facetedLayer->appendRawChild($option->value, $option->product_count);
        }

        $this->facetedLayers[$layer->type][($pivot?$pivot->slug:0)] = $facetedLayer;
    }
}
