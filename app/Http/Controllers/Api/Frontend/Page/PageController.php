<?php

namespace Kommercio\Http\Controllers\Api\Frontend\Page;

use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Cache;
use Kommercio\Http\Controllers\Controller;
use Kommercio\Models\CMS\Page;
use Kommercio\Http\Resources\Page\PageResource;

class PageController extends Controller {

    public function get(Request $request) {
        $this->validate($request, [
            'slugOrId' => 'required',
        ]);

        $slugOrId = $request->input('slugOrId');

        if (is_numeric($slugOrId)) {
            $page = Page::findById($slugOrId);
        } else {
            $page = Page::getBySlug($slugOrId);
        }

        if (!$page || !$page->active) {
            return new JsonResponse(
                [
                    'errors' => [
                        'You have never been here'
                    ],
                ],
                403
            );
        }

        $children = Cache::remember(
            $page->getTable() . '_' . $page->id . '.children',
            3600,
            function() use ($page) {
                $children = $page->children;

                return $children->filter(function($childPage) {
                    return $childPage->active;
                })->values();
            }
        );

        $response = new PageResource($page);
        $response->additional([
            'data' => [
                'children' => PageResource::collection($children),
            ],
        ]);

        return $response->response();
    }
}
