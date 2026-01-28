<?php

namespace yura\Observers\Comercializacion;

use yura\Jobs\Sincronizacion\Comercializacion\StoreDetalleEspecificacionEmpaqueRamosCaja;
use yura\Modelos\DetalleEspecificacionEmpaqueRamosCaja;

class DetalleEspecificacionEmpaqueRamosCajaObserver
{
    public function saved(DetalleEspecificacionEmpaqueRamosCaja $detalleEspecificacionEmpaqueRamos)
    {
        StoreDetalleEspecificacionEmpaqueRamosCaja::dispatch($detalleEspecificacionEmpaqueRamos)->onQueue('store_detalle_especificacionempaque_ramos_caja');
    }
}
