<?php

namespace App\Http\Controllers;

use App\Events\LdapiErrorOnSearch;
use Illuminate\Http\Request;
use App\Http\Requests;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Event;
use Input;
use GuzzleHttp\Exception\ClientException;
use GuzzleHttp\Exception\RequestException;
use GuzzleHttp\Client;
use Illuminate\Support\Facades\Redirect;

class UserController extends Controller
{
    /**
     * Método para retornar uma consulta AJAX para que o administrador possa confirmar
     * os dados do novo usuário que será inserido no banco.
     */
    public function searchPerson() {
        $ldapi_user = env('LDAPI_USER', 'test');
        $ldapi_password = env('LDAPI_PASSWORD', 'test');
        // Componentes do corpo da requisição
        $requestBody['baseConnector'] = "and";
        $requestBody['attributes'] = ["cpf", "nomecompleto", "email", "grupo"]; // Atributos que devem ser retornados em caso autenticação confirmada
        $requestBody['searchBase'] = "ou=People,dc=ufop,dc=br";
        $requestBody['filters'][0] = ["cpf" => ["equals", Input::get('cpf')]];
        // Chamada de autenticação para a LDAPI
        $httpClient = new Client();
        try
        {
            $response = $httpClient->request("POST", "http://200.239.152.5/ldapi/search", [
                "auth" => [$ldapi_user, $ldapi_password, "Basic"],
                "body" => json_encode($requestBody),
                "headers" => [
                    "Content-type" => "application/json",
                ],
            ]);
        } catch (RequestException $ex) {
            // TODO log do erro
            Event::fire(new LdapiErrorOnSearch(Auth::user()));
            return response()->json(['status' => 'danger', 'msg' => 'Erro de conexão com o servidor LDAP.']);
        }
        $result = json_decode($response->getBody()->getContents(), true);
        if($result['count'] != 0)
        {
            $name = $result['result'][0]["nomecompleto"];
            $email = $result['result'][0]["email"];
            $group = $result['result'][0]["grupo"];
            return response()->json(['status' => 'success', 'name' => $name, 'email' => $email, 'group' => $group]);
        }
        else return response()->json(['status' => 'danger', 'msg' => 'Nenhum usuário encontrado com esse CPF.']);
    }

    // TODO Reimplmentar o método de login no Auth/LoginController
    public function middlewareLogin($id)
    {
//        $id = $this->mc_decrypt(base64_decode($id));
//
//        // descriptografar o id
//        $userData = DB::connection('meuicea')->table('sessions')->select('uid', 'cn', 'sn', 'mail', 'gidnumber')->where('id', $id)->first();
//
//
//        // Se os dados do usuários forem nulos é porque a sessão expirou
//        if(is_null($userData)) {
//            abort(412);
//        }
//
//        $userData->uid = $this->mc_decrypt($userData->uid);
//        $userData->cn = $this->mc_decrypt($userData->cn);
//        $userData->sn = $this->mc_decrypt($userData->sn);
//        $userData->mail = $this->mc_decrypt($userData->mail);
//        $userData->gidnumber = $this->mc_decrypt($userData->gidnumber);
//
//        // Verificar o nível de privilégio do usuário
//        $level = $this->permitted($userData->gidnumber, $userData->uid);
//
//        // Colocar a sessão aproriada para o sistema de reserva
//        $name = ucwords(strtolower($userData->cn . ' ' . $userData->sn));
//        $username = explode(" ", $name);
//
//        if($level < 1) {
//            Log::info("Usuário com ID " . $userData->uid . " e nome " . $name . " entrou no sistema através do MEU ICEA e foi redirecionado para a tela de seleção.");
//            return Redirect::route('getVisualizarView');
//        }
//        elseif($level == 4) { // Se for 4 então é o primeiro acesso do usuário, logo será necessário armazená-lo no banco
//            $level = 2;
//            DB::table("ldapusers")->insert(['cpf' => $userData->uid, 'nome' => $name, 'email' => $userData->mail, 'nivel' => $level]);
//        }
//
//        Session::put("username", $username[0] . ' ' . $username[1]);
//        Session::put("id", $userData->uid);
//        Session::put("nome", $name);
//        Session::put("nivel", $level);
//
//        Log::info("Usuário com ID " . $userData->uid . " e nome " . $name . " entrou no sistema através do MEU ICEA.");
//        // Redirecionar para a página principal
//        return Redirect::route('home');
    }
}
