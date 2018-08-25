<?php

namespace Kommercio\Http\Controllers\Api\Frontend\Newsletter;

use Illuminate\Http\JsonResponse;
use Kommercio\Facades\NewsletterSubscriptionHelper;
use Kommercio\Http\Requests\Api\Newsletter\NewsletterFormRequest;
use Kommercio\Http\Controllers\Controller;

class NewsletterController extends Controller {

    public function subscribe(NewsletterFormRequest $request) {
        $response = 200;

        try {
            NewsletterSubscriptionHelper::subscribe(
                $request->input('group', 'default'),
                $request->input('email')
            );
        } catch (\Throwable $e) {
            report($e);
            $response = 500;
        }

        return new JsonResponse([
            'data' => [
                'message' => 'Success',
            ],
        ], $response);
    }
}
