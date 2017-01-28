<!-- Left side column. contains the sidebar -->
<aside class="main-sidebar">

    <!-- sidebar: style can be found in sidebar.less -->
    <section class="sidebar">

        <!-- Sidebar Menu -->
        <ul class="sidebar-menu">
            @can('administrate')
                <li class="header text-center">ADMINISTRAÇÃO</li>

                <li class="@yield('pedidos')"><a href="{{ route('showRequest', 0) }}"><i class="fa fa-legal"></i> <span>Pedidos</span> @if(Session::has('novosPedidos') && Session::get('novosPedidos') > 0) <span class="label label-success pull-right">{{Session::get('novosPedidos')}}</span> @endif</a></li>

                <li class="treeview @if(Route::is('indexTipoDispositivo') || Route::is('createTipoDispositivo') || Route::is('editTipoDispositivo')) active @endif">
                    <a href="#">
                        <i class="fa fa-laptop"></i><span>Dispositivos</span>
                        <i class="fa fa-angle-left pull-right"></i>
                    </a>
                    <ul class="treeview-menu">
                        <li class="@yield('addMac')"><a href="{{ route('showAddDevice') }}"><i class="fa fa-plus"></i> <span>Adicionar</span></a></li>
                        <li class="@yield('listMac')"><a href="{{ route('listDevice', 1)}}"><i class="fa fa-th-list"></i> <span>Listar</span></a></li>
                        <li class="treeview @if(Route::is('indexTipoDispositivo') || Route::is('createTipoDispositivo') || Route::is('editTipoDispositivo')) active @endif">
                            <a href="#">
                                <i class="fa fa-puzzle-piece"></i><span>Tipos</span>
                                <i class="fa fa-angle-left pull-right"></i>
                            </a>
                            <ul class="treeview-menu">
                                <li @if(Route::is('createTipoDispositivo')) class="active" @endif><a href="{{ route('createTipoDispositivo') }}"><i class="fa fa-plus"></i> <span>Adicionar</span></a></li>
                                <li @if(Route::is('indexTipoDispositivo')) class="active" @endif"><a href="{{ route('indexTipoDispositivo') }}"><i class="fa fa-th-list"></i> <span>Listar</span></a></li>
                            </ul>
                        </li>
                    </ul>
                </li>

                <li class="treeview @if(Route::is('indexSubrede') || Route::is('createSubrede')) active @endif">
                    <a href="#">
                        <i class="fa fa-sitemap"></i><span>Subredes</span>
                        <i class="fa fa-angle-left pull-right"></i>
                    </a>
                    <ul class="treeview-menu">
                        <li @if(Route::is('indexSubrede')) class="active" @endif><a href="{{ route('indexSubrede') }}"><i class="fa fa-th-list"></i> <span>Listar subredes</span></a></li>
                        <li @if(Route::is('createSubrede')) class="active" @endif><a href="{{ route('createSubrede') }}"><i class="fa fa-plus"></i> <span>Adicionar subrede</span></a></li>
                    </ul>
                </li>

                <li class="treeview @if(Route::is('createTipoUsuario') || Route::is('indexTipoUsuario') || Route::is('editTipoUsuario')) active @endif">
                    <a href="#">
                        <i class="fa fa-users"></i><span>Usuários</span>
                        <i class="fa fa-angle-left pull-right"></i>
                    </a>
                    <ul class="treeview-menu">
                        <li class="treeview @if(Route::is('createTipoUsuario') || Route::is('indexTipoUsuario') || Route::is('editTipoUsuario')) active @endif">
                            <a href="#">
                                <i class="fa fa-puzzle-piece"></i><span>Tipos de usuários</span>
                                <i class="fa fa-angle-left pull-right"></i>
                            </a>
                            <ul class="treeview-menu">
                                <li @if(Route::is('createTipoUsuario')) class="active" @endif><a href="{{ route('createTipoUsuario') }}"><i class="fa fa-plus"></i> <span>Adicionar</span></a></li>
                                <li @if(Route::is('indexTipoUsuario')) class="active" @endif><a href="{{ route('indexTipoUsuario') }}"><i class="fa fa-th-list"></i> <span>Listar</span></a></li>
                            </ul>
                        </li>
                    </ul>
                </li>

                <li class="treeview">
                    <a href="#">
                        <i class="fa fa-download"></i><span>Exportar</span>
                        <i class="fa fa-angle-left pull-right"></i>
                    </a>
                    <ul class="treeview-menu">
                        <li><a href="{{ route('exportConfig') }}"><i class="fa fa-cogs"></i> <span>Configurações do pfSense</span></a></li>
                    </ul>
                </li>
            @endcan
            <li class="header text-center">MENU</li>
            <li class="treeview @if(Route::is('createRequest') || Route::is('indexUserRequests')) active @endif">
                <a href="#">
                    <i class="fa fa-hand-paper-o"></i><span>Requisições</span>
                    <i class="fa fa-angle-left pull-right"></i>
                </a>

                <ul class="treeview-menu">
                    <li @if(Route::is('createRequest')) class="active" @endif><a href="{{ route('createRequest') }}"><i class="fa fa-plus"></i> <span>Nova requisição</span></a></li>
                    <li @if(Route::is('indexUserRequests')) class="active" @endif><a href="{{ route('indexUserRequests') }}"><i class="fa fa-th-list"></i> <span>Minhas requisições</span></a></li>
                </ul>
            </li>
            <li><a href="{{ route('logout') }}"><i class="fa fa-sign-out" aria-hidden="true"></i><span>Sair</span></a></li>
        </ul>
    </section>
</aside>
