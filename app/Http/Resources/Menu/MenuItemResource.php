<?php

namespace Kommercio\Http\Resources\Menu;

use Illuminate\Http\Resources\Json\Resource;
use Kommercio\Models\CMS\MenuItem;

class MenuItemResource extends Resource {

    public function toArray($request) {
        /** @var MenuItem $menuItem */
        $menuItem = $this->resource;

        return [
            'id' => $menuItem->id,
            'name' => $menuItem->name,
            'menuClass' => $menuItem->menu_class,
            'url' => $menuItem->url,
            'urlTarget' => $menuItem->target,
            'urlAlias' => $menuItem->externalPath,
            'sortOrder' => $menuItem->sort_order,
            'active' => !empty($menuItem->active),
            'children' => static::collection($menuItem->children),
        ];
    }
}
