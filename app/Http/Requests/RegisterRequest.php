<?php

namespace App\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Rules\Password;

class RegisterRequest extends FormRequest
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
            "name" => 'required',
            "email" => 'required|email|unique:users,email',
            "phone" => "required|numeric|unique:users,phone",
           // "pin" => "required|numeric",
             'password' => [
                 'required',
                  Password::min(5) // Minimum length of 8 characters
                      ->letters() // Must contain at least one letter
                      ->mixedCase() // Must contain both uppercase and lowercase letters
                       ->numbers() // Must contain at least one number
            //     //     ->symbols() // Must contain at least one special character
                    //  ->uncompromised() // Check if the password has not been compromised in data breaches
             ],
        ];
    }

    public function filters(){
        return [
            'email' => 'trim|escape|lowercase',
            'phone' => 'trim|escape'
        ];
    }
}
