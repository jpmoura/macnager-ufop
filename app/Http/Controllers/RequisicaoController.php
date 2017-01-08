<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;

use App\Http\Requests;
use App\Requisicao;
use App\TipoDispositivo;
use App\TipoUsuario;
use App\Ldapuser;
use Session;
use View;
use Input;
use Illuminate\Http\UploadedFile;
use Illuminate\Support\Facades\Redirect;
use Response;
use SSH;
use File;
use DB;
use Exception;
use Auth;

class RequisicaoController extends Controller
{

    // Tenta deletar um determinado arquivo em 'attempts' vezes
    private function tryToDelete($file, $attempts = 10) {
        while(File::exists($file) && $attempts > 0) File::delete($file);
        return !File::exists($file);
    }

    // Gera a tabela ARP de whitelist do firewall, envia para o servidor e reinicia o serviço
    public function updateArpAndDhcp(&$arpResponse, &$dhcpResponse)
    {
        $successful = true;
        $this->generateFiles(); // gera os arquivos

        // Sincronizando ARP

        $this->result = '';
        SSH::into('firewall')->run('cp /usr/local/etc/arp_icea /usr/local/etc/arp_icea.' . date("d-m-Y-H-i-s", time()) . '.backup');
        SSH::into('firewall')->put('/var/www/html/macnager/storage/app/public/temp_arp', '/usr/local/etc/arp_icea'); // transfere o arquivo
        SSH::into('firewall')->run('sudo arp -f /usr/local/etc/arp_icea', function($line) { $this->result = $this->result . $line . "<br />"; } ); // recarrega a whitelist do Firewall
        if($this->result == "") $this->result = "Arquivo criado e transferido com sucesso.";
        $arpResponse = $this->result;

        // Sincronizando DHCP
        $this->result = '';
        SSH::into('dhcp')->run('cp /usr/local/etc/dhcp.conf /usr/local/etc/dhcpd.conf.' . date("d-m-Y-H-i-s", time()) . '.backup');
        SSH::into('dhcp')->put('/var/www/html/macnager/storage/app/public/temp_dhcp', '/usr/local/etc/dhcpd.conf'); // transfere o arquivo
        SSH::into('dhcp')->run('/usr/local/etc/rc.d/isc-dhcpd restart', function($line) { $this->result = $this->result. $line . "<br />"; }); // reinicia o servidor DHCP
        if($this->result == "") $this->result = "Arquivo criado e transferido com sucesso.";
        $dhcpResponse = $this->result;

        return;
    }

    private function formatToArp($record)
    {
        $formatted = str_replace(' ', '', $record->ip) . ' ' . str_replace(' ', '', $record->mac) . ' #Requisicao-' . $record->id . PHP_EOL;
        return $formatted;
    }

    private function formatToDhcp($record)
    {
        $explodedIP = explode('.', $record->ip);
        $suffixIP = $explodedIP[2] . '.' . $explodedIP[3];

        $formatted = 'host ' . $suffixIP . ' {' . PHP_EOL .
            "\thardware ethernet " . str_replace(' ', '', $record->mac) . ';' . PHP_EOL .
            "\tfixed-address " . str_replace(' ', '', $record->ip). ';' . PHP_EOL .
            "}" . PHP_EOL;

        return $formatted;
    }

