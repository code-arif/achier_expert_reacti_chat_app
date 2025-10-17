<?php

namespace App\Http\Requests\Calender;

use Illuminate\Foundation\Http\FormRequest;

class CalenderRequest extends FormRequest
{
    /**
     * Determine if the user is authorized to make this request.
     */
    public function authorize(): bool
    {
        return true;
    }


    public function rules(): array
    {
        return [
            'event_id'    => 'nullable|exists:events,id',
            'title'       => 'required|string|max:255',
            'description' => 'nullable|string',
            'all_day'     => 'boolean',
            'start_date'  => 'required|date',
            'end_date'    => 'nullable|date|after_or_equal:start_date',
            'color_code'  => 'nullable|string|max:20',
        ];
    }
}
