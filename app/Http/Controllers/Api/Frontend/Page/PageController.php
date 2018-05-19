<?php

namespace Kommercio\Http\Controllers\Api\Frontend\Page;

use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
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
            $page = Page::getPageBySlug($slugOrId);
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

        $response = new PageResource($page);

        return $response->response();
    }
}
