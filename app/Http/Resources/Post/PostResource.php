<?php

namespace Kommercio\Http\Resources\Post;

use Illuminate\Http\Resources\Json\Resource;
use Kommercio\Facades\Shortcode;
use Kommercio\Http\Resources\Media\ImageCollection;
use Kommercio\Http\Resources\Media\ImageResource;
use Kommercio\Models\CMS\Post;

class PostResource extends Resource {

    public function toArray($request) {
        /** @var Post $post */
        $post = $this->resource;

        $response = [
            'id' => $post->id,
            'name' => $post->name,
            'slug' => $post->slug,
            'categories' => $this->whenLoaded('postCategories', PostCategoryResource::collection($post->postCategories)),
            'teaser' => Shortcode::doShortcode($post->teaser),
            'body' => Shortcode::doShortcode($post->body),
            'thumbnail' => $post->thumbnail ? new ImageResource($post->thumbnail) : null,
            'images' => new ImageCollection($post->images),
            'metaTitle' => $post->meta_title,
            'metaDescription' => $post->meta_description,
            'createdAt' => $post->created_at->toIso8601String(),
            'updatedAt' => $post->updated_at->toIso8601String(),
            'active' => !empty($post->active),
        ];

        return $response;
    }
}
