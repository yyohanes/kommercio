<?php

namespace Kommercio\Http\Requests\Backend\User;

use Kommercio\Http\Requests\Request;
use Kommercio\Models\Role\Role;

class RoleFormRequest extends Request
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
            'permissions' => 'required|array',
        ];

        return $rules;
    }
}