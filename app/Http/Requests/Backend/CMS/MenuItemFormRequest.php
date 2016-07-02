<?php

namespace Kommercio\Http\Requests\Backend\CMS;

use Kommercio\Http\Requests\Request;
use Kommercio\Models\CMS\Menu;

class MenuItemFormRequest extends Request
{
    /**
     * Determine if the user is authorized to make this request.
     *
     * @return bool
     */
    public function authorize()
    {
        return true;
    }

    /**
     * Get the validation rules that apply to the request.
     *
     * @return array
     */
    public function rules()
    {
        $allowedMenuIds = implode(',', Menu::pluck('id')->all());
        $menuId = $this->route('id');

        $rules = [
            'name' => 'required',
            'menu_id' => 'required|integer|in:'.$allowedMenuIds,
            'parent_id' => 'integer|not_in:'.$menuId,
            'active' => 'boolean'
        ];

        return $rules;
    }

    public function all()
    {
        $attributes = parent::all();

        if(empty($attributes['parent_id'])){
            $attributes['parent_id'] = null;
        }

        if(!isset($attributes['active'])){
            $attributes['active'] = 0;
        }

        $this->replace($attributes);

        return parent::all();
    }
}