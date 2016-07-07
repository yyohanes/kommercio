<?php

namespace Kommercio\Traits\Model;

trait SeoTrait
{
    public function getMetaTitle()
    {
        $meta_title = $this->meta_title;

        if(empty($meta_title)){
            $meta_title = $this->getAttribute((isset($this->seoDefaultFields['meta_title'])?$this->seoDefaultFields['meta_title']:'name'));
        }

        return $meta_title;
    }

    public function getMetaDescription()
    {
        $meta_description = $this->meta_description;

        if(empty($meta_description)){
            $meta_description = $this->getAttribute((isset($this->seoDefaultFields['meta_description'])?$this->seoDefaultFields['meta_description']:'description'));
        }

        $meta_description = str_limit(strip_tags($meta_description), 157, '...');

        return $meta_description;
    }
}