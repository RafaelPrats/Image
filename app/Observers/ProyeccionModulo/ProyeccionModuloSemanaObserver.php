<?php

namespace yura\Observers\ProyeccionModulo;

use yura\Jobs\Sincronizacion\ProyeccionModulo\StoreProyeccionModuloSemana;
use yura\Modelos\ProyeccionModuloSemana;

class ProyeccionModuloSemanaObserver
{
    public function saved(ProyeccionModuloSemana $proyeccionModuloSemana)
    {
        StoreProyeccionModuloSemana::dispatch($proyeccionModuloSemana)->onQueue('store_proyeccion_modulo_semana');
    }
}
