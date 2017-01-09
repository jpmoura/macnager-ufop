<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;

use App\Http\Requests;
use Illuminate\Support\Facades\Redirect;
use Input;
use Session;
use View;
use App\TipoUsuario;

class TipoUsuarioController extends Controller
{

    /**
     * Renderiza a view com o formulário de adição.
     */
    public function showAdd()
    {
        return View::make('tipousuario.add');
    }

    /**
     * Adiciona uma nova instância ao banco de dados.
     */
    public function add()
    {
        $newUserType = new TipoUsuario;
        $newUserType->descricao = Input::get('descricao');
        $newUserType->save();

        Session::flash('tipo', 'Sucesso');
        Session::flash('mensagem', 'Novo tipo de usuário adicionado.');

        return Redirect::route('listUserType');
    }

    /**
     * Renderiza aview com todos os tipos cadastrados.
     */
    public function show()
    {
        return View::make('tipousuario.show')->with('tipos', TipoUsuario::all());
    }

    /**
     * Renderiza a view com o formulário de edição.
     * @param $id ID da instância a ser editada
     * @return Redirect
     */
    public function showEdit($id)
    {
        return View::make('tipousuario.edit')->with('tipo', TipoUsuario::find($id));
    }

    /**
     * Edita dos dados de uma instância.
     */
    public function edit()
    {
        $toEdit = TipoUsuario::find(Input::get('id'));
        $toEdit->descricao = Input::get('descricao');
        $toEdit->save();

        Session::flash('tipo', 'Sucesso');
        Session::flash('mensagem', 'O tipo foi editado.');

        return Redirect::back();
    }

    /**
     * Deleta uma instância do banco de dados.
     * @param $id ID da instância a ser deletada
     */
    public function deleteUserType($id)
    {
        TipoUsuario::destroy($id);

        Session::flash('tipo', 'Sucesso');
        Session::flash('mensagem', 'O tipo foi excluído.');

        return Redirect::route('listUserType');
    }
}
