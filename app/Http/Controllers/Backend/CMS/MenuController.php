<?php

namespace Kommercio\Http\Controllers\Backend\CMS;

use Illuminate\Http\Request;
use Kommercio\Http\Controllers\Controller;
use Kommercio\Models\CMS\Menu;

class MenuController extends Controller{
    public function index()
    {
        $qb = Menu::orderBy('created_at', 'DESC');

        $menus = $qb->get();

        return view('backend.cms.menu.index', [
            'menus' => $menus,
        ]);
    }

    public function create()
    {
        $menu = new Menu();

        return view('backend.cms.menu.create', [
            'menu' => $menu,
        ]);
    }

    public function store(Request $request)
    {
        $rules = $this->getRules();
        $this->validate($request, $rules);

        $menu = new Menu();

        $menu->fill($request->all());
        $menu->save();

        return redirect()->route('backend.cms.menu.index')->with('success', [$menu->name.' has successfully been created.']);
    }

    public function edit($id)
    {
        $menu = Menu::findOrFail($id);

        return view('backend.cms.menu.edit', [
            'menu' => $menu,
        ]);
    }

    public function update(Request $request, $id)
    {
        $rules = $this->getRules($id);
        $this->validate($request, $rules);

        $menu = Menu::findOrFail($id);

        $menu->fill($request->all());
        $menu->save();

        return redirect($request->get('backUrl', route('backend.cms.menu.index')))->with('success', [$menu->name.' has successfully been updated.']);
    }

    public function delete($id)
    {
        $menu = Menu::findOrFail($id);

        $name = $menu->name;
        $menu->delete();

        return redirect()->back()->with('success', [$name.' has been deleted.']);
    }

    protected function getRules($id=null)
    {
        $rules = [
            'name' => 'required',
            'slug' => 'required|unique:menus,slug'.($id?','.$id:'')
        ];

        return $rules;
    }
}