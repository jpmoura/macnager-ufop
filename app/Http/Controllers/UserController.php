<?php

namespace App\Http\Controllers;

use App\Events\LdapiErrorOnSearch;
use App\Http\Requests\CreateLdapuserRequest;
use App\Http\Requests\EditLdapuserRequest;
use App\Ldapuser;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Event;
use GuzzleHttp\Exception\RequestException;
use GuzzleHttp\Client;

class UserController extends Controller
{
    /**
     * Procura por um usuário de um determinado CPF no banco de dados local e pela LDAPI.
     * @param Request $request Requisição AJAX feita pela página.
     * @return \Illuminate\Http\JsonResponse Resposta contendo os valores de nome, email e grupo do usuário ou uma resposta de erro se não for encontrado
     */
    public function searchPerson(Request $request)
    {

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
            event(new LdapiErrorOnSearch(auth()->user()));
            return response()->json(['status' => 'danger', 'msg' => 'Erro de conexão com o servidor LDAP.']);
        }

        $result = json_decode($response->getBody()->getContents(), true);

        if ($result['count'] != 0)
        {
            $name = $result['result'][0]["nomecompleto"];
            $email = $result['result'][0]["email"];
            $group = $result['result'][0]["grupo"];
            return response()->json(['status' => 'success', 'name' => $name, 'email' => $email, 'group' => $group]);
        }
        else
        {
            $user = Ldapuser::where('cpf', $cpf)->first();

            if (isset($user))
            {
                $name = $user->nome;
                $email = $user->email ? $user->email : 'Sem e-mail cadastrado';
                $group = 'Organização do ICEA';
                return response()->json(['status' => 'success', 'name' => $name, 'email' => $email, 'group' => $group]);
            }
            else return response()->json(['status' => 'danger', 'msg' => 'Nenhum usuário encontrado com esse CPF.']);
        }
    }

    /**
     * Renderiza a view com o índice de todos os usuários administradores e comuns
     */
    public function index()
    {
        return view('ldapuser.index')->with('usuarios', Ldapuser::where('nivel', '<>', 3)->get());
    }

    /**
     * Renderiza a view contendo o formulário para criação de um novo usuário.
     *
     * @return \Illuminate\Contracts\View\Factory|\Illuminate\View\View
     */
    public function create()
    {
        return view('ldapuser.create');
    }

    public function store(CreateLdapuserRequest $request)
    {
        try
        {
            Ldapuser::create([
                'nome' => ucwords(strtolower($request->get('nome'))),
                'email' => $request->get('email'),
                'cpf' => $request->get('cpf'),
                'nivel' => $request->get('nivel'),
                'status' => 1,
            ]);

            session()->flash('tipo', 'success');
            session()->flash('mensagem', 'Usuário criado com sucesso.');
        }
        catch (\Exception $e)
        {
            session()->flash('tipo', 'error');
            session()->flash('mensagem', $e->getMessage());
        }

        return redirect()->route('ldapuser.index');
    }

    /**
     * Renderiza a view com o formulário para edição de uma instância de Ldapuser
     *
     * @param Ldapuser $ldapuser Instância a ser editada
     */
    public function edit(Ldapuser $ldapuser)
    {
        return view('ldapuser.edit')->with('usuario', $ldapuser);
    }

    /**
     * Realiza a modificação de uma instância de Ldapuser
     *
     * @param EditLdapuserRequest $request Requisiçào com as entradas do formulário já validadas
     * @param Ldapuser $usuario Instância a ser modificada
     * @return \Illuminate\Http\RedirectResponse Rota de índice dos usuários
     */
    public function update(EditLdapuserRequest $request, Ldapuser $usuario)
    {
        try
        {
            $usuario->update([
                'email' => $request->get('email'),
                'nivel' => $request->get('nivel'),
                'status' => $request->get('status'),
            ]);

            session()->flash('tipo', 'success');
            session()->flash('mensagem', 'Usuário editado com sucesso.');
        }
        catch(\Exception $e)
        {
            session()->flash('tipo', 'error');
            session()->flash('mensagem', $e->getMessage());
        }

        return redirect()->route('ldapuser.index');
    }

    /**
     * Bloqueia o acesso de uma instância de Ldapuser
     *
     * @param Ldapuser $ldapuser Instância a ser bloqueada
     * @return \Illuminate\Http\RedirectResponse Rota de índice de usuários
     */
    public function destroy(Ldapuser $ldapuser)
    {
        try
        {
            $ldapuser->update(['status' => 0]);
            session()->flash('tipo', 'success');
            session()->flash('mensagem', 'Usuário desativado com sucesso.');
        }
        catch (\Exception $e)
        {
            session()->flash('tipo', 'error');
            session()->flash('mensagem', $e->getMessage());
        }

        return redirect()->route('ldapuser.index');
    }
}
