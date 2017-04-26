<?php

namespace Kommercio\Http\Controllers\Frontend;

use Illuminate\Http\Request;

use Kommercio\Facades\LanguageHelper;
use Kommercio\Facades\ProjectHelper;
use Kommercio\Facades\RuntimeCache;
use Kommercio\Http\Requests;
use Kommercio\Models\Customer\Bookmark;
use Kommercio\Models\Customer\BookmarkType;
use Kommercio\Models\Product;
use Symfony\Component\HttpFoundation\JsonResponse;

class BookmarkController extends LoggedInController
{
    public function index()
    {
        $bookmarks = $this->customer->bookmarks;

        $seoData = [
            'meta_title' => trans(LanguageHelper::getTranslationKey('frontend.seo.member.bookmark.index.meta_title'))
        ];

        $viewName = ProjectHelper::getViewTemplate('frontend.member.bookmark.index');

        return view($viewName, [
            'bookmarks' => $bookmarks
        ]);
    }

    public function addToBookmark(Request $request, $slug, $product_sku)
    {
        $bookmarkType = BookmarkType::where('slug', $slug)->firstOrFail();

        if($this->customer){
            $product = RuntimeCache::getOrSet('product_sku.'.$product_sku, function() use ($product_sku){
                return Product::where('sku', $product_sku)->firstOrFail();
            });

            $bookmark = Bookmark::getOrNew($this->customer, $bookmarkType);
            $bookmark->add($product->id);

            $message = trans(LanguageHelper::getTranslationKey('frontend.bookmark.added'), ['product' => $product->name, 'bookmark' => $bookmark->name]);

            if($request->ajax()){
                return new JsonResponse([
                    'result' => 'added',
                    'message' => $message
                ]);
            }

            return redirect()->back()->with('success', [$message]);
        }else{
            return $this->notLoggedInResponse($request, $bookmarkType);
        }
    }

    public function removeFromBookmark(Request $request, $slug, $product_sku)
    {
        $bookmarkType = BookmarkType::where('slug', $slug)->firstOrFail();

        if($this->customer){
            $product = RuntimeCache::getOrSet('product_sku.'.$product_sku, function() use ($product_sku){
                return Product::where('sku', $product_sku)->firstOrFail();
            });

            $bookmark = Bookmark::getOrNew($this->customer, $bookmarkType);
            $bookmark->remove($product->id);

            $message = trans(LanguageHelper::getTranslationKey('frontend.bookmark.removed'), ['product' => $product->name, 'bookmark' => $bookmark->name]);

            if($request->ajax()){
                return new JsonResponse([
                    'result' => 'removed',
                    'message' => $message
                ]);
            }

            return redirect()->back()->with('success', [$message]);
        }else{
            return $this->notLoggedInResponse($request, $bookmarkType);
        }
    }

    public function toggleBookmark(Request $request, $slug, $product_sku)
    {
        $bookmarkType = BookmarkType::where('slug', $slug)->firstOrFail();

        if($this->customer){
            $bookmarkType = BookmarkType::where('slug', $slug)->firstOrFail();

            $product = RuntimeCache::getOrSet('product_sku.'.$product_sku, function() use ($product_sku){
                return Product::where('sku', $product_sku)->firstOrFail();
            });

            if($product->bookmarked($this->customer, $slug)){
                return $this->removeFromBookmark($request, $slug, $product_sku);
            }else{
                return $this->addToBookmark($request, $slug, $product_sku);
            }
        }else{
            return $this->notLoggedInResponse($request, $bookmarkType);
        }
    }

    protected function notLoggedInResponse(Request $request, $bookmark)
    {
        $message = trans(LanguageHelper::getTranslationKey('frontend.bookmark.not_logged_in'), ['bookmark' => $bookmark->name]);

        if($request->ajax()){
            return new JsonResponse([
                'message' => $message
            ], 401);
        }

        return redirect()->back()->with('success', [$message]);
    }
}
