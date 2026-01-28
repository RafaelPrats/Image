<?php

namespace yura\Observers\Menu;

use yura\Jobs\Sincronizacion\Menu\StoreSubMenu;
use yura\Modelos\Submenu;

class SubMenuObserver
{
    public function saved(Submenu $subMenu)
    {
        StoreSubMenu::dispatch($subMenu)->onQueue('store_sub_menu');
    }
}
