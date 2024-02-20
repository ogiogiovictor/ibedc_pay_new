<?php

namespace App\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;

class VendingRequest extends FormRequest
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
            'meterNo' => "required",
            "amount" => ["required", "numeric"],
            "account_type" => "required",
            "owner" => "required"
        ];
    }

    public function filters(){
        return [
            'meterNo' => 'trim|escape'
        ];
    }
}
