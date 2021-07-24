<?php

namespace App\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Support\Facades\Hash;

class SaveUserRequest extends FormRequest
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
        return [
            'name' => 'required|min:3',
            'email' => 'required|email',
            'password' => 'required_without:id|confirmed', // должен быть заполнен, если пустой ИД, т.е. новый юзер
            'current_password' => [
                'required',
                // проверка текущего пароля ;)
                function ($attribute, $value, $fail) {
                    if (! Hash::check($value, \Auth::user()->password)) {
                        $fail( __('Current password is invalid') );
                    }
                },
            ],
        ];
    }

    public function attributes()
    {
        return [
            'id' => __('User id empty'),
            'name' => __('User name'),
            'email' => __('User email'),
            'password' => __('User password'),
            'password_confirmation' => __('User password confirmation'),
            'current_password' => __('User current password'),
            'is_admin' => __('User is admin'),
            'is_driver' => __('User is driver'),
            'is_logistic' => __('User is logistic'),
        ];
    }
}