    private function generateFiles()
    {
        $requestsAllowed = Requisicao::where('status', 1)->orderBy(DB::raw('INET_ATON(ip)'))->get();

        $dhcpFile = '/var/www/html/macnager/storage/app/public/temp_dhcp';
        $arpFile = '/var/www/html/macnager/storage/app/public/temp_arp';

        $successful = $this->tryToDelete($arpFile);
        if($successful) $successful = $this->tryToDelete($dhcpFile);

        $dhcpDocument = "option domain-name-servers " . PHP_EOL . PHP_EOL .
            "189.38.95.95,189.38.95.96,8.8.8.8,8.8.4.4;" . PHP_EOL . PHP_EOL .
            "default-lease-time 99999999;" . PHP_EOL . PHP_EOL .
            "max-lease-time 99999999;" . PHP_EOL . PHP_EOL .
            "log-facility local7;" . PHP_EOL . PHP_EOL .
            "subnet 200.239.152.0 netmask 255.255.252.0 {" . PHP_EOL .
            "\toption routers 200.239.152.2;" . PHP_EOL .
            "}" . PHP_EOL . PHP_EOL;

        $arpDocument = "";

        if($successful == true)
        {
            // Itera sobre todos os registros, formatando-os para a escrita
            foreach ($requestsAllowed as $entry)
            {
                $dhcpDocument .= $this->formatToDhcp($entry) . PHP_EOL;
                $arpDocument .= $this->formatToArp($entry) . PHP_EOL;
            }

            $successful = File::append($arpFile, $arpDocument);
            if($successful) $successful = File::append($dhcpFile, $dhcpDocument);
        }

        return $successful;
    }

    public function getListMac($type)
    {
        if(UserController::checkLogin()) {
            $requests = DB::table('requisicoes')->join('tipo_dispositivo', 'requisicoes.tipo_dispositivo', '=', 'tipo_dispositivo.id')
                ->join('tipo_usuario', 'requisicoes.tipo_usuario', '=', 'tipo_usuario.id')
                ->select('requisicoes.id as id', 'responsavelNome', 'usuarioNome', 'tipo_usuario.descricao as tipousuario', 'tipo_dispositivo.descricao as tipodispositivo', 'submissao', 'avaliacao', 'status', 'ip', 'mac', 'descricao_dispositivo', 'validade')
                ->where('status', $type)
                ->orderBy(DB::raw('INET_ATON(ip)'))
                ->get();
            return View::make('admin.actions.listMac')->with(['liberados' => $requests, 'tipo' => $type]);
        }
        else return redirect('/login');
    }

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

    public function getAddMac()
    {
        if(UserController::checkLogin()) {

            if(UserController::checkPermissions(1)) {
                $freeIPs = $this->getFreeIPs();
                return View::make('admin.actions.addMac')->with(['ipsLivre' => $freeIPs, 'dispositivos' => TipoDispositivo::all(), 'usuarios' => TipoUsuario::all()]);
            }
            else abort(403);
        }
        else return redirect('/login');
    }

    public function doAddMac()
    {

        if(UserController::checkPermissions(1)) {
            $input = Input::all();

            if($input['ip'] == "Sem IP livre") {
                Session::flash('mensagem', 'Não existe IP livre.');
                Session::flash('tipo', 'Erro');
            }
            else {
                $date = explode('/', $input['validade']);

                if(empty($input['validade']) || checkdate($date[1], $date[0], $date[2]) ) {

                    // Criar requisição para o novo dispositivo
                    $newRequest = new Requisicao;
                    $newRequest->responsavel = $input['responsavel'];
                    $newRequest->responsavelNome = $input['responsavelNome'];
                    $newRequest->usuario = $input['usuario'];
                    $newRequest->usuarioNome = $input['usuarioNome'];
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

                    $this->updateArpAndDhcp($arpResponse, $dhcpResponse);

                    Session::flash('mensagem', "<p>ARP: " . $arpResponse . "</p><p>DHCP: " . $dhcpResponse . "</p>");
                    Session::flash('tipo', 'Informação');
                }
                else {
                    Session::flash('mensagem', 'A data informada é inválida.');
                    Session::flash('tipo', 'Erro');
                }
            }
            return Redirect::back();
        }
        else abort(401);
    }

    public function forceReload()
    {
        $successful = true;
        $this->loadArpTableFromServer($successful);
        return Redirect::route('home');
    }

