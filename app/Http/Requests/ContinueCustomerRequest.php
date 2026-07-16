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
    public function messages(): array
    {
        return [
            'nin_number.required'       => 'NIN number is required.',
            'nin_number.digits_between' => 'NIN number must be exactly 11 digits.',
            'nin_number.regex'          => 'NIN number must contain numbers only — no letters or special characters.',
            'nin_number.unique'         => 'This NIN number has already been used.',
        ];
    }

    public function rules(): array
    {
         return [
            "nin_number" => ['required', 'digits_between:11,11', 'regex:/^\d{11}$/', 'unique:continue_account_creations,nin_number'],
            "tracking_id" => 'required',
             "no_of_account_apply_for" =>  "required",
             "landlord_surname" => ['required', 'min:3', 'regex:/^[a-zA-Z- ]+$/'],
             "landlord_othernames" => ['required', 'min:3', 'regex:/^[a-zA-Z- ]+$/'],
            //"landlord_telephone" => 'required|regex:/^0\d{10}$/',
            //"longitude" => "required",
            //"latitude" => "required",
           // "landloard_picture" => 'nullable|image|mimes:jpg,jpeg,png|max:4096',
            "landloard_picture" => 'required|image|mimes:jpg,jpeg,png|max:4096',
         ];
    }
}
