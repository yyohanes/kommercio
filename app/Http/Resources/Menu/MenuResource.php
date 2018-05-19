<?php

namespace Kommercio\Http\Resources\Menu;

use Illuminate\Http\Resources\Json\Resource;
use Kommercio\Models\CMS\Menu;

class MenuResource extends Resource {

    public function toArray($request) {
        /** @var Menu $menu */
        $menu = $this->resource;

        return [
            'id' => $menu->id,
            'name' => $menu->name,
            'slug' => $menu->slug,
            'description' => $menu->description,
        ];
    }
}
