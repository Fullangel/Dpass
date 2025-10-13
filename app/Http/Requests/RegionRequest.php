<?php

namespace App\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;

class RegionRequest extends FormRequest
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
     * @return array
     */
    public function rules()
    {
        $rules = [
            'name' => 'required|string|max:255|unique:regions,name',
        ];

        // If we're updating, we need to ignore the current region's name
        if ($this->method() === 'PUT' || $this->method() === 'PATCH') {
            $regionId = $this->route('region'); // Get the region ID from the route
            $rules['name'] = 'required|string|max:255|unique:regions,name,' . $regionId;
        }

        return $rules;
    }
}