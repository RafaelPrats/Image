<?php

namespace yura\Observers\Especificacion;

use yura\Jobs\Sincronizacion\Especificacion\StoreDetalleEspecificacionEmpaque;
use yura\Modelos\DetalleEspecificacionEmpaque;

class DetalleEspecificacionEmpaqueObserver
{
    public function saved(DetalleEspecificacionEmpaque $detalleEspecificacionEmpaque)
    {
        StoreDetalleEspecificacionEmpaque::dispatch($detalleEspecificacionEmpaque)->onQueue('store_detalle_especificacion_empaque');
    }
}