    public function getEditMac($id)
    {
        if(UserController::checkLogin()) {
            if(UserController::checkPermissions(1)) {
                return View::make('admin.actions.editMac')->with(['requisicao' => Requisicao::find($id), 'tiposdispositivo' => TipoDispositivo::all(), 'tiposusuario' => TipoUsuario::all(), 'ipsLivre' => $this->getFreeIPs()]);
            }
            else abort(403);
        }
        else return redirect('/login');
    }

    public function doEditMac()
    {
        if(UserController::checkPermissions(1)) {
            $input = Input::all();

            $date = explode('/', $input['validade']);

            if(empty($input['validade']) || checkdate($date[1], $date[0], $date[2]) ) { // Se a data for válida ou em branco
                $record = Requisicao::find($input['id']);
                $record->ip = $input['ip'];
                $record->responsavel = $input['responsavel'];
                $record->responsavelNome = $input['responsavelNome'];
                $record->usuario = $input['usuario'];
                $record->usuarioNome = $input['usuarioNome'];
                $record->mac = $input['mac'];
                $record->descricao_dispositivo = $input['descricao'];
                $record->tipo_dispositivo = $input['tipodispositivo'];
                $record->tipo_usuario = $input['tipousuario'];

                if( empty($input['validade']) ) $record->validade = null;
                else $record->validade = date_create_from_format('d/m/Y', $input['validade'])->format('Y-m-d H:i:s');

                $record->save();

                $this->updateArpAndDhcp($arpResponse, $dhcpResponse);

                Session::flash('mensagem', "<p>ARP: " . $arpResponse . "</p><p>DHCP: " . $dhcpResponse . "</p>");
                Session::flash('tipo', 'Informação');
            }
            else  {
                Session::flash('mensagem', 'A data informada é inválida.');
                Session::flash('tipo', 'Erro');
            }

            return Redirect::back();
        }
        else abort(401);
    }

    public function doAddRequest()
    {
        // MiMe type = application/pdf
        if(UserController::checkLogin()) {
            Session::flash('tipo', 'Erro');
            $form = Input::all();

            if($form['termo']->isValid()) {
                if($form['termo']->getMimeType() == 'application/pdf') {
                    $newRequest = new Requisicao;
                    $newRequest->responsavel = $form['responsavel'];
                    $newRequest->responsavelNome = $form['responsavelNome'];
                    $newRequest->usuario = $form['usuario'];
                    $newRequest->usuarioNome = $form['usuarioNome'];
                    $newRequest->tipo_usuario = $form['tipousuario'];
                    $newRequest->tipo_dispositivo = $form['tipodispositivo'];
                    $newRequest->mac = $form['mac'];
                    $newRequest->termo = $form['termo']->store('termos');
                    $newRequest->descricao_dispositivo = $form['descricao'];
                    $newRequest->justificativa = $form['justificativa'];
                    $newRequest->save();
                    Session::flash('tipo', 'Sucesso');
                    Session::flash('mensagem', 'Seu pedido foi enviado com sucesso. Aguarde pela resposta.');
                    // Redirecionar para a lista de requisições
                }
                else Session::flash('mensagem', 'O arquivo enviado ou não está em formato PDF ou não foi codificado corretamente.');
            }
            else Session::flash('mensagem', 'Ouve um erro durante o envio do arquivo.');

            if(Session::get('tipo') == 'Erro') return Redirect::back()->withInput(Input::all());
            else return Redirect::to('listUserRequests');
        }
        else return redirect('/login');
    }

    public function getListUserRequests()
    {
        if(UserController::checkLogin()) {
            $requests = DB::table('requisicoes')->join('tipo_dispositivo', 'requisicoes.tipo_dispositivo', '=', 'tipo_dispositivo.id')
                ->join('tipo_usuario', 'requisicoes.tipo_usuario', '=', 'tipo_usuario.id')
                ->select('requisicoes.id as id', 'usuarioNome', 'tipo_usuario.descricao as tipousuario', 'tipo_dispositivo.descricao as tipodispositivo', 'submissao', 'avaliacao', 'status')
                ->where('responsavel', Session::get('id'))
                ->get();
            return View::make('admin.actions.listUserRequests')->with('requisicoes', $requests);
        }
        else return redirect('/login');
    }

