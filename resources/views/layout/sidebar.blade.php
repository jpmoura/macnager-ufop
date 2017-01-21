<!-- Left side column. contains the sidebar -->
<aside class="main-sidebar">

    <!-- sidebar: style can be found in sidebar.less -->
    <section class="sidebar">

        <!-- Sidebar Menu -->
        <ul class="sidebar-menu">
            @can('administrate')
                <li class="header text-center">ADMINISTRAÇÃO</li>

                <li class="@yield('pedidos')"><a href="{{ route('showRequest', 0) }}"><i class="fa fa-legal"></i> <span>Pedidos</span> @if(Session::has('novosPedidos') && Session::get('novosPedidos') > 0) <span class="label label-success pull-right">{{Session::get('novosPedidos')}}</span> @endif</a></li>

                <li class="treeview @yield('dispositivo')">
                    <a href="#">
                        <i class="fa fa-laptop"></i><span>Dispositivos</span>
                        <i class="fa fa-angle-left pull-right"></i>
                    </a>
                    <ul class="treeview-menu">
                        <li class="@yield('addMac')"><a href="{{ route('showAddDevice') }}"><i class="fa fa-plus"></i> <span>Adicionar</span></a></li>
                        <li class="@yield('listMac')"><a href="{{ route('listDevice', 1)}}"><i class="fa fa-th-list"></i> <span>Listar</span></a></li>
                        <li class="treeview @yield('tipodispositivo')">
                            <a href="#">
                                <i class="fa fa-puzzle-piece"></i><span>Tipos</span>
                                <i class="fa fa-angle-left pull-right"></i>
                            </a>
                            <ul class="treeview-menu">
                                <li class="@yield('addDeviceType')"><a href="{{ route('showAddDeviceType') }}"><i class="fa fa-plus"></i> <span>Adicionar</span></a></li>
                                <li class="@yield('listDeviceType')"><a href="{{ route('listDeviceType') }}"><i class="fa fa-th-list"></i> <span>Listar</span></a></li>
                            </ul>
                        </li>
                    </ul>
                </li>

                <li class="treeview">
                    <a href="#">
                        <i class="fa fa-sliders"></i><span>Faixas</span>
                        <i class="fa fa-angle-left pull-right"></i>
                    </a>
                    <ul class="treeview-menu">
                        <li><a href="{{ route('exportConfig') }}"><i class="fa fa-th-list"></i> <span>Listar Faixas</span></a></li>
                        <li><a href="{{ route('exportConfig') }}"><i class="fa fa-plus"></i> <span>Adicionar Faixa</span></a></li>
                    </ul>
                </li>

                <li class="treeview">
                    <a href="#">
                        <i class="fa fa-sitemap"></i><span>Subredes</span>
                        <i class="fa fa-angle-left pull-right"></i>
                    </a>
                    <ul class="treeview-menu">
                        <li><a href="{{ route('exportConfig') }}"><i class="fa fa-th-list"></i> <span>Listar subredes</span></a></li>
                        <li><a href="{{ route('exportConfig') }}"><i class="fa fa-plus"></i> <span>Adicionar subrede</span></a></li>
                    </ul>
                </li>

                <li class="treeview @yield('usuarios')">
                    <a href="#">
                        <i class="fa fa-users"></i><span>Usuários</span>
                        <i class="fa fa-angle-left pull-right"></i>
                    </a>
                    <ul class="treeview-menu">
                        <li class="@yield('listUsers')"><a href="{{ route('showUsageRequest', 1) }}"><i class="fa fa-th-list"></i> <span>Listar Frequentes</span></a></li>
                        <li class="treeview @yield('tipousuario')">
                            <a href="#">
                                <i class="fa fa-puzzle-piece"></i><span>Tipos de usuários</span>
                                <i class="fa fa-angle-left pull-right"></i>
                            </a>
                            <ul class="treeview-menu">
                                <li class="@yield('addUserType')"><a href="{{ route('showAddUserType') }}"><i class="fa fa-plus"></i> <span>Adicionar</span></a></li>
                                <li class="@yield('listUserType')"><a href="{{ route('listUserType') }}"><i class="fa fa-th-list"></i> <span>Listar</span></a></li>
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
            <li class="treeview @yield('requisicoes')">
                <a href="#">
                    <i class="fa fa-hand-paper-o"></i><span>Requisições</span>
                    <i class="fa fa-angle-left pull-right"></i>
                </a>

                <ul class="treeview-menu">
                    <li class="@yield('addRequest')"><a href="{{ route('showAddRequest') }}"><i class="fa fa-plus"></i> <span>Nova requisição</span></a></li>
                    <li class="@yield('listUserRequests')"><a href="{{ route('listUserRequests') }}"><i class="fa fa-th-list"></i> <span>Minhas requisições</span></a></li>
                </ul>
            </li>
            <li><a href="{{ route('logout') }}"><i class="fa fa-sign-out" aria-hidden="true"></i><span>Sair</span></a></li>
        </ul>
    </section>
</aside>
