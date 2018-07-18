<?php

namespace Kommercio\Http\Controllers\Api\Frontend\Banner;

use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Kommercio\Http\Controllers\Controller;
use Kommercio\Http\Resources\Banner\BannerGroupResource;
use Kommercio\Models\CMS\BannerGroup;

class BannerGroupController extends Controller {

    public function get(Request $request) {
        $this->validate($request, [
            'slugOrId' => 'required',
        ]);

        $slugOrId = $request->input('slugOrId');

        if (is_numeric($slugOrId)) {
            $bannerGroup = BannerGroup::findById($slugOrId);
        } else {
            $bannerGroup = BannerGroup::getBySlug($slugOrId);
        }

        if (!$bannerGroup) {
            return new JsonResponse(
                [
                    'errors' => [
                        'You have never been here'
                    ],
                ],
                404
            );
        }

        $bannerGroup->load('banners');
        $response = new BannerGroupResource($bannerGroup);

        return $response->response();
    }
}
