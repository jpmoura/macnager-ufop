<?php

namespace App\Http\Controllers;

use App\Events\DeviceEdited;
use App\Events\DeviceStored;
use App\Events\RequestStored;
use App\Http\Requests\CreateDeviceRequest;
use App\Http\Requests\CreateRequisicaoRequest;
use App\Ldapuser;
use App\Mail\RequestApproved;
use App\Mail\RequestDenied;
use App\Mail\RequestExcluded;
use App\Mail\RequestReactivated;
use App\Mail\RequestReceived;
use App\Mail\RequestSuspended;
use App\Requisicao;
use App\Subrede;
use App\TipoDispositivo;
use App\TipoUsuario;
use Illuminate\Support\Facades\Event;
use Illuminate\Support\Facades\Input;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Mail;
use Exception;

class RequisicaoController extends Controller
{

    /**
     * Retira os pontos e traço de um CPF.
     * @param $cpf string Número de CPF no formato 000.000.000-00
     * @return string Número de CPF no formato 00000000000
     */
    private static function cleanCPF($cpf) {
        $cpf = str_replace('.', '', $cpf);
        $cpf = str_replace('-', '', $cpf);
        return $cpf;
    }

    /**
     * Renderiza a view da lista de dispositivos cadastrados
     * @param $status int Status do dispositivo
     * @return mixed View contendo a lista de dispositos para um dado status
     */
    public function indexDevice($status)
    {
        $requests = DB::table('requisicoes')->join('tipo_dispositivo', 'requisicoes.tipo_dispositivo', '=', 'tipo_dispositivo.id')
            ->join('tipo_usuario', 'requisicoes.tipo_usuario', '=', 'tipo_usuario.id')
            ->select('requisicoes.id as id', 'responsavelNome', 'usuarioNome', 'tipo_usuario.descricao as tipousuario', 'tipo_dispositivo.descricao as tipodispositivo', 'submissao', 'avaliacao', 'status', 'ip', 'mac', 'descricao_dispositivo', 'validade')
            ->where('status', $status)
            ->orderBy(DB::raw('INET_ATON(ip)'))
            ->get();

        return view('requisicao.device.index')->with(['liberados' => $requests, 'tipo' => $status]);
    }

    /**
     * Renderiza a view de adição de um novo dispositivo.
     */
    public function createDevice()
    {
        $deviceType = TipoDispositivo::all();
        $userType = TipoUsuario::all();
        $subnetworks = Subrede::with('tipo')->get();

        return view('requisicao.device.create')->with(['dispositivos' => $deviceType, 'usuarios' => $userType, 'subredes' => $subnetworks]);
    }

    /**
     * @param CreateDeviceRequest $request Requisição já validada
     * @return mixed Página anterior
     */
    public function storeDevice(CreateDeviceRequest $request)
    {
        $input = $request->all();

        // Tratamento de CPFs
        $input['responsavel'] = RequisicaoController::cleanCPF($input['responsavel']);
        $input['usuario'] = RequisicaoController::cleanCPF($input['usuario']);

        // Criar requisição para o novo dispositivo
        $newRequest = new Requisicao;
        $newRequest->responsavel = $input['responsavel'];
        $newRequest->responsavelNome = ucwords(strtolower($input['responsavelNome']));
        $newRequest->usuario = $input['usuario'];
        $newRequest->usuarioNome = ucwords(strtolower($input['usuarioNome']));
        $newRequest->tipo_usuario = $input['tipousuario'];
        $newRequest->tipo_dispositivo = $input['tipodispositivo'];
        $newRequest->mac = $input['mac'];
        $newRequest->termo = "termos/default.pdf";
        $newRequest->descricao_dispositivo = $input['descricao'];
        $newRequest->justificativa = $input['justificativa'];
        $newRequest->submissao = date("Y-m-d H:i:s", time());
        $newRequest->avaliacao = date("Y-m-d H:i:s", time());
        $newRequest->juizCPF = auth()->user()->cpf;
        $newRequest->juizMotivo = 'Adição manual.';
        $newRequest->ip = $input['ip'];
        $newRequest->status = 1;

        if(empty($input['validade'])) $newRequest->validade = null;
        else $newRequest->validade = date_create_from_format('d/m/Y', $input['validade'])->format('Y-m-d H:i:s');

        $newRequest->save();

        if(PfsenseController::refreshPfsense())
        {
            session()->flash('mensagem', "Servidor pfSense atualizado");
            session()->flash('tipo', 'info');
            Event::fire(new DeviceStored($newRequest));
        }
        else
        {
            session()->flash('mensagem', 'Não foi possível conectar ao servidor pfSense.');
            session()->flash('tipo', 'error');
        }

        return back();
    }

