<?php

namespace App\Http\Controllers;

use App\Events\LdapiErrorOnSearch;
use App\Ldapuser;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Event;
use GuzzleHttp\Exception\RequestException;
use GuzzleHttp\Client;

class UserController extends Controller
{
    /**
     * Método para retornar uma consulta AJAX para que o administrador possa confirmar
     * os dados do novo usuário que será inserido no banco.
     */
    public function searchPerson(Request $request) {

        // Tratamento da entrada de CPF
        $cpf = $request->input('cpf');
        $cpf = str_replace('.', '', $cpf);
        $cpf = str_replace('-', '', $cpf);

        // Componentes do corpo da requisição
        $requestBody['baseConnector'] = "and";
        $requestBody['attributes'] = ["cpf", "nomecompleto", "email", "grupo"]; // Atributos que devem ser retornados em caso autenticação confirmada
        $requestBody['searchBase'] = "ou=People,dc=ufop,dc=br";
        $requestBody['filters'][0] = ["cpf" => ["equals", $cpf]];

        // Chamada de autenticação para a LDAPI
        $httpClient = new Client(['verify' => false]);
        try
        {
            $response = $httpClient->request(config('ldapi.requestMethod'), config("ldapi.searchUrl"), [
                "auth" => [config('ldapi.user'), config('ldapi.password'), "Basic"],
                "body" => json_encode($requestBody),
                "headers" => [
                    "Content-type" => "application/json",
                ],
            ]);
        }
        catch (RequestException $ex)
        {
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
        else {
            $user = Ldapuser::where('cpf', $cpf)->first();

            if(isset($user))
            {
                $name = $user->nome;
                $email = $user->email;
                $group = 'Organização do ICEA';
                return response()->json(['status' => 'success', 'name' => $name, 'email' => $email, 'group' => $group]);
            }
            else return response()->json(['status' => 'danger', 'msg' => 'Nenhum usuário encontrado com esse CPF.']);
        }
    }
}
