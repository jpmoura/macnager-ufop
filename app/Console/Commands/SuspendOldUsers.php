<?php

namespace App\Console\Commands;

use App\Events\RequestExpired;
use App\Http\Controllers\PfsenseController;
use App\Ldapuser;
use App\Mail\RequestExcluded;
use Illuminate\Console\Command;
use App\Requisicao;
use Illuminate\Support\Facades\Event;
use Illuminate\Support\Facades\Mail;
use Log;

class SuspendOldUsers extends Command
{
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'suspend:oldusers';

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = 'Suspende o acesso baseado na validade presente na requisição';

    /**
     * Create a new command instance.
     *
     * @return void
     */
    public function __construct()
    {
        parent::__construct();
    }

    /**
     * Execute the console command.
     *
     * @return mixed
     */
    public function handle()
    {
        $rebuild = false;
        $activeRequests = Requisicao::where('status', 1)->whereNotNull('validade')->get();
        $today = date("Y-m-d H:i:s", time());

        foreach ($activeRequests as $request)
        {
            if($today >= $request->validade)
            {
                $request->status = 3;
                $request->avaliacao = $today;
                $request->juizCPF = '00000000001';
                $request->juizMotivo = 'Retirada automática. A data de validade da requisição expirou';
                $request->save();
                $this->info('O acesso de ' . $request->usuarioNome . ' foi suspenso.');

                event(new RequestExpired($request));

                $user = Ldapuser::where('cpf', $request->responsavel)->first();
                if(isset($user) && isset($user->email)) Mail::to($user->email)->queue(new RequestExcluded($user, $request));

                Log::info('O acesso de ' . $request->usuarioNome . ' através do IP '. $request->ip . ' e MAC ' . $request->mac . ' foi suspenso.');
                $rebuild = true;
            }
        }

        $this->line('Comando de suspensão executado.');

        if($rebuild) PfsenseController::refreshPfsense();

        return;
    }
}
