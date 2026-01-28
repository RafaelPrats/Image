<?php

namespace yura\Observers\ProyNintanga;

use yura\Jobs\Sincronizacion\ProyNintanga\StoreDiasCosechaSemana;
use yura\Modelos\DiasCosechaSemana;

class DiasCosechaSemanaObserver
{
    public function saved(DiasCosechaSemana $diasCosechaSemana)
    {
        StoreDiasCosechaSemana::dispatch($diasCosechaSemana)->onQueue('store_dias_cosecha_semana');
    }
}
