<?php

namespace App\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;

class EditSubredeRequest extends FormRequest
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
            'endereco' => 'required|ip|min:7|max:15',
            'cidr' => 'required|between:0,32',
            'tipo' => 'required|exists:tipo_subrede,id',
            'descricao' => 'required|max:75',
        ];
    }

    public function messages()
    {
        return [
            'endereco.required' => 'O campo endereço é obrigatório.',
            'endereco.ip' => 'O endereço da subrede precisa ser um endereço IP válido.',
            'descricao.required' => 'O campo descrição é obrigatório.',
            'descricao.max' => 'A descrição deve conter no máximo 75 caracteres',
        ];
    }
}
