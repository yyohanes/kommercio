<?php

namespace Kommercio\Http\Resources\Media;

use Illuminate\Http\Resources\Json\Resource;

use Kommercio\Models\Media;

class ImageResource extends Resource {
    public function __construct(Media $media) {
        parent::__construct($media);
    }

    public function toArray($request) {
        return [
            'filename' => $this->resource->filename,
            'caption' => $this->resource->pivot->caption,
            'locale' => $this->resource->pivot->locale,
            'crops' => $this->getImageCrops(),
        ];
    }

    private function getImageCrops(): array {
        $cropStyles = array_merge(config('kommercio.image_styles'), config('project.image_styles', []));

        $crops = [];

        foreach (array_keys($cropStyles) as $cropStyle) {
            $crops[$cropStyle] = asset(config('app.url') . '/' . $this->resource->getImagePath($cropStyle));
        }

        return $crops;
    }
}
