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
}
