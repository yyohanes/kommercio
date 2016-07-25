<?php

namespace Kommercio\Traits\Frontend;

use Kommercio\Facades\Shortcode;
use Kommercio\Models\Product;

trait ProductHelper
{
    //'width', 'length', 'depth', 'weight', 'thumbnail', 'images', 'thumbnails';

    public function getShortDescription()
    {
        return Shortcode::doShortcode($this->description_short);
    }

    public function getDescription()
    {
        return Shortcode::doShortcode($this->description);
    }

    public function getActiveVariations()
    {
        $variations = [];

        foreach($this->variations as $variation){
            if($variation->productDetail->active){
                $variations[] = $variation;
            }
        }

        return $variations;
    }

    public function getDefaultVariation()
    {
        $variations = $this->getActiveVariations();

        return isset($variations[0])?$variations[0]:$this;
    }

    public function getSelectableAttributes()
    {
        $attributes = [];

        foreach($this->getActiveVariations() as $variation){
            foreach($variation->productAttributeValues as $attributeValue){
                if(!isset($attributes[$attributeValue->productAttribute->id])){
                    $attributes[$attributeValue->productAttribute->id] = [
                        'id' => $attributeValue->productAttribute->id,
                        'label' => $attributeValue->productAttribute->name,
                        'options' => []
                    ];
                }

                $attributes[$attributeValue->productAttribute->id]['options'][$attributeValue->id] = [
                    'label' => $attributeValue->name,
                    'id' => $attributeValue->id
                ];
            }
        }

        return $attributes;
    }

    public function getThumbnails()
    {
        $thumbnails = $this->thumbnails;

        if($this->combination_type == Product::COMBINATION_TYPE_VARIATION){
            if($thumbnails->count() < 1){
                $thumbnails = $this->parent->thumbnails;
            }
        }

        return $thumbnails;
    }

    public function getThumbnail()
    {
        $thumbnails = $this->getThumbnails();

        return $thumbnails->get(0);
    }

    public function getImages()
    {
        $images = $this->images;

        if($this->combination_type == Product::COMBINATION_TYPE_VARIATION){
            if($images->count() < 1){
                $images = $this->parent->images;
            }
        }

        return $images;
    }

    public function getShippingInformation()
    {
        $array = [
            'width' => $this->width,
            'length' => $this->length,
            'depth' => $this->depth,
            'weight' => $this->weight,
        ];

        if($this->combination_type == Product::COMBINATION_TYPE_VARIATION){
            foreach($array as $idx => &$arrayItem){
                if(is_null($arrayItem)){
                    $arrayItem = $this->parent->{$idx};
                }
            }
        }

        return $array;
    }
}