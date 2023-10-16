<?php

namespace App\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;

class ProductGetRequest extends FormRequest
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
     * @return array<string, mixed>
     */
    public function rules()
    {
        return [
            'q' => 'nullable|string|min:1',
            'page.number' => 'nullable|integer|min:1',
            'page.size' => 'nullable|integer|between:1,100',
            'search' => 'nullable|string|min:3|max:70', 
        ];
    }
}
