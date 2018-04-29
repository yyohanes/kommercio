<?php

namespace Kommercio\Http\Controllers\Backend\CMS;

use Illuminate\Http\Request;
use Kommercio\Http\Controllers\Controller;
use Kommercio\Http\Requests\Backend\CMS\PostCategoryFormRequest;
use Kommercio\Models\CMS\PostCategory;

class PostCategoryController extends Controller{
    public function index($parent=null)
    {
        $qb = PostCategory::orderBy('sort_order', 'ASC');
        if($parent){
            $qb->where('parent_id', $parent);
            $parentCategory = PostCategory::findOrFail($parent);
        }else{
            $qb->whereNull('parent_id');
            $parentCategory = null;
        }

        $postCategories = $qb->get();

        return view('backend.cms.post.category.index', [
            'postCategories' => $postCategories,
            'parentCategory' => $parentCategory
        ]);
    }

    public function create(Request $request)
    {
        $postCategory = new PostCategory([
            'active' => 1,
            'parent_id' => $request->get('parent_id')
        ]);
        $parentOptions = ['None'] + PostCategory::getPossibleParentOptions([]);

        return view('backend.cms.post.category.create', [
            'postCategory' => $postCategory,
            'parentOptions' => $parentOptions
        ]);
    }

    public function store(PostCategoryFormRequest $request)
    {
        $postCategory = new PostCategory();

        $postCategory->fill($request->except(['images', 'thumbnail']));
        $postCategory->save();

        if($request->filled('image')){
            foreach($request->input('image', []) as $idx=>$image){
                $images[$image] = [
                    'type' => 'image',
                    'caption' => $request->input('image_caption.'.$idx, null),
                    'locale' => $postCategory->getTranslation()->locale
                ];
            }
            $postCategory->getTranslation()->attachMedia($images, 'image');
        }

        if($request->filled('thumbnail')){
            foreach($request->input('thumbnail', []) as $idx=>$image){
                $thumbnail[$image] = [
                    'type' => 'thumbnail',
                    'caption' => $request->input('thumbnail_caption.'.$idx, null),
                    'locale' => $postCategory->getTranslation()->locale
                ];
            }
            $postCategory->getTranslation()->attachMedia($thumbnail, 'thumbnail');
        }

        return redirect()->route('backend.cms.post.category.index', ['parent' => $postCategory->parent_id])->with('success', [$postCategory->name.' has successfully been created.']);
    }

    public function edit($id)
    {
        $postCategory = PostCategory::findOrFail($id);
        $parentOptions = ['Root Category'] + PostCategory::getPossibleParentOptions($postCategory->id);

        return view('backend.cms.post.category.edit', [
            'postCategory' => $postCategory,
            'parentOptions' => $parentOptions
        ]);
    }

    public function update(PostCategoryFormRequest $request, $id)
    {
        $postCategory = PostCategory::findOrFail($id);

        $postCategory->fill($request->except(['images', 'thumbnail']));
        $postCategory->save();

        $images = [];
        foreach($request->input('image', []) as $idx=>$image){
            $images[$image] = [
                'type' => 'image',
                'caption' => $request->input('image_caption.'.$idx, null),
                'locale' => $postCategory->getTranslation()->locale
            ];
        }
        $postCategory->getTranslation()->syncMedia($images, 'image');

        $thumbnail = [];
        foreach($request->input('thumbnail', []) as $idx=>$image){
            $thumbnail[$image] = [
                'type' => 'thumbnail',
                'caption' => $request->input('thumbnail_caption.'.$idx, null),
                'locale' => $postCategory->getTranslation()->locale
            ];
        }
        $postCategory->getTranslation()->syncMedia($thumbnail, 'thumbnail');

        return redirect()->back()->with('success', [$postCategory->name.' has successfully been updated.']);
    }

    public function delete($id)
    {
        $postCategory = PostCategory::findOrFail($id);

        $name = $postCategory->name;

        //Remove all media first. We do it manually because Translation model is cascaded, so we can't do this on Translation delete
        foreach($postCategory->translations as $translation){
            $translation->deleteMedia('thumbnail');
            $translation->deleteMedia('image');
        }

        $postCategory->delete();

        return redirect()->back()->with('success', [$name.' has been deleted.']);
    }

    public function reorder(Request $request)
    {
        foreach($request->input('objects') as $idx=>$object){
            $category = PostCategory::findOrFail($object);
            $category->update([
                'sort_order' => $idx
            ]);
        }

        if($request->ajax()){
            return response()->json([
                'result' => 'success',
            ]);
        }else{
            return redirect()->route('backend.cms.post.category.index');
        }
    }
}
