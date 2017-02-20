<?php

namespace App\Http\Controllers\Auth;

use App\Events\LdapiErrorOnLogin;
use App\Events\LoginFailed;
use App\Events\NewUserCreated;
use App\Http\Controllers\Controller;
use App\Http\Requests\LoginRequest;
use App\Ldapuser;
use Illuminate\Foundation\Auth\AuthenticatesUsers;
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
     * @return \Illuminate\Contracts\View\Factory|\Illuminate\View\View View de login
     */
    public function showLogin()
    {
        return view('login');
    }

    /**
     * Determina se um usuário é capaz de usar o sistema ou não baseado no seu grupo.
     * @param int $group ID do grupo o qual o usuário pertence
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
     * @param LoginRequest $request Requisição com os campos validados
     * @return $this|\Illuminate\Http\RedirectResponse Para a página anteriror com mensagem de erro em caso de falha ou para página inicial do sistema em caso de sucesso.
     */
    public function postLogin(LoginRequest $request) {
        $input = $request->all();

        // Retirada dos pontos e hífen do CPF
        $input['username'] = str_replace('.', '', $input['username']);
        $input['username'] = str_replace('-', '', $input['username']);

        // Componentes do corpo da requisição
        $requestBody['user'] = $input['username'];
        $requestBody['password'] = $input['password'];
        $requestBody['attributes'] = ["cpf", "nomecompleto", "email", "id_grupo"]; // Atributos que devem ser retornados em caso autenticação confirmada

        // Chamada de autenticação para a LDAPI
        $httpClient = new Client(['verify' => false]);
        try
        {
            $response = $httpClient->request(config('ldapi.requestMethod'), config('ldapi.authUrl'), [
                "auth" => [config('ldapi.user'), config('ldapi.password'), "Basic"],
                "body" => json_encode($requestBody),
                "headers" => [
                    "Content-type" => "application/json",
                ],
            ]);
        }
        catch (ClientException $ex) { // Erros relacionados a autenticação
            $credentials['username'] = $input["username"];
            $credentials['password'] = $input['password'];

            event(new LoginFailed($credentials));

            return back()->withErrors(['username' => $ex->getMessage()]);
        }
        catch (RequestException $ex) { // Erros relacionados ao servidor
            $credentials['username'] = $input["username"];
            $credentials['password'] = $input['password'];

            event(new LdapiErrorOnLogin($credentials));

            return back()->withErrors(['username' => $ex->getMessage()]);
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

                event(new NewUserCreated($user));
            }
            else return back()->withErrors(['username' => 'Você não tem permissão para usar o sistema.']);
        }

        // Se o usuário tem status ativo, então realiza-se o login
        if($user->status == 1)
        {
            if(isset($input['remember-me']))  auth()->login($user, true);
            else auth()->login($user);

            return redirect()->intended(secure_url('/'));
        }
        else return back()->withErrors(['username' => 'Você não está mais autorizado a usar o sistema.']);
    }
}
