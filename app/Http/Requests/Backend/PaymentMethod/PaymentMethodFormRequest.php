<?php

namespace Kommercio\Http\Requests\Backend\PaymentMethod;

use Kommercio\Http\Requests\Request;
use Kommercio\Models\PaymentMethod\PaymentMethod;
use Kommercio\PaymentMethods\PaymentMethodSettingFormInterface;

class PaymentMethodFormRequest extends Request
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
            'class' => 'required',
            'store_scope' => 'in:all,selected',
            'stores' => 'required_if:store_scope,selected',
            'stores.*' => 'nullable|exists:stores,id'
        ];

        if($this->route('id')){
            $paymentMethod = PaymentMethod::findOrFail($this->route('id'));

            if($paymentMethod->getProcessor() instanceof PaymentMethodSettingFormInterface){
                $additionalValidation = call_user_func(get_class($paymentMethod->getProcessor()).'::additionalSettingValidation', $this);

                if(is_array($additionalValidation)){
                    $rules += $additionalValidation;
                }
            }
        }

        return $rules;
    }
}