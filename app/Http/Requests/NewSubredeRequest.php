<?php

namespace App\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;

class NewSubredeRequest extends FormRequest
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
            'endereco' => 'required|min:7|max:15|unique:subredes',
            'cidr' => 'required|min:0|max:32',
            'tipo' => 'required',
            'descricao' => 'required|max:75',
        ];
    }

    public function messages()
    {
        return [
            'endereco.unique' => 'O endereço da subrede já está sendo usado.',
        ];
    }
}