    public function getAddRequest()
    {
        if(UserController::checkLogin()) {
            if(Session::get('nivel') == 1) {
                $users = TipoUsuario::all();
                $devices = TipoDispositivo::all();
            }
            else {
                $users = TipoUsuario::where('id', '>', 1)->get();
                $devices = TipoDispositivo::where('id', '>', 1)->get();
            }
            return View::make('admin.actions.addRequest')->with(['usuarios' => $users, 'dispositivos' => $devices, 'organizacoes' => Ldapuser::where('nivel', 3)->get()]);
        }
        else return redirect('/login');
    }

    public function showFile($filepath)
    {
        return Response::make(file_get_contents(storage_path('app/' . base64_decode($filepath))), 200, ['Content-Type' => 'application/pdf', 'Content-Disposition' => "inline; filename='termo.pdf'"] );
    }

    public function getListRequests($type)
    {
        if(UserController::checkLogin()) {
            if(UserController::checkPermissions(1)) {
                Session::put('novosPedidos', Requisicao::where('status', '=', 0)->count());
                if(!isset($type)) $type = 0;
                $requests = DB::table('requisicoes')->join('tipo_dispositivo', 'requisicoes.tipo_dispositivo', '=', 'tipo_dispositivo.id')
                    ->join('tipo_usuario', 'requisicoes.tipo_usuario', '=', 'tipo_usuario.id')
                    ->select('requisicoes.id as id', 'responsavelNome', 'usuarioNome', 'tipo_usuario.descricao as tipousuario', 'tipo_dispositivo.descricao as tipodispositivo', 'submissao', 'avaliacao')
                    ->where('status', $type)
                    ->get();
                return View::make('admin.actions.listRequests')->with(['requisicoes' => $requests, 'tipo' => $type]);
            }
            else abort(403);
        }
        else return redirect('/login');
    }

    public function getRequestDetails($id)
    {
        if(UserController::checkLogin()) {
            $freeIPs = null;
            $requisicao = Requisicao::find($id);

            if(Session::get('nivel') == 1 && $requisicao->status == 0) $freeIPs = $this->getFreeIPs();

            return View::make('admin.actions.viewRequestDetails')->with(['requisicao' => $requisicao, 'ipsLivre' => $freeIPs]);
        }
        else return redirect('/login');
    }

    public function doApproveRequest()
    {
        if(UserController::checkPermissions(1)) {
            $date = explode('/', Input::get('validade'));

            if(empty(Input::get('validade')) || checkdate($date[1], $date[0], $date[2]) ) {

                $request = Requisicao::find(Input::get('id'));
                $request->status = 1;
                $request->avaliacao = date("Y-m-d H:i:s", time());
                $request->juizCPF = Session::get('id');
                $request->ip = Input::get('ip');
                if( empty($input['validade']) ) $request->validade = null;
                else $request->validade = date("Y-m-d H:i:s", time());
                $request->save();

                $this->updateArpAndDhcp($arpResponse, $dhcpResponse);

                // enviar e-mail

                Session::flash('tipo', 'Sucesso');
                Session::flash('mensagem', '<p>A requisição foi aprovada.</p><p>Saída do ARP: ' . $arpResponse  . '</p>Saída do DHCPD: ' . $dhcpResponse . '</p>');
            }
            else {
                Session::flash('mensagem', 'A data informada é inválida.');
                Session::flash('tipo', 'Erro');
            }

            return Redirect::back();
        }
        else abort(401);
    }

    public function doDenyRequest()
    {
        if(UserController::checkLogin()) {
            if(UserController::checkPermissions(1)) {
                $requisicao = Requisicao::find(Input::get('id'));
                $requisicao->juizMotivo = Input::get('juizMotivo');
                $requisicao->juizCPF = Session::get("id");
                $requisicao->status = 2;
                $requisicao->save();

                // Mandar e-mail com motivo

                Session::flash('tipo', 'Sucesso');
                Session::flash('mensagem', 'O pedido de liberação do dispositivo foi negado.');

                return redirect('/requests');
            }
            else abort(401);
        }
        else return redirect('/login');
    }

