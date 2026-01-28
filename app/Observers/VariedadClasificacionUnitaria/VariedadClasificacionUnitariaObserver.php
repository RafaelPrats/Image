<?php

namespace yura\Observers\VariedadClasificacionUnitaria;

use yura\Jobs\Sincronizacion\VariedadClasificacionUnitaria\Delete;
use yura\Jobs\Sincronizacion\VariedadClasificacionUnitaria\Store;
use yura\Modelos\VariedadClasificacionUnitaria;

class VariedadClasificacionUnitariaObserver
{
    public function saved(VariedadClasificacionUnitaria $variedadClasificacionUnitaria)
    {
        Store::dispatch($variedadClasificacionUnitaria)->onQueue('store_variedad_clasificacion_unitaria');
    }

    public function deleted(VariedadClasificacionUnitaria $variedadClasificacionUnitaria)
    {
        Delete::dispatch($variedadClasificacionUnitaria)->onQueue('delete_variedad_clasificacion_unitaria');
    }
}
