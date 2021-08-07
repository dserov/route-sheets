<?php

namespace App\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;

class DetailPhotoRequest extends FormRequest
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
            'images' => 'required',
            'images.*' => 'image|mimes:jpg,jpeg,png,svg,gif|max:8192',
        ];
    }
}
