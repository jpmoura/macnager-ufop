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
  public function getAddDeviceType()
  {
    if(UserController::checkLogin()) {
      return View::make('admin.actions.addDeviceType');
    }
    else return redirect('/login');
  }

  public function doAddDeviceType()
  {
    if(UserController::checkLogin()) {
      $newDeviceType = new TipoDispositivo;
      $newDeviceType->descricao = Input::get('descricao');
      $newDeviceType->save();

      Session::flash('tipo', 'Sucesso');
      Session::flash('mensagem', 'Novo tipo de dispostivo adicionado.');

      return Redirect::route('listDeviceType');
    }
    else return redirect('/login');
  }

  public function listDeviceType()
  {
    if(UserController::checkLogin()) {
      return View::make('admin.actions.listDeviceType')->with('tipos', TipoDispositivo::all());
    }
    else return redirect('/login');
  }

  public function getEditDeviceType($id)
  {
    if(UserController::checkLogin()) {
      return View::make('admin.actions.editDeviceType')->with('tipo', TipoDispositivo::find($id));
    }
    else return redirect('/login');
  }

  public function doEditDeviceType()
  {
    if(UserController::checkLogin()) {
      $toEdit = TipoDispositivo::find(Input::get('id'));
      $toEdit->descricao = Input::get('descricao');
      $toEdit->save();

      Session::flash('tipo', 'Sucesso');
      Session::flash('mensagem', 'O tipo foi editado.');

      return Redirect::route('listDeviceType');
    }
    else return redirect('/login');
  }

  public function deleteDeviceType($id)
  {
    if(UserController::checkLogin()) {

      TipoDispositivo::destroy($id);

      Session::flash('tipo', 'Sucesso');
      Session::flash('mensagem', 'O tipo foi excluído.');

      return Redirect::route('listDeviceType');
    }
    else return redirect('/login');
  }
}
