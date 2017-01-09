<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;

use App\Http\Requests;
use Illuminate\Support\Facades\Redirect;
use Input;
use Session;
use View;
use App\TipoDispositivo;

class TipoDispositivoController extends Controller
{
    /**
     * Renderiza a view com o formulário de adição.
     */
    public function showAdd()
    {
        return View::make('tipodispositivo.add');
    }

    /**
     * Adiciona uma nova instância ao banco de dados
     */
    public function add()
    {
        $newDeviceType = new TipoDispositivo;
        $newDeviceType->descricao = Input::get('descricao');
        $newDeviceType->save();

        Session::flash('tipo', 'Sucesso');
        Session::flash('mensagem', 'Novo tipo de dispostivo adicionado.');

        return Redirect::route('listDeviceType');
    }

    /**
     *  Renderiza a view com a lista dos tipos.
     */
    public function show()
    {
        return View::make('tipodispositivo.show')->with('tipos', TipoDispositivo::all());
    }

    /**
     * Renderiza a view com o formulário de edição de um novo tipo.
     * @param $id ID do tipo do dispositivo
     */
    public function showEdit($id)
    {
        return View::make('tipodispositivo.edit')->with('tipo', TipoDispositivo::find($id));
    }

    /**
     * Edita as informações de uma instância.
     */
    public function edit()
    {
        $toEdit = TipoDispositivo::find(Input::get('id'));
        $toEdit->descricao = Input::get('descricao');
        $toEdit->save();

        Session::flash('tipo', 'Sucesso');
        Session::flash('mensagem', 'O tipo foi editado.');

        return Redirect::back();
    }

    /**
     * Deleta uma instância.
     * @param $id ID da instância
     */
    public function delete($id)
    {
        TipoDispositivo::destroy($id);

        Session::flash('tipo', 'Sucesso');
        Session::flash('mensagem', 'O tipo foi excluído.');

        return Redirect::route('listDeviceType');
    }
}
