<!-- Left side column. contains the sidebar -->
<aside class="main-sidebar">

    <!-- sidebar: style can be found in sidebar.less -->
    <section class="sidebar">

        <!-- Sidebar Menu -->
        <ul class="sidebar-menu">
          @if(Session::get("nivel") == 1)
            <li class="header text-center">ADMINISTRAÇÃO</li>
            <li class="@yield('pedidos')"><a href="{{url('/requests/0')}}"><i class="fa fa-legal"></i> <span>Pedidos</span> @if(Session::has('novosPedidos') && Session::get('novosPedidos') > 0) <span class="label label-success pull-right">{{Session::get('novosPedidos')}}</span> @endif</a></li>
            <li class="treeview @yield('dispositivo')">
              <a href="#">
                <i class="fa fa-laptop"></i><span>Dispositivos</span>
                <i class="fa fa-angle-left pull-right"></i>
              </a>
              <ul class="treeview-menu">
                <li class="@yield('addMac')"><a href="{{url('/addMac')}}"><i class="fa fa-plus"></i> <span>Adicionar</span></a></li>
                <li class="@yield('listMac')"><a href="{{url('/listMac/1')}}"><i class="fa fa-th-list"></i> <span>Listar</span></a></li>
                <li class="treeview @yield('tipodispositivo')">
                  <a href="#">
                    <i class="fa fa-puzzle-piece"></i><span>Tipos</span>
                    <i class="fa fa-angle-left pull-right"></i>
                  </a>
                  <ul class="treeview-menu">
                    <li class="@yield('addDeviceType')"><a href="{{url('/addDeviceType')}}"><i class="fa fa-plus"></i> <span>Adicionar</span></a></li>
                    <li class="@yield('listDeviceType')"><a href="{{url('/listDeviceType')}}"><i class="fa fa-th-list"></i> <span>Listar</span></a></li>
                  </ul>
                </li>
              </ul>
            </li>

            <li class="treeview @yield('tipousuario')">
              <a href="#">
                <i class="fa fa-users"></i><span>Tipos de usuários</span>
                <i class="fa fa-angle-left pull-right"></i>
              </a>
              <ul class="treeview-menu">
                <li class="@yield('addUserType')"><a href="{{url('/addUserType')}}"><i class="fa fa-plus"></i> <span>Adicionar</span></a></li>
                <li class="@yield('listUserType')"><a href="{{url('/listUserType')}}"><i class="fa fa-th-list"></i> <span>Listar</span></a></li>
              </ul>
            </li>

            <li class="treeview">
              <a href="#">
                <i class="fa fa-download"></i><span>Exportar</span>
                <i class="fa fa-angle-left pull-right"></i>
              </a>
              <ul class="treeview-menu">
                <li><a href="{{url('/exportArp')}}"><i class="fa fa-table"></i> <span>Tabela ARP</span></a></li>
                <li><a href="{{url('/exportDhcp')}}"><i class="fa fa-gear"></i> <span>DHCPD.CONF</span></a></li>
              </ul>
            </li>
          @endif

          @if(Session::has("id"))
            <li class="header text-center">MENU</li>
            <li class="treeview @yield('requisicoes')">
              <a href="#">
                <i class="fa fa-hand-paper-o"></i><span>Requisições</span>
                <i class="fa fa-angle-left pull-right"></i>
              </a>

              <ul class="treeview-menu">
                <li class="@yield('addRequest')"><a href="{{url('/addRequest')}}"><i class="fa fa-plus"></i> <span>Nova requisição</span></a></li>
                <li class="@yield('listUserRequests')"><a href="{{url('/listUserRequests')}}"><i class="fa fa-th-list"></i> <span>Minhas requisições</span></a></li>
              </ul>
            </li>
            <li><a href="{{url('/sair')}}"><i class="fa fa-sign-out" aria-hidden="true"></i><span>Sair</span></a></li>
          @endif
        </ul>
    </section>
</aside>
