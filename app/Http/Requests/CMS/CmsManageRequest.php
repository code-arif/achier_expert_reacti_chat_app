<?php

namespace App\Http\Requests\CMS;

use Illuminate\Foundation\Http\FormRequest;

class CmsManageRequest extends FormRequest
{

    public function authorize(): bool
    {
        return true;
    }


    public function rules(): array
    {
        return [
            'page'             => 'nullable|string|max:255',
            'section'          => 'nullable|string|max:255',
            'name'             => 'nullable|string|max:255',
            'slug'             => 'nullable|string|max:255',
            'title'            => 'nullable|string|max:255',
            'sub_title'        => 'nullable|string|max:255',
            'description'      => 'nullable|string',
            'sub_description'  => 'nullable|string',
            'bg'               => 'nullable|string',
            'image'            => 'nullable|image|max:2048',
            'btn_text'         => 'nullable|string|max:255',
            'btn_link'         => 'nullable|url|max:255',
            'btn_color'        => 'nullable|string|max:50',
            'metadata'         => 'nullable|array',
            'status'           => 'nullable|in:active,inactive',
        ];
    }
}
