<?php

namespace Kommercio\Http\Controllers\Api\Frontend\Post;

use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Database\Eloquent\Builder;
use Kommercio\Http\Resources\Post\PostResource;
use Kommercio\Http\Controllers\Controller;
use Kommercio\Models\CMS\Post;
use Kommercio\Models\CMS\PostCategory;

class PostController extends Controller {

    public function get(Request $request) {
        $this->validate($request, [
            'slugOrId' => 'required',
        ]);

        $slugOrId = $request->input('slugOrId');

        if (is_numeric($slugOrId)) {
            $post = Post::findById($slugOrId);
        } else {
            $post = Post::getBySlug($slugOrId);
        }

        if (!$post) {
            return new JsonResponse(
                [
                    'errors' => [
                        'You have never been here'
                    ],
                ],
                403
            );
        }

        $response = new PostResource($post);

        return $response->response();
    }

    public function posts(Request $request) {
        $perPage = $request->get('per_page', 25);

        /** @var Builder $qb */
        $qb = Post::active();

        if ($request->get('categories')) {
            $categories = explode(',', $request->get('categories'));
            $qb->whereHas('postCategories', function($query) use ($categories) {
                $categoryIds = array_map(function($category) {
                    if (!is_numeric($category)) {
                        $categoryObject = PostCategory::getBySlug($category);

                        return $categoryObject->id;
                    }

                    return $category;
                }, $categories);

                $query->whereIn('id', $categoryIds);
            });
        }

        $sortOption = $request->get('sort', 'created_at:desc');
        $sort = explode(':', $sortOption);
        $sortBy = $sort[0] ?? 'created_at';
        $sortDirection = $sort[1] ?? 'desc';

        $qb->orderBy($sortBy, $sortDirection);

        $posts = $qb->paginate($perPage);
        $posts->appends($request->except('page'));

        $response = PostResource::collection($posts);

        return $response->response();
    }
}
