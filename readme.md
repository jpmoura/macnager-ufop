# MACnager - Sistema de Gerenciamento de Rede


O [MACnager](http://200.239.152.5/macnager/public)
é o sistema usado pelos corpos acadêmico e administrativo presentes no
[Instituto de Ciências Exatas e Aplicadas (*campus* João Monlevade)](http://www.icea.ufop.br)
da [Universidade Federal de Ouro Preto](http://ufop.br) para o gerenciamento
dos IP reais pertencentes ao *campus*. O objetivo da criação deste sistema visa
facilitar o gerenciamento dos dispositivos integrantes a rede, onde cada um
utiliza um IP real, evitando a manipulação e manutenção direta de diversos
arquivos de configuração de servidores *Firewall* e DHCP bem como a facilitar
a geração e manipulação de informações sobre esses dispositivos. Outro motivo
foi a possibilidade de histórico da relação usuário e endereço IP atribuído ao
mesmo em um determinado intervalo de tempo.

Sua necessidade surgiu dada a dificuldade de se gerenciar todos os dispositivos
e arquivos de configurações entre os servidores de Firewall e DHCP, onde cada
inserção ou remoção eram bastante custosas, necessitando edição de linhas em
diferentes arquivos e execuções de comandos em diferentes servidores com o
objetivo de proibir ou conceder acesso a um dado dispositivo. Existia
também a dificuldade de se verificar se um dispositivo já fazia parte da rede ou
não e de monitorar a sua utilização da rede. Outra dificuldade era a requisição
de inclusão de um dispositivo por parte dos discentes, que necessitavam
desloca-se até a sala dos administradores da rede e entregar o termo de
compromisso em mãos para que pudessem ter sua requisição atendida.

O sistema foi desenvolvido usando a versão 5.3 do *framework* [Laravel](https://laravel.com/)
para aplicações web, um dos mais usados no mercado durante o período de
desenvolvimento.

## Funcionamento

O sistema se baseia que existem dois servidores, um que provê a interface de
*Firewall* e outra que provê os parâmetros de conexão, o DHCP. O servidor
de *Firewall* é o responsável pela liberação do acesso do dispositivo a
Internet enquanto o DHCP provê as configurações necessárias

Os usuários de nível docente podem  solicitar novas conceções de endereços IP
para utilizar nos recursos adquiridos ou distribuir entre alunos bolsistas de
projetos e/ou laboratórios. Podem também, caso sejam de cargo temporário, também
solicitar a integração de sesus dispositivos a rede, porém com uma validade
máxima de 2 anos a partir da data de aprovação da requisição. Não existe limite
de quantidade de dispositivos por pessoa, cabendo aos administradores do sistema
julgar se a requisição será aceita ou não.

Aos administradores, que correspondem aos técnicos do Núcleo de Tecnologia da
Informação, cabe a tarefa de julgar toda e qualquer requisição feita mas tendo
a opção de manipular os dados da mesma antes ou depois da aprovação mas nunca
depois de uma negação ou desativação do dispositivo.

Para autenticação foi usado a mesma base de dados LDAP utilizada pelo sistema
[Minha UFOP](http://www.minha.ufop.br), facilitando o uso para o usuário fazendo
com que ele não precise de um *login* e senha específicos para utilizar o sistema.

Para o layout, foi usado como base o design [AdminLTE](https://almsaeedstudio.com/themes/AdminLTE/documentation/index.html)
desenvolvido por [Abdullah Almsaeed](mailto:abdullah@almsaeedstudio.com),
alterando-se basicamente só a palheta de cores do tema.

## Instalação
Para instalação é necessário ter o gerenciador de dependências [Composer](https://getcomposer.org/)
instalado, de preferência globalmente, e a partir dele usar o comando de
instalação na raiz do projeto:

```bash
composer install
```

Para usuários de sistemas UNIX, será necessário conceder permissão de leitura,
gravação e execução da pasta em que se encontra o sistema para o grupo
*www-data* que pode ser dado pelo seguinte comando usando a permissão de
administrador:

```bash
chown -R www-data:USUARIO_DO_SISTEMA PASTA_DE_DESTINO
```

Basta usar o comando *sudo* ou *su* dependendo da distribuição *Linux*
juntamente com este comando. Além disso, no arquivo de ambiente .ENV é necessário
criar as seguintes a seguir, onde elas devem representar o endereço do servidor,
o usuário e senha do mesmo em cada um deles, tanto Firewall como DHCP:

* FIREWALL_HOST
* FIREWALL_USER
* FIREWALL_PASS
* DHCP_HOST
* DHCP_USER
* DHCP_PASS

A estrutura do banco de dados usada pelo sistema pode ser criada a partir do
script SQL encontrado [aqui](./DUMP_bdarpicea.sql). Além disso é necessário
configurar as variáveis de ambiente do Laravel a partir do arquivo na raiz do
projeto sem nome mas de extensão ENV. Existe um arquivo de exemplo
[aqui](./.env.example) que pode ser editado e depois renomeado apropriadamente
apenas para .env onde nele deve-se encontrar o endereço, senha, usuário e nome
da base do banco de dados além dos endereços, usuários e senhas dos servidores
de Firewall e DHCP.

## Erros conhecidos

* Após retirada das requisições com prazo de validade vencido, não é gerado nem
enviado os novos arquivos de configuração da tabela ARP e DHCP aos servidores;

## TODO

* Permitir a importação dos arquivos de configuração;
* Permitir que as configurações dos bancos possam ser alteradas sem a
necessidade de manipulação direta do arquivo .ENV;
* Otimizar a renderização da página para tabelas com grande quantidade de dados
(tabelas de pedidos e de dispositivos ativos);
* Utilizar middlewares de autenticação e autorização nativos do Laravel;
* Otimização do carregamento dos elementos CSS e Javascript usando Gulp
juntamente com SASS ou LESS.
