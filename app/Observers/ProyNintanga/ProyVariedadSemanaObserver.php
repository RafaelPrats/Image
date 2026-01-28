<?php

namespace yura\Observers\ProyNintanga;

use yura\Jobs\Sincronizacion\ProyNintanga\DeleteProyVariedadSemana;
use yura\Jobs\Sincronizacion\ProyNintanga\StoreProyVariedadSemana;
use yura\Modelos\ProyVariedadSemana;

class ProyVariedadSemanaObserver
{
    public function saved(ProyVariedadSemana $proyVariedadSemana)
    {
        StoreProyVariedadSemana::dispatch($proyVariedadSemana)->onQueue('store_proy_variedad_semana');
    }

    public function deleted(ProyVariedadSemana $proyVariedadSemana)
    {
        DeleteProyVariedadSemana::dispatch($proyVariedadSemana)->onQueue('delete_proy_variedad_semana');
    }
}
