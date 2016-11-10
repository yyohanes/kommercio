<?php

namespace Kommercio\Http\Controllers\Backend\CMS;

use Illuminate\Http\Request;
use Kommercio\Http\Controllers\Controller;
use Kommercio\Http\Requests\Backend\CMS\GalleryCategoryFormRequest;
use Kommercio\Models\CMS\GalleryCategory;

class GalleryCategoryController extends Controller{
    public function index($parent=null)
    {
        $qb = GalleryCategory::orderBy('sort_order', 'ASC');
        if($parent){
            $qb->where('parent_id', $parent);
            $parentCategory = GalleryCategory::findOrFail($parent);
        }else{
            $qb->whereNull('parent_id');
            $parentCategory = null;
        }

        $galleryCategories = $qb->get();

        return view('backend.cms.gallery.category.index', [
            'galleryCategories' => $galleryCategories,
            'parentCategory' => $parentCategory
        ]);
    }

    public function create(Request $request)
    {
        $galleryCategory = new GalleryCategory([
            'active' => 1,
            'parent_id' => $request->get('parent_id')
        ]);
        $parentOptions = ['None'] + GalleryCategory::getPossibleParentOptions([]);

        return view('backend.cms.gallery.category.create', [
            'galleryCategory' => $galleryCategory,
            'parentOptions' => $parentOptions
        ]);
    }

    public function store(GalleryCategoryFormRequest $request)
    {
        $galleryCategory = new GalleryCategory();

        $galleryCategory->fill($request->all());
        $galleryCategory ->save();

        return redirect()->route('backend.cms.gallery.category.index', ['parent' => $galleryCategory ->parent_id])->with('success', [$galleryCategory ->name.' has successfully been created.']);
    }

    public function edit($id)
    {
        $galleryCategory = GalleryCategory::findOrFail($id);
        $parentOptions = ['Root Category'] + GalleryCategory::getPossibleParentOptions($galleryCategory->id);

        return view('backend.cms.gallery.category.edit', [
            'galleryCategory' => $galleryCategory,
            'parentOptions' => $parentOptions
        ]);
    }

    public function update(GalleryCategoryFormRequest $request, $id)
    {
        $galleryCategory = GalleryCategory::findOrFail($id);

        $galleryCategory->fill($request->all());
        $galleryCategory->save();

        return redirect()->back()->with('success', [$galleryCategory->name.' has successfully been updated.']);
    }

    public function delete($id)
    {
        $galleryCategory = GalleryCategory::findOrFail($id);

        $name = $galleryCategory->name;

        $galleryCategory->delete();

        return redirect()->back()->with('success', [$name.' has been deleted.']);
    }

    public function reorder(Request $request)
    {
        foreach($request->input('objects') as $idx=>$object){
            $category = GalleryCategory::findOrFail($object);
            $category->update([
                'sort_order' => $idx
            ]);
        }

        if($request->ajax()){
            return response()->json([
                'result' => 'success',
            ]);
        }else{
            return redirect()->route('backend.cms.gallery.category.index');
        }
    }
}