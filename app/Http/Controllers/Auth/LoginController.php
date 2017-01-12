<?php

namespace App\Http\Controllers\Auth;

use App\Http\Controllers\Controller;
use Illuminate\Foundation\Auth\AuthenticatesUsers;
use App\Ldapuser;
use Auth;
use Event;
use Illuminate\Support\Facades\Config;
use Input;
use GuzzleHttp\Exception\ClientException;
use GuzzleHttp\Exception\RequestException;
use GuzzleHttp\Client;

class LoginController extends Controller
{
    /*
    |--------------------------------------------------------------------------
    | Login Controller
    |--------------------------------------------------------------------------
    |
    | This controller handles authenticating users for the application and
    | redirecting them to your home screen. The controller uses a trait
    | to conveniently provide its functionality to your applications.
    |
    */

    use AuthenticatesUsers;

    /**
     * Where to redirect users after login / registration.
     *
     * @var string
     */
    protected $redirectTo = '/';

    /**
     * Create a new controller instance.
     *
     * @return void
     */
    public function __construct()
    {
        $this->middleware('guest', ['except' => 'logout']);
    }

    /**
     * Renderiza a view de login
     */
    public function showLogin()
    {
        return view('login');
    }

    /**
     * Determina se um usuário é capaz de usar o sistema ou não baseado no seu grupo.
     * @param $group ID do grupo o qual o usuário pertence
     * @return bool True se é autorizado a usar e False caso contrário
     */
    private function isPermitted($group)
    {
        $permitted = false;
        // Se pertencer à algum grupo vinculado ao campus, está liberado
        switch ($group) {
            case 712: // Biblioteca ICEA
            case 714: // ICEA
            case 715: // DECEA
            case 716: // DEENP
            case 7126: // DECOM - Ouro Preto
            case 71130: // DECSI
            case 71481: // DEELT
                $permitted = true;
                break;
        }
        return $permitted;
    }
    /**
     * Realiza o processo de login de usuário.
     */
    public function postLogin() {
        $input = Input::all();

        // Componentes do corpo da requisição
        $requestBody['user'] = $input['username'];
        $requestBody['password'] = $input['password'];
        $requestBody['attributes'] = ["cpf", "nomecompleto", "email", "id_grupo"]; // Atributos que devem ser retornados em caso autenticação confirmada

        // Chamada de autenticação para a LDAPI
        $httpClient = new Client();
        try
        {
            $response = $httpClient->request("POST", "http://200.239.152.5/ldapi/auth", [
                "auth" => [Config::get('ldapi.user'), Config::get('ldapi.password'), "Basic"],
                "body" => json_encode($requestBody),
                "headers" => [
                    "Content-type" => "application/json",
                ],
            ]);
        }
        catch (ClientException $ex) { // Erros relacionados a autenticação
            $credentials['username'] = $input["username"];
            $credentials['password'] = $input['password'];

            // TODO Criar evento de falha de login
            //Event::fire(new LoginFailed($credentials));
            $responseBody = $ex->getResponse()->getBody()->getContents();
            if(is_null($responseBody)) $requestBody = "Erro desconhecido.";

            session()->flash('erro', 1);
            session()->flash('mensagem', $responseBody);

            return redirect()->back();
        }
        catch (RequestException $ex) { // Erros relacionados ao servidor
            session()->flash('mensagem', $ex->getResponse()->getBody()->getContents());
            return redirect()->back();
        }
        // Se nenhuma excessão foi jogada, então o usuário está autenticado
        $user = Ldapuser::where('cpf', $input['username'])->first();
        // Se o usuário é NULL então ou ele não é cadastrado no sistema ainda ou não tem permissão
        if(is_null($user))
        {
            // Recupera os atributos retornados pelo servidor de autenticação
            $userData = json_decode($response->getBody()->getContents());

            //Verificar se ele pertence a algum grupo que é permitido de usar o sistema
            if($this->isPermitted($userData->id_grupo))
            { // Se for permitido, então cria-se um novo usuário
                $user = Ldapuser::create([
                    'cpf' => $userData->cpf,
                    'email' => $userData->email,
                    'nome' => ucwords(strtolower($userData->nomecompleto)),
                    'nivel' => 2,
                    'status' => 1
                ]);

                // TODO Criar evento de usuário criado
                // Event::fire(new NewUserCreated($user));
            }
            else
            {
                session()->flash('mensagem', 'Você não permissão para usar o sistema.');
                return redirect()->route('showLogin');
            }
        }

        // Se o usuário tem status ativo, então realiza-se o login
        if($user->status == 1)
        {
            if(isset($input['remember-me']))  Auth::login($user, true);
            else Auth::login($user);

            return redirect()->intended('/');
        }
        else // Senão retorna para a página de login com mensagem de erro.
        {
            Session::flash('erro', 1);
            Session::flash('mensagem', 'Você não está mais autorizado a usar o sistema.');
            return redirect()->back();
        }
    }
}