    /**
     * Renderiza a view com o formulário de edição de um dispositivo inserido.
     * @param $requisicao Requisicao Instância da requisição que será editada.
     */
    public function editDevice(Requisicao $requisicao)
    {
        $deviceType = TipoDispositivo::all();
        $userType = TipoUsuario::all();
        return view('requisicao.device.edit')->with(['requisicao' => $requisicao, 'tiposdispositivo' => $deviceType, 'tiposusuario' => $userType]);
    }

    /**
     * Atualiza dos dados de uma requisição.
     * @return mixed Página anterior.
     */
    public function updateDevice()
    {
        $input = Input::all();

        $date = explode('/', $input['validade']);

        if(empty($input['validade']) || checkdate($date[1], $date[0], $date[2]) )
        { // Se a data for válida ou em branco
            $record = Requisicao::find($input['id']);
            $record->ip = $input['ip'];
            $record->responsavel = ucwords(strtolower($input['responsavel']));
            $record->responsavelNome = ucwords(strtolower($input['responsavelNome']));
            $record->usuario = $input['usuario'];
            $record->usuarioNome = ucwords(strtolower($input['usuarioNome']));
            $record->mac = $input['mac'];
            $record->descricao_dispositivo = $input['descricao'];
            $record->tipo_dispositivo = $input['tipodispositivo'];
            $record->tipo_usuario = $input['tipousuario'];

            if( empty($input['validade']) ) $record->validade = null;
            else $record->validade = date_create_from_format('d/m/Y', $input['validade'])->format('Y-m-d H:i:s');

            $record->save();

            if(PfsenseController::refreshPfsense())
            {
                session()->flash('mensagem', "Servidor pfSense atualizado");
                session()->flash('tipo', 'info');
                Event::fire(new DeviceEdited($record));
            }
            else
            {
                session()->flash('mensagem', 'Não foi possível conectar ao servidor pfSense.');
                session()->flash('tipo', 'error');
            }
        }
        else
        {
            session()->flash('mensagem', 'A data informada é inválida.');
            session()->flash('tipo', 'error');
        }

        return back();
    }

    /**
     * Armazena um instância de Requisicao no banco de dados após validação do formulário de criação.
     * @param CreateRequisicaoRequest $request Requisição contendo o formulário validado
     * @return \Illuminate\Http\RedirectResponse View com o índice de todas as requisições já feitas pelo usuário.
     */
    public function store(CreateRequisicaoRequest $request)
    {
        $form = $request->all();

        // Limpeza de CPF
        $form['usuario'] = RequisicaoController::cleanCPF($form['usuario']);

        $newRequest = Requisicao::create([
            'responsavel' => $form['responsavel'],
            'responsavelNome' => ucwords(strtolower($form['responsavelNome'])),
            'usuario' => $form['usuario'],
            'usuarioNome' => ucwords(strtolower($form['usuarioNome'])),
            'tipo_dispositivo' => $form['tipousuario'],
            'tipo_usuario' => $form['tipodispositivo'],
            'mac' => $form['mac'],
            'termo' => $form['termo']->store('termos'),
            'descricao_dispositivo' => $form['descricao'],
            'justificativa' => $form['justificativa'],
        ]);

        Event::fire(new RequestStored($newRequest, auth()->user()));

        session()->flash('tipo', 'success');
        session()->flash('mensagem', 'Seu pedido foi enviado com sucesso. Você será notificado assim que o pedido for julgado.');

        // Envio de e-mail avisando que a requisição foi aprovada.
        $user = Ldapuser::where('cpf', $form['responsavel'])->first();
        if(!is_null($user->email) || !empty($user->email)) Mail::to($user->email)->queue(new RequestReceived($user, $newRequest));

        return redirect()->route('indexUserRequests');
    }

