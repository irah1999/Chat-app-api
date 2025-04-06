<?php

namespace App\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;

class CreateRequest extends FormRequest
{
    /**
     * Determine if the user is authorized to make this request.
     */
    public function authorize(): bool
    {
        return true;
    }

    /**
     * Get the validation rules that apply to the request.
     *
     * @return array<string, \Illuminate\Contracts\Validation\ValidationRule|array<mixed>|string>
     */
    public function rules(): array
    {
        return [
            'name' => 'required|min:3|max:255',
            'user' => 'required|array|min:2',
            'user.*' => 'required|exists:users,id'
        ];
    }

    public function messages()
    {
        return [
            'name.required' => 'Group name is required.',
            'name.min' => 'Group name must be at least :min characters.',
            'user.required' => 'Please select users.',
            'user.min' => 'Please select at least :min users.',
            'user.*.exists' => 'One or more selected users do not exist.',
        ];
    }
}
