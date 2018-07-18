<?php

namespace Kommercio\Http\Controllers\Api\Frontend\Block;

use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Kommercio\Http\Controllers\Controller;
use Kommercio\Models\CMS\Block;
use Kommercio\Http\Resources\Block\BlockResource;

class BlockController extends Controller {

    public function get(Request $request) {
        $this->validate($request, [
            'slugOrId' => 'required',
        ]);

        $slugOrId = $request->input('slugOrId');

        if (is_numeric($slugOrId)) {
            $block = Block::findById($slugOrId);
        } else {
            $block = Block::getBySlug($slugOrId);
        }

        if (!$block || !$block->active) {
            $responseCode = !$block ? 404 : 403;
            return new JsonResponse(
                [
                    'errors' => [
                        'You have never been here'
                    ],
                ],
                $responseCode
            );
        }

        $response = new BlockResource($block);

        return $response->response();
    }
}
