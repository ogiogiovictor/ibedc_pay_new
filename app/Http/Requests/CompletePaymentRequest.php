<?php

namespace App\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;

class CompletePaymentRequest extends FormRequest
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
            "transacion_id" => 'required',
           // "resp" => "required",
            "phone" => "required",
            "amount" => "required",
            "provider" => "required",
            "account_id" => "required", // Could be meter no or account no
            "account_type" => "required",
            "payRef" => "required"
        ];
    }
}
