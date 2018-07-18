<?php

namespace Kommercio\Http\Resources\Banner;

use Illuminate\Http\Resources\Json\Resource;
use Kommercio\Models\CMS\BannerGroup;

class BannerGroupResource extends Resource {

    public function toArray($request) {
        /** @var BannerGroup $bannerGroup */
        $bannerGroup = $this->resource;

        $response = [
            'id' => $bannerGroup->id,
            'name' => $bannerGroup->name,
            'slug' => $bannerGroup->slug,
            'description' => $bannerGroup->description,
        ];

        if ($bannerGroup->relationLoaded('banners')) {
            $banners = $bannerGroup->banners->filter(function($banner) {
                return $banner->active;
            });
            $response['banners'] = BannerResource::collection($banners);
        }

        return $response;
    }
}
