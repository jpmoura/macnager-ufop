<?php

namespace App\Http\Controllers;

use App\Requisicao;
use App\Subrede;
use App\TipoSubrede;
use Illuminate\Http\Request;
use App\Http\Requests\CreateSubredeRequest;
use Illuminate\Http\Response;
use Illuminate\Support\Facades\Input;

class SubredeController extends Controller
{
    /**
     * Mostra a lista de subredes cadastradas
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
    public function store(CreateSubredeRequest $request)
    {
        $form = $request->all();

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

        session()->flash('tipo', 'success');
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
        $form = $request->all();
        $subnet = Subrede::find($form['id']);

        $count = Subrede::where('endereco', $form['endereco'])->count();
        if($count < 2)
        {
            $subnet->endereco = $form['endereco'];
            $subnet->cidr = $form['cidr'];
            $subnet->descricao = $form['descricao'];
            $subnet->tipo_subrede_id = $form['tipo'];

            if(isset($form['gateway'])) $subnet->ignorar_gateway = 1;
            else $subnet->ignorar_gateway = 0;

            if(isset($form['broadcast'])) $subnet->ignorar_broadcast = 1;
            else $subnet->ignorar_broadcast = 0;

            $subnet->save();

            session()->flash('tipo', 'success');
            session()->flash('mensagem', 'A subrede foi editada.');

            return back();
        }
        else return back()->withErrors(['endereco' => 'Esse endereço já está sendo usado por outra subrede.']);
    }

    /**
     * Remove uma instância de subrede.
     */
    public function destroy(Subrede $subrede)
    {
        try
        {
            $subrede->delete();
            session()->flash('tipo', 'success');
            session()->flash('mensagem', 'A Subrede foi removida com sucesso');
        }
        catch (\Exception $e)
        {
            session()->flash('tipo', 'error');
            session()->flash('mensagem', 'Ocorreu um erro e a subrede não pôde ser removida.');
        }

        return redirect()->route('indexSubrede');
    }

    /**
     * Obtém todos os IPs disponíveis para uma dada Subrede.
     * @param Subrede $subrede Instância da subrede
     * @return Response Resposta contendo a contagem de IPs disponíveis e quais IPs estão disponíveis
     */
    public function getAvailableIps(Subrede $subrede) {

        $allSubnetIps = $subrede->getAllIps(); // Obtém todos os IPs da subrede
        $activeIpsRaw = Requisicao::select('ip')->where('status', 1)->orWhere('status', 4)->get(); // Obtém todos os IPs sendo usados

        // Transforma a coleção resultante da query em um array contendo somento os IPs
        $activeIps = array();
        foreach($activeIpsRaw as $rawIP) $activeIps[] = $rawIP->ip;

        // Verifica quais IP da subrede não estão sendo utilizados
        $unusedIPs = array();
        foreach($allSubnetIps as $subnetIp)
        {
            if(!in_array($subnetIp, $activeIps)) $unusedIPs[] = $subnetIp; // Se o IP não estiver na lista de ativos, ent"ao adiciona da lista de IPs não utilizados
        }

        // Monta o JSON de resposta
        $result['count'] = count($unusedIPs);
        $result['ips'] = $unusedIPs;

        return response()->json($result);
    }
}
