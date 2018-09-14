<?php

namespace Kommercio\Http\Controllers\Api\Frontend\Post;

use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Database\Eloquent\Builder;
use Kommercio\Http\Resources\Post\PostCategoryResource;
use Kommercio\Http\Controllers\Controller;
use Kommercio\Models\CMS\PostCategory;

class PostCategoryController extends Controller {

    public function get(Request $request) {
        $this->validate($request, [
            'slugOrId' => 'required',
        ]);

        $slugOrId = $request->input('slugOrId');

        if (is_numeric($slugOrId)) {
            $postCategory = PostCategory::findById($slugOrId);
        } else {
            $postCategory = PostCategory::getBySlug($slugOrId);
        }

        if (!$postCategory) {
            return new JsonResponse(
                [
                    'errors' => [
                        'You have never been here'
                    ],
                ],
                403
            );
        }

        $response = new PostCategoryResource($postCategory);
        $response->additional([
            'data' => [
                'children' => PostCategoryResource::collection($postCategory->children),
            ],
        ]);

        return $response->response();
    }

    public function categories(Request $request) {
        $perPage = $request->get('per_page', 25);

        /** @var Builder $qb */
        $qb = PostCategory::query();

        if ($request->get('parent_id')) {
            $qb->where('parent_id', $request->get('parent_id'));
        }

        $postCategories = $qb->paginate($perPage);
        $postCategories->appends($request->except('page'));

        $response = PostCategoryResource::collection($postCategories);

        return $response->response();
    }
}
