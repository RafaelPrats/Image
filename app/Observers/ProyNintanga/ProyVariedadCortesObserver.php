<?php

namespace yura\Observers\ProyNintanga;

use yura\Jobs\Sincronizacion\ProyNintanga\DeleteProyVariedadCortes;
use yura\Jobs\Sincronizacion\ProyNintanga\StoreProyVariedadCortes;
use yura\Modelos\ProyVariedadCortes;

class ProyVariedadCortesObserver
{
    public function saved(ProyVariedadCortes $proyVariedadCortes)
    {
        StoreProyVariedadCortes::dispatch($proyVariedadCortes)->onQueue('store_proy_variedad_cortes');
    }

    public function deleted(ProyVariedadCortes $proyVariedadCortes)
    {
        DeleteProyVariedadCortes::dispatch($proyVariedadCortes)->onQueue('delete_proy_variedad_cortes');
    }
}
