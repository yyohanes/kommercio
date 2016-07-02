<?php

namespace Kommercio\Http\Controllers\Backend\CMS;

use Kommercio\Http\Controllers\Controller;
use Illuminate\Http\Request;
use Kommercio\Http\Requests\Backend\CMS\BannerFormRequest;
use Kommercio\Models\CMS\Banner;
use Kommercio\Models\CMS\BannerGroup;

class BannerController extends Controller{
    public function index($banner_group_id)
    {
        $bannerGroup = BannerGroup::findOrFail($banner_group_id);

        $banners = $bannerGroup->banners;

        return view('backend.cms.banner.index', [
            'banners' => $banners,
            'bannerGroup' => $bannerGroup
        ]);
    }

    public function create(Request $request)
    {
        $banner = new Banner();

        $bannerGroupOptions = BannerGroup::orderBy('created_at', 'DESC')->get();
        $bannerGroup = $request->has('banner_group_id')?BannerGroup::findOrFail($request->input('banner_group_id')):$bannerGroupOptions->first();

        return view('backend.cms.banner.create', [
            'banner' => $banner,
            'bannerGroup' => $bannerGroup,
            'bannerGroupOptions' => $bannerGroupOptions->pluck('name', 'id')->all()
        ]);
    }

    public function store(BannerFormRequest $request)
    {
        $banner = new Banner();
        $banner->fill($request->all());
        $banner->save();

        if($request->has('image')){
            foreach($request->input('image', []) as $idx=>$image){
                $images[$image] = [
                    'type' => 'image',
                    'caption' => $request->input('image_caption.'.$idx, null),
                    'locale' => $banner->getTranslation()->locale
                ];
            }
            $banner->getTranslation()->attachMedia($images, 'image');
        }

        return redirect($request->get('backUrl', route('backend.cms.banner.index', ['banner_group_id' => $banner->bannerGroup->id])))->with('success', [$banner->name.' has successfully been created.']);
    }

    public function edit($id)
    {
        $banner = Banner::findOrFail($id);

        $bannerGroupOptions = BannerGroup::orderBy('created_at', 'DESC')->get();

        return view('backend.cms.banner.edit', [
            'banner' => $banner,
            'bannerGroup' => $banner->bannerGroup,
            'bannerGroupOptions' => $bannerGroupOptions->pluck('name', 'id')->all()
        ]);
    }

    public function update(BannerFormRequest $request, $id)
    {
        $banner = Banner::findOrFail($id);
        $banner->fill($request->all());
        $banner->save();

        $images = [];
        foreach($request->input('image', []) as $idx=>$image){
            $images[$image] = [
                'type' => 'image',
                'caption' => $request->input('image_caption.'.$idx, null),
                'locale' => $banner->getTranslation()->locale
            ];
        }
        $banner->getTranslation()->syncMedia($images, 'image');

        return redirect($request->get('backUrl', route('backend.cms.banner.index')))->with('success', [$banner->name.' has successfully been updated.']);
    }

    public function delete($id)
    {
        $banner = Banner::findOrFail($id);

        $name = $banner->name;

        //Remove all media first. We do it manually because Translation model is cascaded, so we can't do this on Translation delete
        foreach($banner->translations as $translation){
            $translation->deleteMedia('image');
        }

        $banner->delete();

        return redirect()->back()->with('success', [$name.' has been deleted.']);
    }

    public function reorder(Request $request)
    {
        foreach($request->input('objects') as $idx=>$object){
            $banner = Banner::findOrFail($object);
            $banner->update([
                'sort_order' => $idx
            ]);
        }

        return response()->json([
            'result' => 'success',
        ]);
    }
}