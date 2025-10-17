<?php

namespace App\Http\Requests\Profile;

use App\Models\User;
use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Rule;

class ProfileUpdateRequest extends FormRequest
{
    public function authorize(): bool
    {
        return true;
    }

    public function rules(): array
    {
        return [
            'first_name' => ['sometimes', 'string', 'max:50'],
            'last_name' => ['nullable', 'string', 'max:50'],
            'profession' => ['nullable', 'string', 'max:255'],
            'gender' => ['nullable', 'string', 'max:50'],
            'country' => ['nullable', 'string', 'max:100'],
            'age' => ['nullable', 'string', 'max:50'],
            'address' => ['nullable', 'string'],
            'avatar' => ['nullable', 'image', 'mimes:jpeg,png,jpg,gif', 'max:2048'],
        ];
    }

    public function messages(): array
    {
        return [
            'avatar.max' => 'The avatar must not be greater than 2MB.',
            'avatar.mimes' => 'The avatar must be a file of type: jpeg, png, jpg, gif.',
        ];
    }
}
