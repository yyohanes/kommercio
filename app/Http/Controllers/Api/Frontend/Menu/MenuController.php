<?php

namespace Kommercio\Http\Controllers\Api\Frontend\Menu;

use Illuminate\Http\Request;
use Illuminate\Support\Facades\Cache;
use Kommercio\Http\Resources\Menu\MenuItemResource;
use Symfony\Component\HttpKernel\Exception\NotFoundHttpException;
use Kommercio\Http\Controllers\Controller;
use Kommercio\Http\Resources\Menu\MenuResource;
use Kommercio\Models\CMS\Menu;

class MenuController extends Controller {

    public function get(Request $request) {
        $slugOrId = $request->get('slugOrId');

        if (is_numeric($slugOrId)) {
            $menu = Menu::findById($slugOrId);
        } else {
            $menu = Menu::getBySlug($slugOrId);
        }

        if (!$menu) throw new NotFoundHttpException('Menu not found.');

        $menuItems = Cache::remember(
            $menu->getTable() . '_' . $menu->id . '.active_menu_items',
            3600,
            function() use ($menu) {
                $menuItems = $menu->menuItems;

                return $menuItems->filter(function($menuItem) {
                    return $menuItem->active;
                });
            }
        );

        $response = new MenuResource($menu);
        $response->additional([
            'data' => [
                'menuItems' => MenuItemResource::collection($menuItems),
            ],
        ]);

        return $response->response();
    }
}
