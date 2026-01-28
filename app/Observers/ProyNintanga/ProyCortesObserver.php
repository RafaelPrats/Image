<?php

namespace yura\Observers\ProyNintanga;

use yura\Jobs\Sincronizacion\ProyNintanga\StoreProyCortes;
use yura\Modelos\ProyCortes;

class ProyCortesObserver
{
    public function saved(ProyCortes $proyCortes)
    {
        StoreProyCortes::dispatch($proyCortes)->onQueue('store_proy_cortes');
    }
}
