<?php

namespace App\Http\Controllers;

use App\Events\DeviceEdited;
use App\Events\DeviceStored;
use App\Events\NewConfigurationFile;
use App\Events\RequestStored;
use App\Mail\RequestDenied;
use App\Mail\RequestExcluded;
use App\Mail\RequestReactivated;
use App\Mail\RequestSuspended;
use Illuminate\Http\Request;
use App\Http\Requests;
use App\Requisicao;
use App\TipoDispositivo;
use App\TipoUsuario;
use App\Ldapuser;
use Illuminate\Support\Facades\Event;
use Illuminate\Support\Facades\Session;
use Illuminate\Support\Facades\View;
use Illuminate\Support\Facades\Input;
use Illuminate\Http\UploadedFile;
use Illuminate\Support\Facades\Redirect;
use Illuminate\Support\Facades\Response;
use SSH;
use Illuminate\Support\Facades\File;
use Illuminate\Support\Facades\DB;
use Exception;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Mail;
use App\Mail\RequestApproved;

class RequisicaoController extends Controller
{

    /**
     * Reconstroi o static map (lista de ip vinculados a macs)
     * @return bool True em todos os casos.
     */
    private function rebuildStaticMap()
    {
         $requestsAllowed = Requisicao::where('status', 1)->orderBy(DB::raw('INET_ATON(ip)'))->get();

        $configXML = simplexml_load_file(storage_path("app/config/config.xml"));
        unset($configXML->dhcpd->lan->staticmap);

        $iterator = 0;

        foreach ($requestsAllowed as $request)
        {
            $configXML->dhcpd->lan->staticmap[$iterator]->mac = $request->mac;
            $configXML->dhcpd->lan->staticmap[$iterator]->ipaddr = $request->mac;
            $configXML->dhcpd->lan->staticmap[$iterator]->hostname = "Requisicao-" . $request->id;
            $configXML->dhcpd->lan->staticmap[$iterator]->addChild("descr");
            $configXML->dhcpd->lan->staticmap[$iterator]->addChild("arp_table_static_entry");
            $configXML->dhcpd->lan->staticmap[$iterator]->addChild("filename");
            $configXML->dhcpd->lan->staticmap[$iterator]->addChild("rootpath");
            $configXML->dhcpd->lan->staticmap[$iterator]->addChild("defaultleasetime");
            $configXML->dhcpd->lan->staticmap[$iterator]->addChild("maxleasetime");
            $configXML->dhcpd->lan->staticmap[$iterator]->addChild("gateway");
            $configXML->dhcpd->lan->staticmap[$iterator]->addChild("domain");
            $configXML->dhcpd->lan->staticmap[$iterator]->addChild("domainsearchlist");
            $configXML->dhcpd->lan->staticmap[$iterator]->addChild("ddnsdomain");
            $configXML->dhcpd->lan->staticmap[$iterator]->addChild("ddnsdomainprimary");
            $configXML->dhcpd->lan->staticmap[$iterator]->addChild("ddnsdomainkeyname");
            $configXML->dhcpd->lan->staticmap[$iterator]->addChild("ddnsdomainkey");
            $configXML->dhcpd->lan->staticmap[$iterator]->addChild("tftp");
            $configXML->dhcpd->lan->staticmap[$iterator]->addChild("ldap");
            ++$iterator;
        }

        // Salva o arquivo de configuração, sobreescrevendo o antigo;
        $configXML->saveXML(storage_path("app/config/config.xml"));

        return true;
    }

    /**
     * Atualiza o arquivo de configuração no pfSense
     */
    public function refreshPfsense()
    {
        $configFile = storage_path('app/config/config.xml');

        // Realiza o backup do arquivo de configuração anterior
        if(File::exists($configFile))
        {
            $lastModified = File::lastModified($configFile);
            File::copy($configFile, storage_path('app/config/config-' . $lastModified . '.xml'));
        }

        // Obtém o arquivo de configurações mais recente
        SSH::into('pfsense')->get("/cf/conf/config.xml", $configFile); // Path do arquivo local e path do arquivo remoto

        // Modificar o arquivo de configuração, adicionando o novo static map
        $this->rebuildStaticMap();

        // Envia o novo arquivo de configuração
        SSH::into('pfsense')->put($configFile, '/cf/conf/config.xml');

        // Remove o cache da configuração e reinicia o firewall com a nova configuração
        $commands = ["rm /tmp/config.cache", "/etc/rc.reload_all"];
        SSH::into('pfsense')->run($commands);

        Event::fire(new NewConfigurationFile());

        return;
    }

