<?php

namespace yura\Observers\Menu;

use yura\Jobs\Sincronizacion\Menu\StoreMenu;
use yura\Modelos\Menu;

class MenuObserver
{
    public function saved(Menu $menu)
    {
        StoreMenu::dispatch($menu)->onQueue('store_menu');
    }
}