    public function doSuspendRequest()
    {
        if(UserController::checkLogin()) {
            if(UserController::checkPermissions(1)) {
                $requisicao = Requisicao::find(Input::get('id'));
                $requisicao->juizMotivo = Input::get('juizMotivo');
                $requisicao->juizCPF = Session::get("id");
                $requisicao->status = 4;
                $requisicao->avaliacao = date("Y-m-d H:i:s", time());
                $requisicao->save();

                // Mandar e-mail com motivo

                $this->updateArpAndDhcp($arpResponse, $dhcpResponse);

                Session::flash('tipo', 'Sucesso');
                Session::flash('mensagem', "<p>O dispositivo teve o acesso suspenso.</p><p>ARP: " . $arpResponse . "</p><p>DHCP: " . $dhcpResponse . "</p>");

                return Redirect::back()->with('requisicao', $requisicao);
            }
            else abort(401);
        }
        else return redirect('/login');
    }

    public function doDisableRequest()
    {
        if(UserController::checkLogin()) {
            if(UserController::checkPermissions(1)) {
                $requisicao = Requisicao::find(Input::get('id'));
                $requisicao->juizMotivo = Input::get('juizMotivo');
                $requisicao->juizCPF = Session::get("id");
                $requisicao->status = 5;
                $requisicao->avaliacao = date("Y-m-d H:i:s", time());
                $requisicao->save();

                // Mandar e-mail com motivo

                $this->updateArpAndDhcp($arpResponse, $dhcpResponse);

                Session::flash('tipo', 'Sucesso');
                Session::flash('mensagem', "<p>O dispositivo foi desligado da rede.</p><p>ARP: " . $arpResponse . "</p><p>DHCP: " . $dhcpResponse . "</p>");

                return Redirect::to('/listMac/1');
            }
            else abot(401);
        }
        else return redirect('/login');
    }

    public function doReactiveRequest($id)
    {
        if(UserController::checkLogin()) {
            if(UserController::checkPermissions(1)) {
                $requisicao = Requisicao::find($id);
                $requisicao->juizMotivo = null;
                $requisicao->juizCPF = Session::get("id");
                $requisicao->status = 1;
                $requisicao->avaliacao = date("Y-m-d H:i:s", time());
                $requisicao->save();

                // Mandar e-mail com motivo

                $this->updateArpAndDhcp($arpResponse, $dhcpResponse);

                Session::flash('tipo', 'Sucesso');
                Session::flash('mensagem', "<p>O dispositivo teve o acesso reativado.</p><p>ARP: " . $arpResponse . "</p><p>DHCP: " . $dhcpResponse . "</p>");

                return Redirect::back();
            }
            else abort(403);
        }
        else return redirect('/login');
    }

    public function doDeleteRequest()
    {
        if(UserController::checkLogin()) {
            $requisicao = Requisicao::find(Input::get('id'));
            if(UserController::checkPermissions(1) || $requisicao->responsavel == Session::get('id')) {
                if($requisicao->status == 0) {
                    Requisicao::destroy($requisicao->id);
                    Session::flash('tipo', 'Sucesso');
                    Session::flash('mensagem', "A requisição foi apagada.");
                }
                else {
                    Session::flash('tipo', 'Erro');
                    Session::flash('mensagem', "Não é possível deletar uma requisição que já foi julgada.");
                }

                return redirect('/listUserRequests');
            }
            else abort(403);
        }
        else return redirect('/login');
    }

