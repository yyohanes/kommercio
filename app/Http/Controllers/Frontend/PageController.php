<?php

namespace Kommercio\Http\Controllers\Frontend;

use Kommercio\Models\CMS\Page;
use Kommercio\Http\Controllers\Controller;
use Kommercio\Facades\ProjectHelper;

class PageController extends Controller
{
    public function view($id)
    {
        $page = Page::findOrFail($id);

        $view_name = ProjectHelper::findViewTemplate(['frontend.page.view_'.$page->id, 'frontend.page.view']);

        return view($view_name, [
            'page' => $page,
            'seoModel' => $page
        ]);
    }
}
