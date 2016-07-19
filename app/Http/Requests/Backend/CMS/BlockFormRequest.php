<?php

namespace Kommercio\Http\Requests\Backend\CMS;

use Kommercio\Http\Requests\Request;

class BlockFormRequest extends Request
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
        $blockId = $this->route('id');

        $rules = [
            'machine_name' => 'required|unique:blocks,machine_name'.($blockId?','.$blockId:''),
            'name' => 'required',
            'body' => 'required',
        ];

        return $rules;
    }

    public function all()
    {
        $attributes = parent::all();

        if(!isset($attributes['active'])){
            $attributes['active'] = 0;
        }

        $this->replace($attributes);

        return parent::all();
    }
}