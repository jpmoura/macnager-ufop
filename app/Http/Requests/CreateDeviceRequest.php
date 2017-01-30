<?php

namespace App\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;

class CreateDeviceRequest extends FormRequest
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
            'ip'              => 'required|ip',
            'responsavel'     => 'required|size:14',
            'responsavelNome' => 'required',
            'usuario'         => 'required|size:14',
            'usuarioNome'     => 'required',
            'tipousuario'     => 'required|exists:tipo_usuario,id',
            'tipodispositivo' => 'required|exists:tipo_dispositivo,id',
            'mac'             => 'required|size:17',
            'descricao'       => 'required|max:100',
            'justificativa'   => 'required|max:100',
            'validade'        => 'nullable|date_format:d/m/Y|after:today',
        ];
    }

    public function messages()
    {
        return [
            'ip.required' => 'O campo IP é obrigatório.',
            'ip.ip' => 'O campo IP precisa conter um endereço válido de IP.',
            'usuario.required' => 'O campo Usuário é obrigatório.',
            'usuario.size' => 'O campo Usuário precisa ter :size caracteres.',
            'tipousuario.required' => 'O campo Tipo Usuário é obrigatório.',
            'tipousuário.exists' => 'O valor selecionado para o campo Tipo Usuário não é valido.',
            'tipodispositivo.required' => 'O campo Tipo Dispositivo é obrigatório',
            'tipodispositivo.exists' => 'O valor selecionado para o campo Tipo Dispositivo e inválido.',
            'mac.required' => 'O campo Endereço MAC é obrigatório.',
            'mac.size' => 'O campo Endereço MAC necessita ter exatamente :size caracteres.',
            'descricao.required' => 'O campo Descrição é obrigatório.',
            'descricao.max' => 'O campo Descrição pode conter no máximo :max caracteres.',
            'justificativa.required' => 'O campo Justificativa é obrigatório.',
            'justificativa.max' => 'O campo Justificativa pode conter no máximo :max caracteres.',
            'validade.date_format' => 'O campo Validade precisa estar no formato DD/MM/AAAA',
            'validade.after' => 'A data de validade precisa ser posterior ao dia de hoje.',
        ];
    }
}
