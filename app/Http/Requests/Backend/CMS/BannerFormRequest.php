<?php

namespace Kommercio\Http\Requests\Backend\CMS;

use Kommercio\Http\Requests\Request;

class BannerFormRequest extends Request
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
        $rules = [
            'name' => 'required',
            'images' => 'required',
            'banner_group_id' => 'required|integer',
        ];

        return $rules;
    }

    public function all($keys = null)
    {
        $attributes = parent::all($keys);

        if(!isset($attributes['active'])){
            $attributes['active'] = 0;
        }

        $this->replace($attributes);

        return parent::all($keys);
    }
}
