<?php

namespace App\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;

class EditTipoDispositivoRequest extends FormRequest
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
            'id' => 'required|exists:tipo_dispositivo',
            'descricao' => 'required|max:50|unique:tipo_dispositivo,descricao',
        ];
    }

    public function messages()
    {
        return [
            'descricao.required' => 'O campo Descrição é obrigatório',
            'descricao.max' => 'A descrição pode conter no máximo :max caracteres',
            'descricao.unique' => 'Essa descrição já está sendo usada',
        ];
    }
}
