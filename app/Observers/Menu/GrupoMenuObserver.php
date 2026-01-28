<?php

namespace yura\Observers\Menu;

use yura\Jobs\Sincronizacion\Menu\StoreGrupoMenu;
use yura\Modelos\GrupoMenu;

class GrupoMenuObserver
{
    public function saved(GrupoMenu $grupoMenu)
    {
        StoreGrupoMenu::dispatch($grupoMenu)->onQueue('store_grupo_menu');
    }
}
