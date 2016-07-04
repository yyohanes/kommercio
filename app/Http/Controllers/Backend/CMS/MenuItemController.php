<?php

namespace Kommercio\Http\Controllers\Backend\CMS;

use Illuminate\Http\Request;
use Kommercio\Http\Controllers\Controller;
use Kommercio\Http\Requests\Backend\CMS\MenuItemFormRequest;
use Kommercio\Models\CMS\Menu;
use Kommercio\Models\CMS\MenuItem;

class MenuItemController extends Controller{
    public function index($menu_id)
    {
        $menu = Menu::findOrFail($menu_id);

        $menuItems = $menu->menuItems()->whereNull('parent_id')->get();

        return view('backend.cms.menu_item.index', [
            'menuItems' => $menuItems,
            'menu' => $menu
        ]);
    }

    public function create(Request $request)
    {
        $menu = Menu::findOrFail($request->get('menu_id'));
        $menuItem = new MenuItem();

        $parentOptions = ['None'] + MenuItem::getPossibleParentOptions($menu->id, []);

        return view('backend.cms.menu_item.create', [
            'menuItem' => $menuItem,
            'menu' => $menu,
            'parentOptions' => $parentOptions
        ]);
    }

    public function store(MenuItemFormRequest $request)
    {
        $menuItem = new MenuItem();

        $menuItem->fill($request->all());
        $menuItem->getTranslation()->saveData($request->input('data'));
        $menuItem->save();


        return redirect()->route('backend.cms.menu_item.index', ['menu_id' => $menuItem->menu_id])->with('success', [$menuItem->name.' has successfully been created.']);
    }

    public function edit($id)
    {
        $menuItem = MenuItem::findOrFail($id);

        $parentOptions = ['None'] + MenuItem::getPossibleParentOptions($menuItem->menu_id, $menuItem->id);

        return view('backend.cms.menu_item.edit', [
            'menuItem' => $menuItem,
            'menu' => $menuItem->menu,
            'parentOptions' => $parentOptions
        ]);
    }

    public function update(MenuItemFormRequest $request, $id)
    {
        $menuItem = MenuItem::findOrFail($id);

        $menuItem->fill($request->all());
        $menuItem->getTranslation()->saveData($request->input('data'));
        $menuItem->save();


        return redirect($request->get('backUrl', route('backend.cms.menu_item.index', ['menu_id' => $menuItem->menu_id])))->with('success', [$menuItem->name.' has successfully been updated.']);
    }

    public function delete($id)
    {
        $menuItem = MenuItem::findOrFail($id);

        $name = $menuItem->name;
        $menuItem->delete();

        return redirect()->back()->with('success', [$name.' has been deleted.']);
    }

    public function reorder(Request $request)
    {
        $this->saveNewOrder($request->input('objects', []));

        if($request->ajax()){
            return response()->json([
                'result' => 'success',
            ]);
        }
    }

    protected function saveNewOrder($chilren, $parent = null)
    {
        foreach($chilren as $idx => $object){
            $menuItem = MenuItem::findOrFail($object['id']);
            $menuItem->update([
                'sort_order' => $idx + 1,
                'parent_id' => $parent
            ]);

            if(isset($object['children'])){
                $this->saveNewOrder($object['children'], $object['id']);
            }
        }
    }
}