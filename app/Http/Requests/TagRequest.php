<?php

namespace App\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Contracts\Validation\Validator;
use Illuminate\Http\Exceptions\HttpResponseException;

class TagRequest extends FormRequest
{
    /**
     * Get the validation rules that apply to the request.
     * @return array
     */
    public function rules()
    {
        $exclude = isset($this->id) ? ",{$this->id}" : '';

        $rules = [
            'name'  => 'required|string|min:3|max:32|unique:tags,name'. $exclude,
            'title' => 'required|string|min:3|max:64',
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