    /**
     * Renderiza a view com o índice de todas as requisições feitas pelo usuário atual.
     * @return mixed View com a lista das requisições
     */
    public function userIndex()
    {
        $requests = Requisicao::with('tipoDoUsuario', 'tipoDoDispositivo')->where('responsavel', auth()->user()->cpf)->get();
        return view('requisicao.indexUser')->with('requisicoes', $requests);
    }

    /**
     * Renderiza a view com o formulário de adição de uma nova requisição
     */
    public function create()
    {
        $users = TipoUsuario::where('id', '>', 1)->get();
        $devices = TipoDispositivo::where('id', '>', 1)->get();
        $organizations = Ldapuser::where('nivel', 3)->get();

        return view('requisicao.create')->with(['usuarios' => $users, 'dispositivos' => $devices, 'organizacoes' => $organizations]);
    }

    /**
     * Renderiza o arquivo PDF do termo de aceite enviado.
     * @param $filepath string do arquivo em base64
     * @return mixed
     */
    public function showTerm($filepath)
    {
        $file = NULL;

        try
        {
            $file = file_get_contents(storage_path('app/' . base64_decode($filepath)));
        }
        catch(Exception $ex)
        {
            abort(404);
        }

        return response($file, 200, ['Content-Type' => 'application/pdf', 'Content-Disposition' => "inline; filename='termo.pdf'"]);
    }

    /**
     * Renderiza a view contendo todas as requisições de um determinado tipo
     * @param $type Tipo da requisição
     */
    public function allIndex($type)
    {
        session()->put('novosPedidos', Requisicao::where('status', '=', 0)->count());

        if(!isset($type)) $type = 0;

        $requests = DB::table('requisicoes')->join('tipo_dispositivo', 'requisicoes.tipo_dispositivo', '=', 'tipo_dispositivo.id')
            ->join('tipo_usuario', 'requisicoes.tipo_usuario', '=', 'tipo_usuario.id')
            ->select('requisicoes.id as id', 'responsavelNome', 'usuarioNome', 'tipo_usuario.descricao as tipousuario', 'tipo_dispositivo.descricao as tipodispositivo', 'submissao', 'avaliacao')
            ->where('status', $type)
            ->get();

        return view('requisicao.indexAll')->with(['requisicoes' => $requests, 'tipo' => $type]);
    }

    /**
     * Renderiza view contendo os detalhes de uma requisição
     * @param Requisicao $request Requisição a qual se verá os detalhes
     * @return mixed View com os dados da requisição
     */
    public function show(Requisicao $request)
    {
        return view('requisicao.show')->with('requisicao', $request);
    }

    /**
     * Aprova uma requisição.
     */
    public function approve()
    {
        $date = explode('/', Input::get('validade'));

        // Verifica se a data informada é nula ou se ele é válida
        if(empty(Input::get('validade')) || checkdate($date[1], $date[0], $date[2]) )
        {
            $request = Requisicao::find(Input::get('id'));
            $request->status = 1;
            $request->avaliacao = date("Y-m-d H:i:s", time());
            $request->juizCPF = auth()->user()->cpf;
            $request->ip = Input::get('ip');

            if( empty($input['validade']) ) $request->validade = null;
            else $request->validade = date("Y-m-d H:i:s", time());

            $request->save();

            if(PfsenseController::refreshPfsense())
            {
                session()->flash('mensagem', "Servidor pfSense atualizado");
                session()->flash('tipo', 'success');
                Event::fire(new RequestApproved($request, auth()->user()));
            }
            else
            {
                session()->flash('mensagem', 'Não foi possível conectar ao servidor pfSense.');
                session()->flash('tipo', 'error');
            }

            // Envio de e-mail avisando que a requisição foi aprovada.
            $user = Ldapuser::where('cpf', $request->responsavel)->first();
            if(!is_null($user->email)) Mail::to($user->email)->queue(new RequestApproved($user, $request));
        }
        else
        {
            session()->flash('mensagem', 'A data informada é inválida.');
            session()->flash('tipo', 'error');
        }

        return back();
    }

