<?php

namespace App\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;

class AgencyRequest extends FormRequest
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
            "agent_name" => 'required',
            "agent_email" => 'required|email|unique:agency,agent_email',
            "agent_official_phone" => 'required',
            "no_of_agents" => 'required',
            "authourity" =>  "required",
        ];
    }
}
