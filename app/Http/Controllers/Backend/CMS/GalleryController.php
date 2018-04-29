<?php

namespace Kommercio\Http\Controllers\Backend\CMS;

use Kommercio\Http\Controllers\Controller;
use Kommercio\Http\Requests\Backend\CMS\GalleryFormRequest;
use Kommercio\Models\CMS\Gallery;

class GalleryController extends Controller{
    public function index()
    {
        $qb = Gallery::orderBy('created_at', 'DESC');

        $galleries = $qb->get();

        return view('backend.cms.gallery.index', [
            'galleries' => $galleries,
        ]);
    }

    public function create()
    {
        $gallery = new Gallery();

        return view('backend.cms.gallery.create', [
            'gallery' => $gallery,
        ]);
    }

    public function store(GalleryFormRequest $request)
    {
        $gallery = new Gallery();
        $gallery->fill($request->all());
        $gallery->save();

        $gallery->galleryCategories()->sync($request->input('categories', []));

        if($request->filled('images')){
            foreach($request->input('images', []) as $idx=>$image){
                $images[$image] = [
                    'type' => 'image',
                    'caption' => $request->input('images_caption.'.$idx, null),
                    'locale' => $gallery->getTranslation()->locale
                ];
            }
            $gallery->getTranslation()->attachMedia($images, 'image');
        }

        if($request->filled('thumbnail')){
            foreach($request->input('thumbnail', []) as $idx=>$image){
                $thumbnail[$image] = [
                    'type' => 'thumbnail',
                    'caption' => $request->input('thumbnail_caption.'.$idx, null),
                    'locale' => $gallery->getTranslation()->locale
                ];
            }
            $gallery->getTranslation()->attachMedia($thumbnail, 'thumbnail');
        }

        return redirect($request->get('backUrl', route('backend.cms.gallery.index')))->with('success', [$gallery->name.' has successfully been created.']);
    }

    public function edit($id)
    {
        $gallery = Gallery::findOrFail($id);

        return view('backend.cms.gallery.edit', [
            'gallery' => $gallery,
        ]);
    }

    public function update(GalleryFormRequest $request, $id)
    {
        $gallery = Gallery::findOrFail($id);
        $gallery->fill($request->all());
        $gallery->save();

        $gallery->galleryCategories()->sync($request->input('categories', []));

        $images = [];
        foreach($request->input('images', []) as $idx=>$image){
            $images[$image] = [
                'type' => 'image',
                'caption' => $request->input('images_caption.'.$idx, null),
                'locale' => $gallery->getTranslation()->locale
            ];
        }
        $gallery->getTranslation()->syncMedia($images, 'image');

        $thumbnail = [];
        foreach($request->input('thumbnail', []) as $idx=>$image){
            $thumbnail[$image] = [
                'type' => 'thumbnail',
                'caption' => $request->input('thumbnail_caption.'.$idx, null),
                'locale' => $gallery->getTranslation()->locale
            ];
        }
        $gallery->getTranslation()->syncMedia($thumbnail, 'thumbnail');

        return redirect($request->get('backUrl', route('backend.cms.gallery.index')))->with('success', [$gallery->name.' has successfully been updated.']);
    }

    public function delete($id)
    {
        $gallery = Gallery::findOrFail($id);

        $name = $gallery->name;

        //Remove all media first. We do it manually because Translation model is cascaded, so we can't do this on Translation delete
        foreach($gallery->translations as $translation){
            $translation->deleteMedia('image');
            $translation->deleteMedia('thumbnail');
        }

        $gallery->delete();

        return redirect()->back()->with('success', [$name.' has been deleted.']);
    }
}