    /**
     * Nega uma requisição.
     */
    public function deny()
    {
        $requisicao = Requisicao::find(Input::get('id'));
        $requisicao->juizMotivo = Input::get('juizMotivo');
        $requisicao->juizCPF = Session::get("id");
        $requisicao->status = 2;
        $requisicao->save();

        Event::fire(new RequestDenied($requisicao, auth()->user()));

        // Envio de e-mail avisando que a requisição foi aprovada.
        $user = Ldapuser::where('cpf', $requisicao->responsavel)->first();
        if(!is_null($user->email)) Mail::to($user->email)->queue(new RequestDenied($user, $requisicao));

        session()->flash('tipo', 'success');
        session()->flash('mensagem', 'O pedido de liberação do dispositivo foi negado.');

        return redirect()->route('showRequest', 0);
    }

    /**
     * Suspende temporariamente uma requisição, impedindo que o usuário seja capaz de usar a rede.
     */
    public function block()
    {
        $requisicao = Requisicao::find(Input::get('id'));
        $requisicao->juizMotivo = Input::get('juizMotivo');
        $requisicao->juizCPF = auth()->user->cpf;
        $requisicao->status = 4;
        $requisicao->avaliacao = date("Y-m-d H:i:s", time());
        $requisicao->save();

        // Envio de e-mail avisando que a requisição foi aprovada.
        $user = Ldapuser::where('cpf', $requisicao->responsavel)->first();
        if(!is_null($user->email)) Mail::to($user->email)->queue(new RequestSuspended($user, $requisicao));

        if(PfsenseController::refreshPfsense())
        {
            session()->flash('mensagem', "Servidor pfSense atualizado");
            session()->flash('tipo', 'info');
            Event::fire(new RequestSuspended($requisicao, auth()->user()));
        }
        else
        {
            session()->flash('mensagem', 'Não foi possível conectar ao servidor pfSense.');
            session()->flash('tipo', 'error');
        }

        return back()->with('requisicao', $requisicao);

    }

    /**
     * Desativa uma requisição, sendo que o usuário não será capaz mais de se conectar a rede, sendo necessário abrir
     * uma nova requisição.
     */
    public function disable()
    {
        $requisicao = Requisicao::find(Input::get('id'));
        $requisicao->juizMotivo = Input::get('juizMotivo');
        $requisicao->juizCPF = auth()->user()->cpf;
        $requisicao->status = 5;
        $requisicao->avaliacao = date("Y-m-d H:i:s", time());
        $requisicao->save();

        // Envio de e-mail avisando que a requisição foi aprovada.
        $user = Ldapuser::where('cpf', $requisicao->responsavel)->first();
        if(!is_null($user->email)) Mail::to($user->email)->queue(new RequestExcluded($user, $requisicao));

        if(PfsenseController::refreshPfsense())
        {
            session()->flash('mensagem', "Servidor pfSense atualizado");
            session()->flash('tipo', 'success');
            Event::fire(new RequestExcluded($requisicao, auth()->user()));
        }
        else
        {
            session()->flash('mensagem', 'Não foi possível conectar ao servidor pfSense.');
            session()->flash('tipo', 'error');
        }

        return redirect()->route('listDevice');
    }

