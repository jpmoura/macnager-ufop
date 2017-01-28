<?php

namespace App\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;

class CreateRequisicaoRequest extends FormRequest
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
            'responsavel' => 'required|size:11',
            'responsavelNome' => 'required',
            'usuario' => 'required|size:14',
            'usuarioNome' => 'required',
            'tipousuario' => 'required|exists:tipo_usuario,id',
            'tipodispositivo' => 'required|exists:tipo_dispositivo,id',
            'mac' => 'required|size:17',
            'descricao' => 'required|max:100',
            'justificativa' => 'required|max:100',
            'termo' => 'required|mimetypes:application/pdf'
        ];
    }

    public function messages()
    {
        return [
            'usuario.required' => 'O campo Usuário é obrigatório.',
            'usuario.size' => 'O campo Usuário deve ter :size caracteres.',
            'tipousuario.required' => 'O campo Tipo Usuário é obrigatório',
            'tipousuario.exists' => 'O tipo de usuário selecionado é inválido',
            'tipodispositivo.required' => 'O campo Tipo Dispositivo é obrigarório.',
            'tipodispositivo.exists' => 'O tipo de dispositivo selecionado é invãlido.',
            'mac.required' => 'O campo Endereço MAC é obrigatório',
            'mac.size' => 'O endereço MAC deve conter exatamente :size caracteres',
            'descricao.required' => 'O campo descrição é obrigatório',
            'descricao.max' => 'A descrição deve conter no mãximo :max caracteres',
            'justificativa.required' => 'O campo Justificativa é obrigatório',
            'justificativa.max' => 'A justificativa deve conter no máximo :max caracteres',
            'termo.mimetypes' => 'O arquivo do termo de compromisso deve ser do tipo PDF.',
            'termo.uploaded' => 'Ocorreu uma falha durante o envio do termo de compromisso.',
        ];
    }
}