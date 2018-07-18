<?php

namespace Kommercio\Http\Resources\Banner;

use Illuminate\Http\Resources\Json\Resource;
use Kommercio\Http\Resources\Media\ImageCollection;
use Kommercio\Models\CMS\Banner;

class BannerResource extends Resource {
    public function toArray($request) {
        /** @var Banner $banner */
        $banner = $this->resource;

        return [
            'id' => $banner->id,
            'name' => $banner->name,
            'body' => $banner->body,
            'images' => new ImageCollection($banner->images),
            'active' => !!$banner->active,
            'sortOrder' => $banner->sort_order,
            'link' => [
                'url' => $banner->getData('url'),
                'target' => $banner->getData('target', '_self'),
                'text' => $banner->getData('callToAction'),
            ],
            'cssClass' => $banner->getData('class'),
            'bannerGroup' => $this->whenLoaded('bannerGroup'),
        ];
    }
}
