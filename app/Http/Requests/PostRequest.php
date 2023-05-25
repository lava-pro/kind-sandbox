<?php

namespace App\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Contracts\Validation\Validator;
use Illuminate\Http\Exceptions\HttpResponseException;

class PostRequest extends FormRequest
{
    /**
     * Get the validation rules that apply to the request.
     * @return array
     */
    public function rules()
    {
        $rules = [
            'translations'             => 'required',
            'translations.title'       => 'required|string|min:3',
            'translations.description' => 'required|string|min:5',
            'translations.content'     => 'required|string|min:6',
            'tags'                     => 'nullable|array',
            'tags.*.id'                => 'required|exists:tags,id',
        ];

        return $rules;
    }

    /**
     * Return exception as json
     * @return Exception
     */
    protected function failedValidation(Validator $validator)
    {
        throw new HttpResponseException(response()->json($validator->errors(), 422));
    }

}