    /**
     * Reativa uma requisição que foi suspensa.
     * @param $id ID da requisição
     */
    public function reactive($id)
    {
        $requisicao = Requisicao::find($id);
        $requisicao->juizMotivo = null;
        $requisicao->juizCPF = auth()->user()->cpf;
        $requisicao->status = 1;
        $requisicao->avaliacao = date("Y-m-d H:i:s", time());
        $requisicao->save();

        // Envio de e-mail avisando que a requisição foi aprovada.
        $user = Ldapuser::where('cpf', $requisicao->responsavel)->first();
        if(!is_null($user->email)) Mail::to($user->email)->queue(new RequestReactivated($user, $requisicao));

        if(PfsenseController::refreshPfsense())
        {
            session()->flash('mensagem', "Servidor pfSense atualizado");
            session()->flash('tipo', 'info');
            Event::fire(new RequestReactivated($requisicao, auth()->user()));
        }
        else
        {
            session()->flash('mensagem', 'Não foi possível conectar ao servidor pfSense.');
            session()->flash('tipo', 'error');
        }

        return back();
    }

    /**
     * Apaga uma requisição do banco de dados.
     */
    public function delete()
    {
        $requisicao = Requisicao::find(Input::get('id'));

        if(auth()->user()->isAdmin() || $requisicao->responsavel == auth()->user()->cpf)
        {
            if($requisicao->status == 0)
            {
                Requisicao::destroy($requisicao->id);
                session()->flash('tipo', 'success');
                session()->flash('mensagem', "A requisição foi apagada.");
                return redirect()->route('listUserRequests');
            }
            else
            {
                session()->flash('tipo', 'error');
                session()->flash('mensagem', "Não é possível deletar uma requisição que já foi julgada.");
                return redirect()->back();
            }
        }
    }

    /**
     * Renderiza a view de edição de uma requisição.
     * @param $id int ID da requisição
     */
    public function edit($id)
    {
        $requisicao = Requisicao::find($id);

        if(auth()->user()->isAdmin() || $requisicao->responsavel == auth()->user()->cpf)
        {
            if($requisicao->status == 0)
            {
                if(auth()->user()->isAdmin())
                {
                    $users = TipoUsuario::all();
                    $devices = TipoDispositivo::all();
                }
                else
                {
                    $users = TipoUsuario::where('id', '>', 1)->get();
                    $devices = TipoDispositivo::where('id', '>', 1)->get();
                }
                return view('admin.actions.editRequest')->with(['requisicao' => $requisicao, 'usuarios' => $users, 'dispositivos' => $devices, 'organizacoes' => Ldapuser::where('nivel', 3)->get()]);
            }
            return redirect()->route('listUserRequests');
        }
        else abort(403);
    }

    /**
     * Edita uma instância de requisição que ainda não foi julgada.
     */
    public function update()
    {
        $requisicao = Requisicao::find(Input::get('id'));

        if(auth()->user()->isAdmin() || $requisicao->responsavel == auth()->user()->cpf)
        {
            if($requisicao->status == 0)
            {
                // pegar variáveis e salvar
                $form = Input::all();

                if(Input::has('termo'))
                {
                    if($form['termo']->isValid())
                    {
                        if($form['termo']->getMimeType() == 'application/pdf') $requisicao->termo = $form['termo']->store('termos');
                        else
                        {
                            session()->flash('tipo', 'error');
                            session()->flash('mensagem', 'O formato do arquivo não é PDF ou não foi bem codificado.');
                        }
                    }
                    else
                    {
                        session()->flash('tipo', 'error');
                        session()->flash('mensagem', 'Houve um erro no envio do arquivo e ele não pode ser validado.');
                    }
                }

                $requisicao->usuario = $form['usuario'];
                $requisicao->usuarioNome = $form['usuarioNome'];
                $requisicao->tipo_usuario = $form['tipousuario'];
                $requisicao->tipo_dispositivo = $form['tipodispositivo'];
                $requisicao->mac = $form['mac'];
                $requisicao->descricao_dispositivo = $form['descricao'];
                $requisicao->justificativa = $form['justificativa'];
                $requisicao->save();

                session()->flash('tipo', 'success');
                session()->flash('mensagem', 'Seu pedido foi atualizado com sucesso. Aguarde pela resposta.');
            }
            else
            {
                session()->flash('tipo', 'error');
                session()->flash('mensagem', 'Não é possível editar uma requisição que foi julgada. Edite o dispostivo ao invés disso.');
            }
        }
        else abort(403);

        return redirect()->back();
    }
}
