<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;

use App\Http\Requests;
use App\Http\Controllers\UserController;
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

class RequisicaoController extends Controller
{

  private function loadArpTableFromServer($successful) {
    $serverArp = '/usr/local/etc/arp_icea';
    $localArp = '/var/www/html/macnager/storage/app/public/arp_icea';

    $this->tryToDelete($localArp, $successful);
    SSH::into('firewall')->get('/usr/local/etc/arp_icea', '/var/www/html/macnager/storage/app/public/arp_icea');

    $this->parseArpTable($localArp);

    return;
  }

  private function parseArpTable($file)
  {

    DB::table('requisicoes')->truncate();

    $handle = fopen($file, "r");
    if($handle)
    {
      while(($line = fgets($handle)) !== false)
      {
        // Ignorar linhas de comentário, em branco ou quebras de linha
        if($line == '' || $line[0] == '#' || $line[0] == '\n') continue;
        else
        {
          $chunks = explode(" ", $line); // divide a linha usando espaço em branco como separor
          if(count($chunks) < 2) continue; // se encontrar só duas porções, reinicia o loop pois a linha deve ser considerada
          else {
            if($chunks[0] == '') continue; // se o primeiro token for um espaço nulo, também pode desconsiderar a linha
            else
            {
              $ip = $chunks[0]; // Ip é o primeiro token no arquivo
              $mac = strtoupper($chunks[1]); // Mac é o segundo
              $totalChunks = count($chunks); // Obtém a quantidade de tokens encontrados

              if($totalChunks > 2) // Caso acha mais tokens, eles são de descrição
              {
                $descricao = ''; // Inicializa a descrição em branco
                $temp = array_slice($chunks, 2, $totalChunks); // Recupera o restante da linha
                foreach ($temp as $descChunk) $descricao = $descricao . $descChunk; // Transforma o array em uma string
                $descricao = str_replace(' ', '', $descricao); // Retira espaços em branco
                $descricao = str_replace('/n', '', $descricao); // Retira fim de linha
                $descricao = str_replace('#', '', $descricao); // Retira caracter especial de comentário
                $descricao = trim($descricao); // Retira possíveis espaços em branco e quebras de linha
                $descricao = utf8_encode($descricao); // Codifica para utf8
              }
              else $descricao = "Nao Identificado";

              // Adiciona uma requisição já automaticamente aprovada
              $newAllowed = new Requisicao;
              $newAllowed->responsavel = '00000000000';
              $newAllowed->responsavelNome = 'NTI';
              $newAllowed->usuario = '00000000000';
              $newAllowed->usuarioNome = 'NTI';
              $newAllowed->tipo_usuario = 1;
              $newAllowed->tipo_dispositivo = 1;
              $newAllowed->mac = $mac;
              $newAllowed->termo = 'termos/default.pdf';
              $newAllowed->descricao_dispositivo = $descricao;
              $newAllowed->justificativa = 'Criado a partir do arquivo arp_icea';
              $newAllowed->submissao = date("Y-m-d H:i:s", time());
              $newAllowed->avaliacao = date("Y-m-d H:i:s", time());
              $newAllowed->juizCPF = '00000000000';
              $newAllowed->juizMotivo = "Aprovação automática (legado)";
              $newAllowed->ip = $ip;
              $newAllowed->status = 1;
              $newAllowed->save();
            }

          }
        }
      }
      fclose($handle);
    }
    else echo "Error opening the file";
    return;
  }

  // Tenta deletar um determinado arquivo em 'attempts' vezes
  private function tryToDelete($file, $successful, $attempts = 10) {
    $status = true;
    while(File::exists($file) && $attempts > 0) File::delete($file);
    return !File::exists($file);
  }

  // Gera a tabela ARP de whitelist do firewall, envia para o servidor e reinicia o serviço
  public function updateArp()
  {
    $successful = true;
    $this->generate('arp', $successful); // gera o arquivo
    $this->result = '';
    SSH::into('firewall')->run('cp /usr/local/etc/arp_icea /usr/local/etc/arp_icea.backup');
    SSH::into('firewall')->put('/var/www/html/macnager/storage/app/public/temp_arp', '/usr/local/etc/arp_icea'); // transfere o arquivo
    SSH::into('firewall')->run('sudo arp -f /usr/local/etc/arp_icea', function($line) { $this->result = $this->result . $line . "<br />"; } ); // recarrega a whitelist do Firewall
    return $this->result;
  }

