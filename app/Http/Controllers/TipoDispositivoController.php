<?php

namespace App\Http\Controllers;

use App\Http\Requests;
use Input;
use App\TipoDispositivo;

class TipoDispositivoController extends Controller
{
    /**
     * Renderiza a view com o formulário de criação
     * @return \Illuminate\Contracts\View\Factory|\Illuminate\View\View View contendo o formulário de criação
     */
    public function create()
    {
        return view('tipodispositivo.create');
    }

    /**
     * Armazena uma instância de TipoDispositivo no banco de dados.
     * @param Requests\CreateTipoDispositivoRequest $request Requisição com os campos validados
     * @return \Illuminate\Http\RedirectResponse View de índece dos tipos de dispositivos.
     */
    public function store(Requests\CreateTipoDispositivoRequest $request)
    {
        $newDeviceType = TipoDispositivo::create(['descricao' => $request->input('descricao')]);

        if($newDeviceType)
        {
            session()->flash('tipo', 'success');
            session()->flash('mensagem', 'Novo tipo de dispostivo adicionado.');
        }
        else
        {
            session()->flash('tipo', 'error');
            session()->flash('mensagem', 'Um erro aconteceu durante a inserção no banco de dados.');
        }

        return redirect()->route('indexTipoDispositivo');
    }

    /**
     * Mostra todos os tipos de dispositivos cadastrados em uma tabela com opções de edição e deleção
     * @return mixed View contendo todos os tipos de dispositivos cadastrados
     */
    public function index()
    {
        return view('tipodispositivo.index')->with('tipos', TipoDispositivo::all());
    }

    /**
     * Mostra a view de edição de uma instâcia de tipo de dispositivo.
     * @param TipoDispositivo $deviceType Instância do TipoDispositivo
     * @return mixed View de edição com os dados atuais do tipo de dispositivo.
     */
    public function edit(TipoDispositivo $deviceType)
    {
        return view('tipodispositivo.edit')->with('tipo', $deviceType);
    }

    /**
     * Atualiza os valores de uma instância do TipoDispositivo.
     * @param Requests\EditTipoDispositivoRequest $request Requisição com os campos validados
     * @return \Illuminate\Http\RedirectResponse Página anterior com os calores atualizados
     */
    public function update(Requests\EditTipoDispositivoRequest $request)
    {
        $toEdit = TipoDispositivo::find($request->input('id'));
        $toEdit->descricao = $request->input('descricao');
        $toEdit->save();

        session()->flash('tipo', 'success');
        session()->flash('mensagem', 'O tipo foi editado.');

        return back();
    }

    /**
     * Deleta uma instância.
     * @param $id ID da instância
     */
    public function delete(TipoDispositivo $deviceType)
    {
        try
        {
            $deviceType->delete();
            session()->flash('tipo', 'success');
            session()->flash('mensagem', 'O tipo foi excluído.');
        }
        catch (\Exception $e)
        {
            session()->flash('tipo', 'error');
            session()->flash('mensagem', "Não foi possível excluir o tipo de dispositivo. Motivo: " . $e->getMessage());
        }

        return redirect()->route('indexTipoDispositivo');
    }
}
