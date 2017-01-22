<?php

namespace App\Http\Controllers;

use App\Subrede;
use App\TipoSubrede;
use Illuminate\Http\Request;

use App\Http\Requests;
use Illuminate\Support\Facades\Input;

class SubredeController extends Controller
{
    /**
     * Mostra a lsita de subredes cadastradas
     */
    public function index()
    {
        $subnetworks = Subrede::with('tipo')->get();
        return view('subrede.index')->with(['subredes' => $subnetworks]);
    }

    /**
     * Mostra a página para criação de uma nova subrede.
     */
    public function create()
    {
        return view('subrede.create')->with(['tipos' => TipoSubrede::all()]);
    }

    /**
     * Armazena uma nova instância de subrede.
     */
    public function store(Requests\NewSubredeRequest $request)
    {
        $form = Input::all();

        $newSubrede = new Subrede;

        $newSubrede->endereco = $form['endereco'];
        $newSubrede->cidr = $form['cidr'];
        $newSubrede->descricao = $form['descricao'];
        $newSubrede->tipo_subrede_id = $form['tipo'];

        if(isset($form['gateway'])) $newSubrede->ignorar_gateway = 1;
        else $newSubrede->ignorar_gateway = 0;

        if(isset($form['broadcast'])) $newSubrede->ignorar_broadcast = 1;
        else $newSubrede->ignorar_broadcast = 0;

        $newSubrede->save();

        session()->flash('tipo', 'Sucesso');
        session()->flash('mensagem', 'Nova subrede adicionada ao banco de dados.');

        return redirect()->route('indexSubrede');
    }


    /**
     * Mostra a página de edição de uma subrede.
     */
    public function edit(Subrede $subrede)
    {
        return view('subrede.edit')->with(['subrede' => $subrede, 'tipos' => TipoSubrede::all()]);
    }

    /**
     * Update the specified resource in storage.
     */
    public function update(Request $request)
    {
        $form = Input::all();
        $subnet = Subrede::find($form['id']);

        $subnet->endereco = $form['endereco'];
        $subnet->cidr = $form['cidr'];
        $subnet->descricao = $form['descricao'];
        $subnet->tipo_subrede_id = $form['tipo'];

        if(isset($form['gateway'])) $subnet->ignorar_gateway = 1;
        else $subnet->ignorar_gateway = 0;

        if(isset($form['broadcast'])) $subnet->ignorar_broadcast = 1;
        else $subnet->ignorar_broadcast = 0;

        $subnet->save();

        session()->flash('tipo', 'Sucesso');
        session()->flash('mensagem', 'A subrede foi editada.');

        return redirect()->back();
    }

    /**
     * Remove uma instância de subrede.
     */
    public function destroy(Subrede $subrede)
    {
        try
        {
            $subrede->delete();
            session()->flash('tipo', 'Sucesso');
            session()->flash('mensagem', 'A Subrede foi removida com sucesso');
        }
        catch (\Exception $e)
        {
            session()->flash('tipo', 'Erro');
            session()->flash('mensagem', 'Ocorreu um erro e a subrede não pôde ser removida.');
        }

        return redirect()->route('indexSubrede');
    }
}
