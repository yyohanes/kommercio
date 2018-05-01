<?php

namespace Kommercio\Http\Resources\Media;

use Illuminate\Http\Resources\Json\ResourceCollection;

class ImageCollection extends ResourceCollection {
    public function toArray($request) {
        return ImageResource::collection($this->collection);
    }
}
