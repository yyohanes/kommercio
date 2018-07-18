<?php

namespace Kommercio\Http\Resources\Page;

use Illuminate\Http\Resources\Json\Resource;
use Kommercio\Http\Resources\Media\ImageCollection;
use Kommercio\Models\CMS\Page;

class PageResource extends Resource {

    public function toArray($request) {
        /** @var Page $page */
        $page = $this->resource;

        $response = [
            'id' => $page->id,
            'name' => $page->name,
            'slug' => $page->slug,
            'body' => $page->body,
            'images' => new ImageCollection($page->images),
            'metaTitle' => $page->meta_title,
            'metaDescription' => $page->meta_description,
            'sortOrder' => $page->sort_order,
            'active' => !empty($page->active),
        ];

        return $response;
    }
}
