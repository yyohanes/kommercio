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

        return view(ProjectHelper::getViewTemplate('frontend.page.view'), [
            'page' => $page
        ]);
    }
}
