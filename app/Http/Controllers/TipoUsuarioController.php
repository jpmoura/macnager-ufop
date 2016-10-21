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

    public function getAddUserType()
    {
      if(UserController::checkLogin()) {
        return View::make('admin.actions.addUserType');
      }
      else return redirect('/login');
    }

    public function doAddUserType()
    {
      if(UserController::checkLogin()) {
        $newUserType = new TipoUsuario;
        $newUserType->descricao = Input::get('descricao');
        $newUserType->save();

        Session::flash('tipo', 'Sucesso');
        Session::flash('mensagem', 'Novo tipo de usuário adicionado.');

        return Redirect::route('listUserType');
      }
      else return redirect('/login');
    }

    public function listUserType()
    {
      if(UserController::checkLogin()) {
        return View::make('admin.actions.listUserType')->with('tipos', TipoUsuario::all());
      }
      else return redirect('/login');
    }

    public function getEditUserType($id)
    {
      if(UserController::checkLogin()) {
        return View::make('admin.actions.editUserType')->with('tipo', TipoUsuario::find($id));
      }
      else return redirect('/login');
    }

    public function doEditUserType()
    {
      if(UserController::checkLogin()) {
        $toEdit = TipoUsuario::find(Input::get('id'));
        $toEdit->descricao = Input::get('descricao');
        $toEdit->save();

        Session::flash('tipo', 'Sucesso');
        Session::flash('mensagem', 'O tipo foi editado.');

        return Redirect::route('listUserType');
      }
      else return redirect('/login');
    }

    public function deleteUserType($id)
    {
      if(UserController::checkLogin()) {

        TipoUsuario::destroy($id);

        Session::flash('tipo', 'Sucesso');
        Session::flash('mensagem', 'O tipo foi excluído.');

        return Redirect::route('listUserType');
      }
      else return redirect('/login');
    }
}
