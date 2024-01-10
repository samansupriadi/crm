<?php

namespace App\Http\Requests;

use Illuminate\Validation\Rule;
use Illuminate\Validation\Rules\Password;
use Illuminate\Foundation\Http\FormRequest;

class UpdateUserRequest extends FormRequest
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
     * @return array<string, \Illuminate\Contracts\Validation\ValidationRule|array|string>
     */
    public function rules(): array
    {
        return [
            'fullName'      => ['required', 'string', 'max:255'],
            'email'         => ['required', 'string', 'max:255', Rule::unique('users','email')->ignore($this->user)],
            'password'      => ['required', 'confirmed', Password::defaults()],
            'telp'          => ['required', 'string', 'max:20'],
        ];
    }
}
