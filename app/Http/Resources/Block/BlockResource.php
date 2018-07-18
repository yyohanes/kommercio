<?php

namespace Kommercio\Http\Resources\Block;

use Illuminate\Http\Resources\Json\Resource;
use Kommercio\Models\CMS\Block;

class BlockResource extends Resource {

    public function toArray($request) {
        /** @var Block $block */
        $block = $this->resource;

        return [
            'id' => $block->id,
            'name' => $block->name,
            'body' => $block->body,
            'machineName' => $block->machine_name,
            'type' => $block->type,
            'active' => !!$block->active,
        ];
    }
}