    public function getEditRequest($id)
    {
        if(UserController::checkLogin()) {
            $requisicao = Requisicao::find($id);
            if(UserController::checkPermissions(1) || $requisicao->responsavel == Session::get('id')) {
                if($requisicao->status == 0) {
                    if(Session::get('nivel') == 1) {
                        $users = TipoUsuario::all();
                        $devices = TipoDispositivo::all();
                    }
                    else {
                        $users = TipoUsuario::where('id', '>', 1)->get();
                        $devices = TipoDispositivo::where('id', '>', 1)->get();
                    }
                    return View::make('admin.actions.editRequest')->with(['requisicao' => $requisicao, 'usuarios' => $users, 'dispositivos' => $devices, 'organizacoes' => Ldapuser::where('nivel', 3)->get()]);
                }

                return redirect('/listUserRequests');
            }
            else abort(401);
        }
        else return redirect('/login');
    }

    public function doEditRequest()
    {
        if(UserController::checkLogin()) {
            $requisicao = Requisicao::find(Input::get('id'));
            if(UserController::checkPermissions(1) || $requisicao->responsavel == Session::get('id')) {
                if($requisicao->status == 0) {
                    // pegar variáveis e salvar
                    $form = Input::all();

                    if(Input::has('termo')) {
                        if($form['termo']->isValid()) {
                            if($form['termo']->getMimeType() == 'application/pdf') $requisicao->termo = $form['termo']->store('termos');
                            else {
                                Session::flash('tipo', 'Erro');
                                Session::flash('mensagem', 'O formato do arquivo não é PDF ou não foi bem codificado.');
                            }
                        }
                        else {
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

                    // Enivar e-mail de atualização?

                    return redirect('/listUserRequests');
                }
                else {
                    Session::flash('tipo', 'Erro');
                    Session::flash('mensagem', 'Não é possível editar uma requisição que foi julgada. Edite o dispostivo ao invés disso.');
                }
            }
            else abort(403);
        }
        else return redirect('/login');
    }

    public function getMonthlyActiveUsers($id)
    {
        if ($id == 1) $url = "http://200.239.152.2:8080/trafego-nti/Subnet-1-200.239.152.0.html"; //diário
        elseif ($id == 2) $url = "http://200.239.152.2:8080/trafego-nti/Subnet-2-200.239.152.0.html"; //semanal
        else $url = "http://200.239.152.2:8080/trafego-nti/Subnet-3-200.239.152.0.html"; // mensal e inativos;

        $content = file_get_contents($url);

        if($content != false) {
            $dom = new \DOMDocument;
            $dom->preserveWhiteSpace = false;

            try {
                libxml_use_internal_errors(true); // ignora erros de formatação provenientes do Bandwidthd
                $loadSuccess = $dom->loadHTML($content);
            } catch (Exception $e) {
                $loadSuccess = false;
            }

            if($loadSuccess == true) {
                $rows = $dom->getElementsByTagName('td');
                $frequentUsers = array();

                for($i=10; $rows->item($i) != NULL; $i+=10) {
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
            else {
                $frequentUsers = NULL;
                Session::flash('tipo', 'Erro');
                Session::flash('mensagem', 'O servidor do Bandwidthd não formatou corretamente a sua saída. Verifique a página gerada pelo Bandwidthd');
            }

            return $frequentUsers;
        }
        else {
            Session::flash('tipo', 'Erro');
            Session::flash('mensagem', 'O servidor do Bandwidthd não respondeu a solicitação. Tente novamente em alguns instantes.');
            return NULL;
        }
    }

    public function getUsersList($id)
    {
        if(UserController::checkLogin()) {
            if (UserController::checkPermissions(1)) {

                $frequentUsers = $this->getMonthlyActiveUsers($id);

                if($id < 4) return View::make('admin.actions.listUsers')->with(['id' => $id, 'usuarios' => $frequentUsers]);
                else {
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

                    return View::make('admin.actions.listUsers')->with(['id' => $id, 'usuarios' => $nonFrequentUsers]);
                }
            }
            else abort(403);
        }
        else return redirect('/login');
    }
}
