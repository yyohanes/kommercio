<?php

namespace Kommercio\Http\Controllers\Backend\Catalog;

use Illuminate\Http\Request;
use Kommercio\Http\Controllers\Controller;
use Kommercio\Http\Requests\Backend\Catalog\ProductCategoryFormRequest;
use Kommercio\Models\ProductCategory;

class CategoryController extends Controller{
    public function index($parent=null)
    {
        $qb = ProductCategory::orderBy('sort_order', 'ASC');
        if($parent){
            $qb->where('parent_id', $parent);
            $parentCategory = ProductCategory::findOrFail($parent);
        }else{
            $qb->whereNull('parent_id');
            $parentCategory = null;
        }

        $categories = $qb->get();

        return view('backend.catalog.category.index', [
            'categories' => $categories,
            'parentCategory' => $parentCategory
        ]);
    }

    public function create(Request $request)
    {
        $category = new ProductCategory([
            'active' => 1,
            'parent_id' => $request->get('parent_id')
        ]);
        $parentOptions = ['Root Category'] + ProductCategory::getPossibleParentOptions([]);

        return view('backend.catalog.category.create', [
            'category' => $category,
            'parentOptions' => $parentOptions
        ]);
    }

    public function store(ProductCategoryFormRequest $request)
    {
        $category = new ProductCategory();

        $category->fill($request->all());
        $category->save();

        if($request->has('image')){
            foreach($request->input('image', []) as $idx=>$image){
                $images[$image] = [
                    'type' => 'image',
                    'caption' => $request->input('image_caption.'.$idx, null),
                    'locale' => $category->getTranslation()->locale
                ];
            }
            $category->getTranslation()->attachMedia($images, 'image');
        }

        if($request->has('thumbnail')){
            foreach($request->input('thumbnail', []) as $idx=>$image){
                $thumbnail[$image] = [
                    'type' => 'thumbnail',
                    'caption' => $request->input('thumbnail_caption.'.$idx, null),
                    'locale' => $category->getTranslation()->locale
                ];
            }
            $category->getTranslation()->attachMedia($thumbnail, 'thumbnail');
        }

        return redirect()->route('backend.catalog.category.index', ['parent' => $category->parent_id])->with('success', [$category->name.' has successfully been created.']);
    }

    public function edit($id)
    {
        $category = ProductCategory::findOrFail($id);
        $parentOptions = ['Root Category'] + ProductCategory::getPossibleParentOptions($category->id);

        return view('backend.catalog.category.edit', [
            'category' => $category,
            'parentOptions' => $parentOptions
        ]);
    }

    public function update(ProductCategoryFormRequest $request, $id)
    {
        $category = ProductCategory::findOrFail($id);

        $category->fill($request->all());
        $category->update();

        $images = [];
        foreach($request->input('image', []) as $idx=>$image){
            $images[$image] = [
                'type' => 'image',
                'caption' => $request->input('image_caption.'.$idx, null),
                'locale' => $category->getTranslation()->locale
            ];
        }
        $category->getTranslation()->syncMedia($images, 'image');

        $thumbnail = [];
        foreach($request->input('thumbnail', []) as $idx=>$image){
            $thumbnail[$image] = [
                'type' => 'thumbnail',
                'caption' => $request->input('thumbnail_caption.'.$idx, null),
                'locale' => $category->getTranslation()->locale
            ];
        }
        $category->getTranslation()->syncMedia($thumbnail, 'thumbnail');

        return redirect()->back()->with('success', [$category->name.' has successfully been updated.']);
    }

    public function delete($id)
    {
        $category = ProductCategory::findOrFail($id);

        $name = $category->name;

        //Remove all media first. We do it manually because Translation model is cascaded, so we can't do this on Translation delete
        foreach($category->translations as $translation){
            $translation->deleteMedia('image');
            $translation->deleteMedia('thumbnail');
        }

        $category->delete();

        return redirect()->back()->with('success', [$name.' has been deleted.']);
    }

    public function reorder(Request $request)
    {
        foreach($request->input('objects') as $idx=>$object){
            $category = ProductCategory::findOrFail($object);
            $category->update([
                'sort_order' => $idx
            ]);
        }

        if($request->ajax()){
            return response()->json([
                'result' => 'success',
            ]);
        }else{
            return redirect()->route('backend.catalog.category.index');
        }
    }

    public function autocomplete(Request $request)
    {
        $return = [];
        $search = $request->get('query', '');

        if(!empty($search)){
            $qb = ProductCategory::with('parent')->whereTranslationLike('name', '%'.$search.'%');

            $results = $qb->get();

            foreach($results as $result){
                $return[] = [
                    'id' => $result->id,
                    'name' => $result->getName(),
                    'tokens' => [
                        $result->name
                    ]
                ];
            }
        }

        return response()->json(['data' => $return, '_token' => csrf_token()]);
    }
}