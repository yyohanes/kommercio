<?php

namespace Kommercio\Http\Controllers\Backend\CMS;

use Kommercio\Http\Controllers\Controller;
use Kommercio\Http\Requests\Backend\CMS\PageFormRequest;
use Kommercio\Models\CMS\Page;

class PageController extends Controller{
    public function index()
    {
        $qb = Page::orderBy('created_at', 'DESC');
        $qb->whereNull('parent_id');

        $pages = $qb->get();

        return view('backend.cms.page.index', [
            'pages' => $pages,
        ]);
    }

    public function create()
    {
        $page = new Page();
        $parentOptions = ['None'] + Page::getPossibleParentOptions([]);

        return view('backend.cms.page.create', [
            'page' => $page,
            'parentOptions' => $parentOptions
        ]);
    }

    public function store(PageFormRequest $request)
    {
        $page = new Page();

        $page->fill($request->all());
        $page->save();

        if($request->has('image')){
            foreach($request->input('image', []) as $idx=>$image){
                $images[$image] = [
                    'type' => 'image',
                    'caption' => $request->input('image_caption.'.$idx, null),
                    'locale' => $page->getTranslation()->locale
                ];
            }
            $page->getTranslation()->attachMedia($images, 'image');
        }

        return redirect()->route('backend.cms.page.index', ['parent' => $page->parent_id])->with('success', [$page->name.' has successfully been created.']);
    }

    public function edit($id)
    {
        $page = Page::findOrFail($id);
        $parentOptions = ['None'] + Page::getPossibleParentOptions($page->id);

        return view('backend.cms.page.edit', [
            'page' => $page,
            'parentOptions' => $parentOptions
        ]);
    }

    public function update(PageFormRequest $request, $id)
    {
        $page = Page::findOrFail($id);

        $page->fill($request->all());
        $page->save();

        $images = [];
        foreach($request->input('image', []) as $idx=>$image){
            $images[$image] = [
                'type' => 'image',
                'caption' => $request->input('image_caption.'.$idx, null),
                'locale' => $page->getTranslation()->locale
            ];
        }
        $page->getTranslation()->syncMedia($images, 'image');

        return redirect()->back()->with('success', [$page->name.' has successfully been updated.']);
    }

    public function delete($id)
    {
        $page = Page::findOrFail($id);

        $name = $page->name;

        //Remove all media first. We do it manually because Translation model is cascaded, so we can't do this on Translation delete
        foreach($page->translations as $translation){
            $translation->deleteMedia('image');
        }

        $page->delete();

        return redirect()->back()->with('success', [$name.' has been deleted.']);
    }
}