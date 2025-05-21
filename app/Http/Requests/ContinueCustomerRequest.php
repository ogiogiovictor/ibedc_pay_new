<?php

namespace App\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;

class ContinueCustomerRequest extends FormRequest
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
            "tracking_id" => 'required',
            "longitude" => "required",
            "latitude" => "required",
            "landloard_picture" => 'nullable|image|mimes:jpg,jpeg,png|max:4096',
         ];
    }
}
