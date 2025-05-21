<?php

namespace App\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Rule;

class AccountCreationRequest extends FormRequest
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
            //"surname" => ['required', 'min:3', 'regex:/^[A-Z ]+$/'],
            "email" => 'required|email|unique:account_creations,email',
            "phone" => "required|unique:account_creations,phone",
            "surname" => ['required', 'min:3', 'regex:/^[A-Z ]+$/'],
            "firstname" => ['required', 'min:3', 'regex:/^[A-Z ]+$/'],
           // "surname" => ['required', 'min:3', 'regex:/^[A-Z ]+$/'],
            "nearest_bustop" => 'required',
            "lga" => 'required',
            "address" => 'required',
            "type_of_premise" => 'required',

            // Composite uniqueness
            'name_combination' => [
                Rule::unique('account_creations')->where(fn ($query) => $query
                    ->where('surname', $this->surname)
                    ->where('firstname', $this->firstname)
                    ->where('other_name', $this->other_name)
                ),
            ],
          
        ];
    }

    /**
     * Custom error messages.
     */
    public function messages(): array
    {
        return [
            'name_combination.unique' => 'A user with the same surname, firstname, and other name already exists.',
        ];
    }


    protected function prepareForValidation()
    {
        $this->merge([
            'surname' => ucwords(strtoupper(trim($this->surname))),
            'firstname' => ucwords(strtoupper(trim($this->firstname))),
            'other_name' => ucwords(strtoupper(trim($this->other_name))),
            'email' => strtolower(trim($this->email)),
            'nearest_bustop' => strtolower(trim($this->nearest_bustop)),
            'phone' => trim($this->phone),
        ]);
    }


    public function filters(){
        return [
            'email' => 'trim|escape|lowercase',
            'nearest_bustop' => 'trim|escape|lowercase',
            'surname' => 'trim|escape|lowercase',
            'firstname' => 'trim|escape|lowercase',
            'phone' => 'trim|escape'
        ];
    }
}