  // Gera o arquivo DHCP, envia para o servidor e reinicia o serviço
  public function updateDhcp()
  {
    $successful = true;
    $this->generate('dhcp', $successful); // gera o arquivo
    $this->result = '';
    SSH::into('dhcp')->run('cp /usr/local/etc/dhcp.conf /usr/local/etc/dhcpd.conf.backup');
    SSH::into('dhcp')->put('/var/www/html/macnager/storage/app/public/temp_dhcp', '/usr/local/etc/dhcpd.conf'); // transfere o arquivo
    $result = SSH::into('dhcp')->run('/usr/local/etc/rc.d/isc-dhcpd restart', function($line) { $this->result = $this->result. $line . "<br />"; }); // reinicia o servidor DHCP
    return $result;
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

  private function generate($conf, $successful)
  {
    //$requestsAllowed = DB::table('requisicoes')->select()->where('status', 1)->orderBy(DB::raw('INET_ATON(ip)'))->get();
    $requestsAllowed = Requisicao::where('status', 1)->orderBy(DB::raw('INET_ATON(ip)'))->get();

    // Verifica qual é o arquivo a ser gerado
    if($conf == 'dhcp') $tempFile = '/var/www/html/macnager/storage/app/public/temp_dhcp';
    else if($conf == 'arp') $tempFile = '/var/www/html/macnager/storage/app/public/temp_arp';
    else return false;

    $this->tryToDelete($tempFile, $successful);

    if($conf == 'dhcp') { // cabeçalho dhcp
      $header = "option domain-name-servers " . PHP_EOL . PHP_EOL .
      "189.38.95.95,189.38.95.96,8.8.8.8,8.8.4.4;" . PHP_EOL . PHP_EOL .
      "default-lease-time 99999999;" . PHP_EOL . PHP_EOL .
      "max-lease-time 99999999;" . PHP_EOL . PHP_EOL .
      "log-facility local7;" . PHP_EOL . PHP_EOL .
      "subnet 200.239.152.0 netmask 255.255.252.0 {" . PHP_EOL .
        "\toption routers 200.239.152.2;" . PHP_EOL .
        "}" . PHP_EOL . PHP_EOL;
        File::append($tempFile, $header);
    }

    if($successful == true)
    {
      // Itera sobre todos os registros, formatando-os para a escrita
      foreach ($requestsAllowed as $user)
      {
        if($conf == 'dhcp') $formattedUser = $this->formatToDhcp($user);
        else $formattedUser = $this->formatToArp($user);

        $successful = File::append($tempFile, $formattedUser);
        if($successful == false) break; // Se a escrita falhar, para o processo
      }
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

  public function getIndex()
  {

    if(UserController::checkLogin()) {

      if(Session::get('nivel') == 1) {
        for ($faixa=152; $faixa < 156; $faixa++) {
          $count = Requisicao::where('ip', 'like', '200.239.' . $faixa . '.%')->where('status', 1)->count();
          $used[$faixa] = $count;
        }

        Session::put('novosPedidos', Requisicao::where('status', '=', 0)->count());

        return View::make('index')->with(['faixa' => $used]);
      }
      else {
        $accepted = Requisicao::where('status', 1)->where('responsavel', Session::get('id'))->count();
        $rejected = Requisicao::where('status', 2)->where('responsavel', Session::get('id'))->count();
        $oudated = Requisicao::where('status', 3)->where('responsavel', Session::get('id'))->count();
        $blocked = Requisicao::where('status', 4)->where('responsavel', Session::get('id'))->count();

        return View::make('index')->with(['aceitas' => $accepted, 'rejeitadas' => $rejected, 'vencidas' => $oudated, 'bloqueadas' => $blocked]);
      }

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

          $responseArp = $this->updateArp();
          $responseDhcp = $this->updateDhcp();

          Session::flash('mensagem', "<p>ARP: " . $responseArp . "</p><p>DHCP: " . $responseDhcp . "</p>");
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

        //$responseArp = $this->updateArp();
        //$responseDhcp = $this->updateDhcp();

        //Session::flash('mensagem', "<p>ARP: " . $responseArp . "</p><p>DHCP: " . $responseDhcp . "</p>");
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

        $arpResult = $this->updateArp();
        $dhcpResult = $this->updateDhcp();

        // enviar e-mail

        Session::flash('tipo', 'Sucesso');
        Session::flash('mensagem', '<p>A requisição foi aprovada.</p><p>Saída do ARP: ' . $arpResult . '</p>Saída do DHCPD: ' . $dhcpResult . '</p>');
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

        $responseArp = $this->updateArp();
        $responseDhcp = $this->updateDhcp();

        Session::flash('tipo', 'Sucesso');
        Session::flash('mensagem', "<p>O dispositivo teve o acesso suspenso.</p><p>ARP: " . $responseArp . "</p><p>DHCP: " . $responseDhcp . "</p>");

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

        $responseArp = $this->updateArp();
        $responseDhcp = $this->updateDhcp();

        Session::flash('tipo', 'Sucesso');
        Session::flash('mensagem', "<p>O dispositivo foi desligado da rede.</p><p>ARP: " . $responseArp . "</p><p>DHCP: " . $responseDhcp . "</p>");

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

        $responseArp = $this->updateArp();
        $responseDhcp = $this->updateDhcp();

        Session::flash('tipo', 'Sucesso');
        Session::flash('mensagem', "<p>O dispositivo teve o acesso reativado.</p><p>ARP: " . $responseArp . "</p><p>DHCP: " . $responseDhcp . "</p>");

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

  public function getMonthlyActiveUsers()
  {
    $content = file_get_contents("http://200.239.152.2:8080/trafego-nti/Subnet-3-200.239.152.0.html");

    if($content != false) {
      $dom = new \DOMDocument;
      $dom->preserveWhiteSpace = false;
      $dom->loadHTML($content);
      $rows = $dom->getElementsByTagName('td');
      $frequentUsers = array();
      $frequentUsersTransfers = array();

      for($i=10; $rows->item($i) != NULL; $i+=10) {
        // $rows->item(11)->nodeValue; // Total
        // $rows->item(12)->nodeValue; // Total Sent
        // $rows->item(13)->nodeValue; // Total Received
        // $rows->item($i)->nodeValue; // IP

        $user = Requisicao::where('ip', $rows->item($i)->nodeValue)->where('status', 1)->first();
        if(!is_null($user)) {
          $user['totalTransferred'] = $rows->item($i + 1)->nodeValue;
          $user['sent'] = $rows->item($i + 2)->nodeValue;
          $user['received'] = $rows->item($i + 3)->nodeValue;
          array_push($frequentUsers, $user);
        }
      }

      return $frequentUsers;
    }
    else return NULL;
  }

  public function getUsersList($id)
  {
    if(UserController::checkLogin()) {
      if (UserController::checkPermissions(1)) {
        $frequentUsers = $this->getMonthlyActiveUsers();

        if($frequentUsers == NULL) {
          Session::flash('tipo', 'Erro');
          Session::flash('mensagem', 'O servidor do Bandwidthd não respondeu a solicitação. Tente novamente em alguns instantes.');
          $id = 1;
        }

        if($id == 1) return View::make('admin.actions.listUsers')->with(['id' => $id, 'usuarios' => $frequentUsers]);
        else {
          //pegar os usuarios que tem status == 1 mas não tem o ip na lista
          $frequentIPs = array();

          // Obtém todos os IPs frequentes
          foreach ($frequentUsers as $user) array_push($frequentIPs, $user->ip);

          //Obtém todos os usuários aprovados que não estão na lista de frequentes
          $nonFrequentUsers = Requisicao::where('status', 1)->whereNotIn('ip', $frequentIPs)->get();

          return View::make('admin.actions.listUsers')->with(['id' => $id, 'usuarios' => $nonFrequentUsers]);
        }
      }
      else abort(403);
    }
    else return redirect('/login');
  }
}
