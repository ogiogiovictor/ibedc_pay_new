<?php

namespace App\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;

class UploadRequest extends FormRequest
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
           // "means_of_identification" => "required",
           // "tracking_id" => "required",
           // "identification" => "required|image|mimes:jpg,jpeg,png|max:4096",
           // "photo" => "required|image|mimes:jpg,jpeg,png|max:4096",
           // "no_of_account_apply_for" =>  "required"
        ];
    }
}
