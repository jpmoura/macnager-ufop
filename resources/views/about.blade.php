@extends('admin.admin_base')

@section('content')
  <div class="row">
    <div class="col-md-12">
      <img src="{{asset('/img/stackholders.png')}}" alt="Stackholders do Sistema" class="center-block img-responsive"/>
    </div>
  </div>
  <br />
  <div class="row">
    <div class="col-md-6 col-md-offset-3">
      <p class="text-justify">
        O <span class="text-bold">MAC</span>nager é o sistema da <a target="_blank" href="http://ufop.br">Universidade Federal de Ouro Preto</a> do
        <a taget="_blank" href="http://www.icea.ufop.br/site/"><em>campus</em> João Monlevade</a> usado por professores para fazer requisições de inclusão de dispositivos
        à rede da <a>UFOP</a> e acompanhar o status daquelas que já foram feitas. É usado também pelo NTI do <em>campus</em> para gerenciar a rede do instituto, podendo acompanhar
        todos os pedidos e suas características, podendo facilmente suspender/reativar dispostivos bem como desligá-los ou adicionar a rede a qualquer momento. Existe também uma
        integração com o <a href="http://bandwidthd.sourceforge.net/" target="_blank">Bandwidthd</a>, que rastreia todo tráfego na rede que passa pelo servidor de Firewall,
        onde é possível verificar a quantidade que cada dispositvo transferiu.

        <br />

        O novo sistema foi feito pelo bolsista <a target="_black" href="https://github.com/jpmoura">João Pedro Santos de Moura</a> e sua necessidade surgiu dada a dificuldade
        de se gerenciar todos os dispositivos e arquivos de configurações entre os servidores de Firewall e DHCP, onde cada inserção ou remoção eram bastante custosas, necessitando
        edição de linhas em diferentes arquivos e execuções de comandos em diferentes servidores com o objetivo de proibir ou conceder acesso a um dado dispositivo. Existia
        também a dificuldade de se verificar se um dispositivo já fazia parte da rede ou não e de monitorar a sua utilização da rede. Outra dificuldade era a requisição de inclusão
        de um dispositivo por parte dos discentes, que necessitavam desloca-se até a sala dos administradores da rede e entregar o termo de compromisso em mãos para que pudessem ter
        sua requisição atendida.

        <br />

        O sistema foi desenvolvido usando a versão 5.3 do <em>Framework</em> <a href="https://laravel.com/" target="_blank">Laravel</a> para aplicações web, um dos mais usados no mercado durante o
        período de desenvolvimento.

        <h3 class="text-center">A Fazer</h3>
        <ul>
          <li>Otimizar carregamento de objetos javascript e CSS usando SASS ou LESS;</li>
          <li>Corrigir comando para enviar a nova tabela ARP para o servidor após ela ser atualizada pelo rotina que retira requisições vencidas.</li>
        </ul>
      </p>
    </div>
  </div>
@endsection
