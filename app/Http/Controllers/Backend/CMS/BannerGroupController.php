<?php

namespace Kommercio\Http\Controllers\Backend\CMS;

use Kommercio\Http\Controllers\Controller;
use Illuminate\Http\Request;
use Kommercio\Http\Requests\Backend\CMS\BannerGroupFormRequest;
use Kommercio\Models\CMS\BannerGroup;

class BannerGroupController extends Controller{
    public function index()
    {
        $bannerGroups = BannerGroup::orderBy('created_at', 'DESC')->get();

        return view('backend.cms.banner_group.index', [
            'bannerGroups' => $bannerGroups
        ]);
    }

    public function create()
    {
        $bannerGroup = new BannerGroup();

        return view('backend.cms.banner_group.create', [
            'bannerGroup' => $bannerGroup
        ]);
    }

    public function store(BannerGroupFormRequest $request)
    {
        $bannerGroup = new BannerGroup();
        $bannerGroup->fill($request->all());
        $bannerGroup->save();

        return redirect($request->get('backUrl', route('backend.cms.banner_group.index')))->with('success', [$bannerGroup->name.' has successfully been created.']);
    }

    public function edit($id)
    {
        $bannerGroup = BannerGroup::findOrFail($id);

        return view('backend.cms.banner_group.edit', [
            'bannerGroup' => $bannerGroup
        ]);
    }

    public function update(BannerGroupFormRequest $request, $id)
    {
        $bannerGroup = BannerGroup::findOrFail($id);
        $bannerGroup->fill($request->all());
        $bannerGroup->save();

        return redirect($request->get('backUrl', route('backend.cms.banner_group.index')))->with('success', [$bannerGroup->name.' has successfully been updated.']);
    }

    public function delete($id)
    {
        $bannerGroup = BannerGroup::findOrFail($id);

        $name = $bannerGroup->name;

        $bannerGroup->delete();

        return redirect()->back()->with('success', [$name.' has been deleted.']);
    }
}