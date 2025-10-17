<?php

namespace App\Http\Requests\Venue;

use Illuminate\Foundation\Http\FormRequest;

class VenueRequest extends FormRequest
{
    public function authorize()
    {
        return true;
    }

    public function rules()
    {
        return [
            'title'               => 'required|string|max:200',
            'capacity'            => 'required|integer|min:1',
            'location'            => 'required|string|max:255',
            'latitude'            => 'nullable|numeric|between:-90,90',
            'longitude'           => 'nullable|numeric|between:-180,180',
            'service_start_time'  => 'nullable',
            'service_end_time'    => 'nullable|after_or_equal:service_start_time',
            'ticket_price'        => 'nullable|numeric|min:0',
            'phone'               => 'nullable|string|max:20',
            'email'               => 'nullable|email|max:50',

            // Nested detail
            'description'         => 'nullable|string',
            'features'            => 'nullable',
            'features.*'          => 'string|max:1000',

            // Media—uploading URLs for simplicity
            'images.*'   => 'nullable|image|mimes:jpg,jpeg,png,gif|max:5120',
            'videos.*'    => 'nullable|mimes:mp4,mov,avi|max:51200',
        ];
    }


    protected function prepareForValidation()
    {
        // Ensure empty strings become null
        $this->merge([
            'latitude' => $this->latitude ?: null,
            'longitude' => $this->longitude ?: null,
            'ticket_price' => $this->ticket_price ?: null,
        ]);
    }
}