    /**
     * Renderiza a view da lista de dispositivos cadastrados
     * @param $type Status do dispositivo
     */
    public function listDevices($status)
    {
        $requests = DB::table('requisicoes')->join('tipo_dispositivo', 'requisicoes.tipo_dispositivo', '=', 'tipo_dispositivo.id')
            ->join('tipo_usuario', 'requisicoes.tipo_usuario', '=', 'tipo_usuario.id')
            ->select('requisicoes.id as id', 'responsavelNome', 'usuarioNome', 'tipo_usuario.descricao as tipousuario', 'tipo_dispositivo.descricao as tipodispositivo', 'submissao', 'avaliacao', 'status', 'ip', 'mac', 'descricao_dispositivo', 'validade')
            ->where('status', $status)
            ->orderBy(DB::raw('INET_ATON(ip)'))
            ->get();

        return View::make('requisicao.device.list')->with(['liberados' => $requests, 'tipo' => $status]);
    }

    /**
     * Recupera quais são os endereços IP livres para serem alocados.
     * @return array Array com o índice sendo a faixa e o conteúdo de cada índice sendo a quantidade de IPs livre naquela faixa
     */
    public static function getFreeIPs() {
        $freeIPs = array();

        for ($faixa=152; $faixa < 156; $faixa++) {
            for ($id=1; $id < 256; $id++) {
                $tempIP = '200.239.' . $faixa . '.' . $id;
                $count = Requisicao::where('ip', $tempIP)->whereRaw("(`status` = 1 or `status` = 2)")->count();
                if($count == 0 && $tempIP != '200.239.155.255') array_push($freeIPs, $tempIP);
            }
        }

        return $freeIPs;
    }

    /**
     * Renderiza a view de adição de um novo dispositivo.
     */
    public function showAddDevice()
    {
        $freeIPs = $this->getFreeIPs();
        $deviceType = TipoDispositivo::all();
        $userType = TipoUsuario::all();

        return View::make('requisicao.device.add')->with(['ipsLivre' => $freeIPs, 'dispositivos' => $deviceType, 'usuarios' => $userType]);
    }

    /**
     * Adiciona um dispositivo diretamente no sistema, sem a necessidade de requisiçAo
     */
    public function addDevice()
    {
        $input = Input::all();

        if($input['ip'] == "Sem IP livre")
        {
            Session::flash('mensagem', 'Não existe IP livre.');
            Session::flash('tipo', 'Erro');
        }
        else
        {
            $date = explode('/', $input['validade']);

            if(empty($input['validade']) || checkdate($date[1], $date[0], $date[2]) )
            {

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
                $newRequest->juizCPF = Session::get('id');
                $newRequest->juizMotivo = 'Adição manual.';
                $newRequest->ip = $input['ip'];
                $newRequest->status = 1;

                if( empty($input['validade']) ) $newRequest->validade = null;
                else $newRequest->validade = date_create_from_format('d/m/Y', $input['validade'])->format('Y-m-d H:i:s');

                $newRequest->save();

                Event::fire(new DeviceStored($newRequest));

                $this->refreshPfsense();

                Session::flash('mensagem', "Servidor pfSense atualizado");
                Session::flash('tipo', 'Informação');
            }
            else
            {
                Session::flash('mensagem', 'A data informada é inválida.');
                Session::flash('tipo', 'Erro');
            }
        }

        return Redirect::back();
    }

    /**
     * Renderiza a view com o formulário de edição de um dispositivo inserido.
     * @param $id ID da requisição
     */
    public function showEditDevice($id)
    {
        $request = Requisicao::find($id);
        $deviceType = TipoDispositivo::all();
        $userType = TipoUsuario::all();
        $freeIPs = $this->getFreeIPs();
        return View::make('requisicao.device.edit')->with(['requisicao' => $request, 'tiposdispositivo' => $deviceType, 'tiposusuario' => $userType, 'ipsLivre' => $freeIPs]);
    }

