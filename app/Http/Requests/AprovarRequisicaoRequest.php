<?php

namespace App\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;

class AprovarRequisicaoRequest extends FormRequest
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
            'id'       => 'required|exists:requisicoes',
            'subrede'  => 'required|exists:subredes,id',
            'ip'       => 'required|ip',
            'validade' => 'nullable|date_format:d/m/Y|after:today',
        ];
    }

    public function messages()
    {
        return [
            'ip.required' => 'O endereço IP é obrigatório.',
            'ip.ip' => 'O endereçc IP precisa ser um endereço válido.',
            'validade.date_format' => 'A data precisa estar no formato DD/MM/AAAA.',
            'validade.after' => 'A data de validade não pode ser anterior a data de hoje.'
        ];
    }
}
