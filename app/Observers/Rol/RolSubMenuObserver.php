<?php

namespace yura\Observers\Rol;

use yura\Jobs\Sincronizacion\Rol\StoreRolMenu;
use yura\Modelos\Rol_Submenu;

class RolSubMenuObserver
{
    public function saved(Rol_Submenu $rolSubmenu)
    {
        StoreRolMenu::dispatch($rolSubmenu)->onQueue('store_rol_menu');
    }
}
