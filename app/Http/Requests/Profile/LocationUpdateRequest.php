<?php

namespace App\Http\Requests\Profile;

use Illuminate\Foundation\Http\FormRequest;

class LocationUpdateRequest extends FormRequest
{
    public function authorize(): bool
    {
        return true;
    }

    public function rules(): array
    {
        return [
            'address' => ['nullable', 'string'],
            'city' => ['nullable', 'string', 'max:100'],
            'state' => ['nullable', 'string', 'max:100'],
            'latitude' => ['nullable', 'string'], 
            'longitude' => ['nullable', 'string'],
        ];
    }

    public function messages(): array
    {
        return [
            'latitude.string' => 'Invalid latitude format.',
            'longitude.string' => 'Invalid longitude format.',
        ];
    }
}
