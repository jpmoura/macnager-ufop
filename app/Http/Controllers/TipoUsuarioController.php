<?php

namespace App\Http\Controllers;

use App\Http\Requests;
use App\TipoUsuario;

class TipoUsuarioController extends Controller
{

    /**
     * Renderiza aview para criação de um novo tipo de usuário
     * @return \Illuminate\Contracts\View\Factory|\Illuminate\View\View View que contém o formulário de criação para o modelo TipoUsuario
     */
    public function create()
    {
        return view('tipousuario.create');
    }

    /**
     * Armazena uma nova instância do TipoUsuario no banco de dados
     * @param Requests\CreateTipoUsuarioRequest $request Requisição com os campos validados
     * @return \Illuminate\Http\RedirectResponse View qe contém todos os tipos cadastrados.
     */
    public function store(Requests\CreateTipoUsuarioRequest $request)
    {
        $newUserType = TipoUsuario::create(['descricao' => $request->input('descricao')]);

        if($newUserType)
        {
            session()->flash('tipo', 'success');
            session()->flash('mensagem', 'Novo tipo de usuário adicionado.');
        }
        else
        {
            session()->flash('tipo', 'error');
            session()->flash('mensagem', 'Ocorreu um erro durante a adição do novo tipo de usuário.');
        }

        return redirect()->route('indexTipoUsuario');
    }

    /**
     * View com os tipos cadastrados e opções de edição e deleção para cada um.
     * @return mixed View contendo todos os tipos já cadastrados.
     */
    public function index()
    {
        return view('tipousuario.index')->with('tipos', TipoUsuario::all());
    }

    /**
     * Renderiza view contendo os valores atuais do tipo do usuário.
     * @param TipoUsuario $userType Instância a ser editada
     * @return mixed View contendo o formulário de edição dos detalhes da instãncia
     */
    public function edit(TipoUsuario $userType)
    {
        return view('tipousuario.edit')->with('tipo', $userType);
    }

    /**
     * Atualiza os valores de uma instância TipoUsuario
     * @param Requests\EditTipoUsuarioRequest $request Requisição com os campos validados
     * @return \Illuminate\Http\RedirectResponse Página anterior com os valores atualizados da instância
     */
    public function update(Requests\EditTipoUsuarioRequest $request)
    {
        $toEdit = TipoUsuario::find($request->input('id'));
        $toEdit->descricao = $request->input('descricao');
        $toEdit->save();

        session()->flash('tipo', 'success');
        session()->flash('mensagem', 'O tipo foi editado.');

        return back();
    }

    /**
     * Remove uma intância de TipoUsuario do banco de dados
     * @param TipoUsuario $userType Instância a ser removida
     * @return \Illuminate\Http\RedirectResponse View com o índice de todos os tipos existentes
     */
    public function delete(TipoUsuario $userType)
    {
        try
        {
            $userType->delete();
            session()->flash('tipo', 'success');
            session()->flash('mensagem', 'O tipo foi excluído.');
        }
        catch (\Exception $e)
        {
            session()->flash('tipo', 'error');
            session()->flash('mensagem', 'O tipo de usuário não foi excluído. Motivo: ' . $e->getMessage());
        }

        return redirect()->route('indexTipoUsuario');
    }
}
