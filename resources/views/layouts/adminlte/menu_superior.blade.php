<header class="main-header">
    <!-- Logo -->
    <a href="{{ url('') }}" class="logo">
        <!-- mini logo for sidebar mini 50x50 pixels -->
        <span class="logo-mini">
            <img src="{{ url('images/Logo_Bench_Flow_B.png') }}" alt="" width="30px">
        </span>
        <!-- logo for regular state and mobile devices -->
        <span class="logo-lg">
            <img src="{{ url('images/Logo_Bench_Flow_verde_negro.png') }}" alt="" style="width:80px">
        </span>
    </a>
    <!-- Header Navbar: style can be found in header.less -->
    <nav class="navbar navbar-static-top">
        <!-- Sidebar toggle button-->
        <a href="#" class="sidebar-toggle" data-toggle="push-menu" role="button">
            <span class="sr-only">Menú</span>
        </a>
        <div id="div_submenu_crm" style="padding: 13px 10px; float: left;">

        </div>
        <div class="navbar-custom-menu">
            <ul class="nav navbar-nav" id="ul_navbar_superior">
                <li class="dropdown notifications-menu">
                    <a href="javascript:void(0)" class="dropdown-toggle" data-toggle="dropdown" aria-expanded="false"
                        onclick="actualizar_cosecha_x_variedad('li_cosecha_hoy', false)">
                        <i class="fa fa-leaf text-color_yura"></i>
                    </a>
                    <ul class="dropdown-menu sombra_estandar" style="">
                        <li>
                            <div id="li_cosecha_hoy" style="padding: 10px">

                            </div>
                        </li>
                    </ul>
                </li>
                <li class="dropdown notifications-menu">
                    <a href="javascript:void(0)" class="dropdown-toggle" data-toggle="dropdown" aria-expanded="false"
                        onclick="get_distribuciones_pendientes()" title="Distribuciones Pendientes">
                        <i class="fa fa-fw fa-star-half-empty text-color_yura"></i>
                    </a>
                    <ul class="dropdown-menu sombra_estandar" id="ul_distribuciones_pendientes" style="width: 650px">

                    </ul>
                </li>
                <li class="dropdown" title="Ver Cabios Diarios">
                    <a href="javascript:void(0)" class="dropdown-toggle" data-toggle="dropdown" aria-expanded="false"
                        onclick="buscar_cambios_diarios()">
                        <i class="fa fa-bell-o text-color_yura"></i>
                        <span class="label label-success" id="link_cambios"></span>
                    </a>
                    <ul class="dropdown-menu sombra_estandar" style="width: 850px">
                        <li class="header text-center" id="header_cambios"></li>
                        <li id="list_cambios" class="padding_lateral_5" style="overflow-y: scroll; max-height: 650px">
                        </li>
                    </ul>
                </li>
                <li class="dropdown notifications-menu hidden" title="Notifícame">
                    <input type="hidden" id="input_cant_not">
                    <a href="javascript:void(0)" class="dropdown-toggle" data-toggle="dropdown" aria-expanded="false"
                        onclick="buscar_notificaciones('S', false)">
                        <i class="fa fa-bell-o text-color_yura"></i>
                        <span class="label label-success" id="link_not"></span>
                    </a>
                    <ul class="dropdown-menu sombra_estandar" style="width: 850px">
                        <li class="header text-center" id="header_not"></li>
                        <li>
                            <ul class="menu" id="list_not">
                            </ul>
                        </li>
                        {{-- <li class="footer"><a href="javascript:void(0)">Marcar todo como leído</a></li> --}}
                    </ul>
                </li>
                <li class="dropdown user user-menu">
                    <a href="#" class="dropdown-toggle" data-toggle="dropdown">
                        <img src="{{ url('storage/imagenes') . '/' . getUsuario(Session::get('id_usuario'))->imagen_perfil }}"
                            class="user-image" alt="User Image" id="img_perfil_menu_superior">
                        <span
                            class="hidden-xs text-color_yura">{{ getUsuario(Session::get('id_usuario'))->nombre_completo }}</span>
                    </a>
                    <ul class="dropdown-menu">
                        <li class="user-body text-center text-color_yura">
                            <small>Miembro desde
                                {{ substr(getUsuario(Session::get('id_usuario'))->fecha_registro, 0, 10) }}</small>
                        </li>
                        <li class="user-footer">
                            <div class="pull-left">
                                <a href="javascript:void(0)" class="btn btn-yura_default"
                                    onclick="cargar_url('perfil')">
                                    Mi Perfil
                                </a>
                            </div>
                            <div class="pull-right">
                                <a href="javascript:void(0)" onclick="cargar_url('logout')"
                                    class="btn btn-yura_default">Salir</a>
                            </div>
                        </li>
                    </ul>
                </li>
                <li>
                    <a href="#" data-toggle="control-sidebar" onclick="cargar_manual_usuario()"><i
                            class="fa fa-book text-color_yura"></i></a>
                </li>
            </ul>
        </div>
    </nav>
</header>
