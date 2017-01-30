<?php

namespace App\Providers;

use App\Ldapuser;
use App\Requisicao;
use Illuminate\Support\Facades\Gate;
use Illuminate\Foundation\Support\Providers\AuthServiceProvider as ServiceProvider;

class AuthServiceProvider extends ServiceProvider
{
    /**
     * The policy mappings for the application.
     *
     * @var array
     */
    protected $policies = [
        'App\Model' => 'App\Policies\ModelPolicy',
    ];

    /**
     * Register any authentication / authorization services.
     *
     * @return void
     */
    public function boot()
    {
        $this->registerPolicies();

        // Define se um usuário pode administrar o sistema
        Gate::define('administrate', function ($user) {
            return $user->nivel == 1;
        });

        // Define se um usuário é capaz de manipular (editar/apagar) uma determinada instância de Requisicao
        Gate::define('manipulateRequisicao', function (Ldapuser $user, Requisicao $requisicao) {
            if($requisicao->status == 0 && ($user->isAdmin() || ($requisicao->responsavel == $user->cpf))) return true;
            else return false;
        });
    }
}
