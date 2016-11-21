<?php

namespace App\Console\Commands;

use Illuminate\Console\Command;
use App\Requisicao;
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
      $activeRequests = Requisicao::where('status', 1)->where('validade', '<>', null)->get();
      $today = date("Y-m-d H:i:s", time());

      foreach ($activeRequests as $request) {
        if($today >= $request->validade) {
          $request->status = 3;
          $request->avaliacao = $today;
          $request->juizMotivo = 'Data de validade da concessão expirou.';
          $request->save();
          $this->info('O acesso de ' . $request->usuarioNome . ' foi suspenso.');
          Log::info('O acesso de ' . $request->usuarioNome . ' através do IP '. $request->ip . ' e MAC ' . $request->mac . ' foi suspenso.');
        }
      }

      $this->line('Comando de suspensão executado.');

      // Enviar e-mail de suspensão
      // executar arp e dhcp (controller)

      return;

    }
}
