<?php

namespace Kommercio\Http\Controllers\Frontend;

use Kommercio\Models\CMS\Gallery;
use Kommercio\Http\Controllers\Controller;
use Kommercio\Facades\ProjectHelper;
use Kommercio\Models\CMS\GalleryCategory;

class GalleryController extends Controller
{
    public function viewGallery($id)
    {
        $gallery = Gallery::findOrFail($id);

        $view_name = ProjectHelper::findViewTemplate($gallery->getViewSuggestions());

        return view($view_name, [
            'gallery' => $gallery,
            'seoModel' => $gallery
        ]);
    }

    public function viewCategory($id)
    {
        $galleryCategory = GalleryCategory::findOrFail($id);

        $qb = $galleryCategory->galleries()->active();
        $galleries = $qb->get();

        $view_name = ProjectHelper::findViewTemplate($galleryCategory->getViewSuggestions());

        return view($view_name, [
            'galleryCategory' => $galleryCategory,
            'galleries' => $galleries,
            'seoModel' => $galleryCategory
        ]);
    }
}
