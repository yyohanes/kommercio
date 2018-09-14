<?php

namespace Kommercio\Http\Resources\Post;

use Illuminate\Http\Resources\Json\Resource;
use Kommercio\Facades\Shortcode;
use Kommercio\Http\Resources\Media\ImageCollection;
use Kommercio\Models\CMS\PostCategory;

class PostCategoryResource extends Resource {

    public function toArray($request) {
        /** @var PostCategory $postCategory */
        $postCategory = $this->resource;

        $response = [
            'id' => $postCategory->id,
            'name' => $postCategory->name,
            'slug' => $postCategory->slug,
            'parent' => $this->whenLoaded('parent', new self($postCategory->parent)),
            'body' => Shortcode::doShortcode($postCategory->body),
            'images' => new ImageCollection($postCategory->images),
            'metaTitle' => $postCategory->meta_title,
            'metaDescription' => $postCategory->meta_description,
            'sortOrder' => $postCategory->sort_order,
        ];

        return $response;
    }
}
