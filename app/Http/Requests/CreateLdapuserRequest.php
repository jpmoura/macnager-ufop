<?php

namespace App\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;

class CreateLdapuserRequest extends FormRequest
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
        return [
            'cpf' => 'required|size:11|unique:ldapusers',
            'nivel' => 'required|in:1,2',
            'nome' => 'required|max:100',
            'email' => 'required|email|max:100',
        ];
    }
}