    /**
     * Edita os dados de uma requisição de um dispositivo.
     */
    public function editDevice()
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

            Event::fire(new DeviceEdited($record));

            $this->refreshPfsense();

            Session::flash('mensagem', "Servidor pfSense atualizado");
            Session::flash('tipo', 'Informação');
        }
        else  {
            Session::flash('mensagem', 'A data informada é inválida.');
            Session::flash('tipo', 'Erro');
        }

        return Redirect::back();
    }

    /**
     * Armazena uma nova requisição no banoo de dados
     */
    public function store()
    {
        Session::flash('tipo', 'Erro');
        $form = Input::all();

        if($form['termo']->isValid()) {
            if($form['termo']->getMimeType() == 'application/pdf') {
                $newRequest = new Requisicao;
                $newRequest->responsavel = $form['responsavel'];
                $newRequest->responsavelNome = ucwords(strtolower($form['responsavelNome']));
                $newRequest->usuario = $form['usuario'];
                $newRequest->usuarioNome = ucwords(strtolower($form['usuarioNome']));
                $newRequest->tipo_usuario = $form['tipousuario'];
                $newRequest->tipo_dispositivo = $form['tipodispositivo'];
                $newRequest->mac = $form['mac'];
                $newRequest->termo = $form['termo']->store('termos');
                $newRequest->descricao_dispositivo = $form['descricao'];
                $newRequest->justificativa = $form['justificativa'];
                $newRequest->save();

                Event::fire(new RequestStored($newRequest, Auth::user()));

                Session::flash('tipo', 'Sucesso');
                Session::flash('mensagem', 'Seu pedido foi enviado com sucesso. Aguarde pela resposta.');

                // Envio de e-mail avisando que a requisição foi aprovada.
                $user = Ldapuser::where('cpf', $form['responsavel'])->first();
                if(!is_null($user->email) || !empty($user->email)) Mail::to($user->email)->queue(new RequestApproved($user, $newRequest));
            }
            else Session::flash('mensagem', 'O arquivo enviado ou não está em formato PDF ou não foi codificado corretamente.');
        }
        else Session::flash('mensagem', 'Ouve um erro durante o envio do arquivo do termo de compromisso.');

        if(Session::get('tipo') == 'Erro') return Redirect::back()->withInput(Input::all());
        else return Redirect::route('listUserRequests');
    }

    /**
     * Renderiza a view com todas as requisições feitas pelo usuário atual.
     */
    public function showFromUser()
    {
        $requests = DB::table('requisicoes')->join('tipo_dispositivo', 'requisicoes.tipo_dispositivo', '=', 'tipo_dispositivo.id')
            ->join('tipo_usuario', 'requisicoes.tipo_usuario', '=', 'tipo_usuario.id')
            ->select('requisicoes.id as id', 'usuarioNome', 'tipo_usuario.descricao as tipousuario', 'tipo_dispositivo.descricao as tipodispositivo', 'submissao', 'avaliacao', 'status')
            ->where('responsavel', Auth::user()->cpf)
            ->get();

        return View::make('requisicao.showuser')->with('requisicoes', $requests);
    }

    /**
     * Renderiza a view com o formulário de adição de uma nova requisição
     */
    public function showAdd()
    {
        if(Auth::user()->isAdmin())
        {
            $users = TipoUsuario::all();
            $devices = TipoDispositivo::all();
        }
        else
        {
            $users = TipoUsuario::where('id', '>', 1)->get();
            $devices = TipoDispositivo::where('id', '>', 1)->get();
        }

        $organizations = Ldapuser::where('nivel', 3)->get();

        return View::make('requisicao.add')->with(['usuarios' => $users, 'dispositivos' => $devices, 'organizacoes' => $organizations]);
    }

    /**
     * Renderiza o arquivo PDF do termo de aceite enviado.
     * @param $filepath Nome do arquivo em base64
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

        return Response::make($file, 200, ['Content-Type' => 'application/pdf', 'Content-Disposition' => "inline; filename='termo.pdf'"] );
    }

    /**
     * Renderiza a view contendo todas as requisições de um determinado tipo
     * @param $type Tipo da requisição
     */
    public function show($type)
    {
        session()->put('novosPedidos', Requisicao::where('status', '=', 0)->count());

        if(!isset($type)) $type = 0;

        $requests = DB::table('requisicoes')->join('tipo_dispositivo', 'requisicoes.tipo_dispositivo', '=', 'tipo_dispositivo.id')
            ->join('tipo_usuario', 'requisicoes.tipo_usuario', '=', 'tipo_usuario.id')
            ->select('requisicoes.id as id', 'responsavelNome', 'usuarioNome', 'tipo_usuario.descricao as tipousuario', 'tipo_dispositivo.descricao as tipodispositivo', 'submissao', 'avaliacao')
            ->where('status', $type)
            ->get();

        return View::make('requisicao.show')->with(['requisicoes' => $requests, 'tipo' => $type]);
    }

    /**
     * Renderiza a view que mostra os detalhes de uma requisição.
     * @param $id ID da requisição
     */
    public function details($id)
    {
            $freeIPs = null;
            $requisicao = Requisicao::find($id);

            if(Auth::user()->isAdmin() == 1 && $requisicao->status == 0) $freeIPs = $this->getFreeIPs();

            return View::make('requisicao.details')->with(['requisicao' => $requisicao, 'ipsLivre' => $freeIPs]);
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
            $request->juizCPF = Auth::user()->cpf;
            $request->ip = Input::get('ip');

            if( empty($input['validade']) ) $request->validade = null;
            else $request->validade = date("Y-m-d H:i:s", time());

            $request->save();

            Event::fire(new RequestApproved($request, Auth::user()));

            $this->refreshPfsense();

            // Envio de e-mail avisando que a requisição foi aprovada.
            $user = Ldapuser::where('cpf', $request->responsavel)->first();
            if(!is_null($user->email)) Mail::to($user->email)->queue(new RequestApproved($user, $request));

            Session::flash('tipo', 'Sucesso');
            Session::flash('mensagem', 'Servidor pfSense');
        }
        else
        {
            Session::flash('mensagem', 'A data informada é inválida.');
            Session::flash('tipo', 'Erro');
        }

        return Redirect::back();
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

        Event::fire(new RequestDenied($requisicao, Auth::user()));

        // Envio de e-mail avisando que a requisição foi aprovada.
        $user = Ldapuser::where('cpf', $requisicao->responsavel)->first();
        if(!is_null($user->email)) Mail::to($user->email)->queue(new RequestDenied($user, $requisicao));

        Session::flash('tipo', 'Sucesso');
        Session::flash('mensagem', 'O pedido de liberação do dispositivo foi negado.');

        return redirect()->route('showRequest', 0);
    }

    /**
     * Suspende temporariamente uma requisição, impedindo que o usuário seja capaz de usar a rede.
     */
    public function suspend()
    {
        $requisicao = Requisicao::find(Input::get('id'));
        $requisicao->juizMotivo = Input::get('juizMotivo');
        $requisicao->juizCPF = Session::get("id");
        $requisicao->status = 4;
        $requisicao->avaliacao = date("Y-m-d H:i:s", time());
        $requisicao->save();

        Event::fire(new RequestSuspended($requisicao, Auth::user()));

        // Envio de e-mail avisando que a requisição foi aprovada.
        $user = Ldapuser::where('cpf', $requisicao->responsavel)->first();
        if(!is_null($user->email)) Mail::to($user->email)->queue(new RequestSuspended($user, $requisicao));

        $this->refreshPfsense();

        Session::flash('tipo', 'Sucesso');
        Session::flash('mensagem', "Servidor pfSense atualizado.");

        return Redirect::back()->with('requisicao', $requisicao);

    }

    /**
     * Desativa uma requisição, sendo que o usuário não será capaz mais de se conectar a rede, sendo necessário abrir
     * uma nova requisição.
     */
    public function disable()
    {
        $requisicao = Requisicao::find(Input::get('id'));
        $requisicao->juizMotivo = Input::get('juizMotivo');
        $requisicao->juizCPF = Session::get("id");
        $requisicao->status = 5;
        $requisicao->avaliacao = date("Y-m-d H:i:s", time());
        $requisicao->save();

        Event::fire(new RequestExcluded($requisicao, Auth::user()));

        // Envio de e-mail avisando que a requisição foi aprovada.
        $user = Ldapuser::where('cpf', $requisicao->responsavel)->first();
        if(!is_null($user->email)) Mail::to($user->email)->queue(new RequestExcluded($user, $requisicao));

        $this->refreshPfsense();

        Session::flash('tipo', 'Sucesso');
        Session::flash('mensagem', "O dispositivo foi desligado da rede.");

        return Redirect::route('listDevice');
    }

    /**
     * Reativa uma requisição que foi suspensa.
     * @param $id ID da requisição
     */
    public function reactive($id)
    {
        $requisicao = Requisicao::find($id);
        $requisicao->juizMotivo = null;
        $requisicao->juizCPF = Auth::user()->cpf;
        $requisicao->status = 1;
        $requisicao->avaliacao = date("Y-m-d H:i:s", time());
        $requisicao->save();

        Event::fire(new RequestReactivated($requisicao, Auth::user()));

        // Envio de e-mail avisando que a requisição foi aprovada.
        $user = Ldapuser::where('cpf', $requisicao->responsavel)->first();
        if(!is_null($user->email)) Mail::to($user->email)->queue(new RequestReactivated($user, $requisicao));

        $this->refreshPfsense();

        Session::flash('tipo', 'Sucesso');
        Session::flash('mensagem', "O dispositivo teve o acesso reativado.");

        return Redirect::back();
    }

    /**
     * Apaga uma requisição do banco de dados.
     */
    public function delete()
    {
        $requisicao = Requisicao::find(Input::get('id'));

        if(Auth::user()->isAdmin() || $requisicao->responsavel == Auth::user()->cpf)
        {
            if($requisicao->status == 0)
            {
                Requisicao::destroy($requisicao->id);
                Session::flash('tipo', 'Sucesso');
                Session::flash('mensagem', "A requisição foi apagada.");
                return redirect()->route('listUserRequests');
            }
            else
            {
                Session::flash('tipo', 'Erro');
                Session::flash('mensagem', "Não é possível deletar uma requisição que já foi julgada.");
                return redirect()->back();
            }
        }
    }

    /**
     * Renderiza a view de edição de uma requisição.
     * @param $id ID da requisição
     */
    public function showEdit($id)
    {
        $requisicao = Requisicao::find($id);

        if(Auth::user()->isAdmin() || $requisicao->responsavel == Auth::user()->cpf)
        {
            if($requisicao->status == 0)
            {
                if(Auth::user()->isAdmin())
                {
                    $users = TipoUsuario::all();
                    $devices = TipoDispositivo::all();
                }
                else
                {
                    $users = TipoUsuario::where('id', '>', 1)->get();
                    $devices = TipoDispositivo::where('id', '>', 1)->get();
                }
                return View::make('admin.actions.editRequest')->with(['requisicao' => $requisicao, 'usuarios' => $users, 'dispositivos' => $devices, 'organizacoes' => Ldapuser::where('nivel', 3)->get()]);
            }
            return redirect()->route('listUserRequests');
        }
        else abort(403);
    }

    public function edit()
    {
            $requisicao = Requisicao::find(Input::get('id'));

            if(Auth::user()->isAdmin() || $requisicao->responsavel == Auth::user()->cpf)
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
                                Session::flash('tipo', 'Erro');
                                Session::flash('mensagem', 'O formato do arquivo não é PDF ou não foi bem codificado.');
                            }
                        }
                        else
                        {
                            Session::flash('tipo', 'Erro');
                            Session::flash('mensagem', 'Houve um erro no envio do arquivo e ele não pode ser validado.');
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

                    Session::flash('tipo', 'Sucesso');
                    Session::flash('mensagem', 'Seu pedido foi atualizado com sucesso. Aguarde pela resposta.');
                }
                else
                {
                    Session::flash('tipo', 'Erro');
                    Session::flash('mensagem', 'Não é possível editar uma requisição que foi julgada. Edite o dispostivo ao invés disso.');
                }
            }
            else abort(403);

            return redirect()->back();
    }

    /**
     * Recupera os usuários ativos em um determinado intervalo de tempo
     * @param $id
     */
    public function getMonthlyActiveUsers($id)
    {
        if ($id == 1) $url = "http://200.239.152.2:8080/trafego-nti/Subnet-1-200.239.152.0.html"; //diário
        elseif ($id == 2) $url = "http://200.239.152.2:8080/trafego-nti/Subnet-2-200.239.152.0.html"; //semanal
        else $url = "http://200.239.152.2:8080/trafego-nti/Subnet-3-200.239.152.0.html"; // mensal e inativos;

        try
        {
            $content = file_get_contents($url);
        }
        catch (Exception $e0)
        {
            $content = false;
        }

        if($content != false) {
            $dom = new \DOMDocument;
            $dom->preserveWhiteSpace = false;

            try
            {
                libxml_use_internal_errors(true); // ignora erros de formatação provenientes do Bandwidthd
                $loadSuccess = $dom->loadHTML($content);
            } catch (Exception $e) {
                $loadSuccess = false;
            }

            if($loadSuccess == true)
            {
                $rows = $dom->getElementsByTagName('td');
                $frequentUsers = array();

                for($i=10; $rows->item($i) != NULL; $i+=10)
                {
                    // $rows->item(11)->nodeValue; // Total
                    // $rows->item(12)->nodeValue; // Total Sent
                    // $rows->item(13)->nodeValue; // Total Received
                    // $rows->item($i)->nodeValue; // IP

                    $user = Requisicao::where('ip', $rows->item($i)->nodeValue)->where('status', 1)->first();

                    // Se o usuário não existe no banco, é uma falha de segurança na rede
                    if(is_null($user))
                    {
                        $user = new Requisicao();
                        $user->id = -1;
                        $user->ip = $rows->item($i)->nodeValue;
                        $user->responsavelNome = "Inexistente no banco";
                        $user->usuarioNome = "Inexistente no banco";
                        $user->descricao_dispositivo = "Desconhecido";
                    }

                    // Adição de dados de uso
                    $user['totalTransferred'] = $rows->item($i + 1)->nodeValue;
                    $user['sent'] = $rows->item($i + 2)->nodeValue;
                    $user['received'] = $rows->item($i + 3)->nodeValue;

                    array_push($frequentUsers, $user);
                }
            }
            else
            {
                $frequentUsers = NULL;
                Session::flash('tipo', 'Erro');
                Session::flash('mensagem', 'O servidor do Bandwidthd não formatou corretamente a sua saída. Verifique a página gerada pelo Bandwidthd');
            }

            return $frequentUsers;
        }
        else
        {
            Session::flash('tipo', 'Erro');
            Session::flash('mensagem', 'O servidor do Bandwidthd não respondeu a solicitação. Tente novamente em alguns instantes.');
            return NULL;
        }
    }

    /**
     * Renderiza a view contendo o uso da rede pelos usuários.
     * @param $id Intervalo de usuários ativos ou inativos (1 = ativos hoje, 2 = ativos na semana, 3 = ativos no mês e 4 = inativos a um mês ou mais
     */
    public function showUsage($id)
    {
        $frequentUsers = $this->getMonthlyActiveUsers($id);

        if($id < 4) return View::make('requisicao.usage')->with(['id' => $id, 'usuarios' => $frequentUsers]);
        else
        {
            if(is_null($frequentUsers)) $nonFrequentUsers = null;
            else
            {
                //pegar os usuarios que tem status == 1 mas não tem o ip na lista
                $frequentIPs = array();

                // Obtém todos os IPs frequentes
                foreach ($frequentUsers as $user) array_push($frequentIPs, $user->ip);

                //Obtém todos os usuários aprovados que não estão na lista de frequentes
                $nonFrequentUsers = Requisicao::where('status', 1)->whereNotIn('ip', $frequentIPs)->get();
            }

            return View::make('requisicao.usage')->with(['id' => $id, 'usuarios' => $nonFrequentUsers]);
        }
    }
}
