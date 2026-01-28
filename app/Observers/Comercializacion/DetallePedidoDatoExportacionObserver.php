<?php

namespace yura\Observers\Comercializacion;

use yura\Jobs\Sincronizacion\Comercializacion\StoreDetallePedidoDatoExportacion;
use yura\Modelos\DetallePedidoDatoExportacion;

class DetallePedidoDatoExportacionObserver
{
    public function saved(DetallePedidoDatoExportacion $detallePedidoDatoExportacion)
    {
        StoreDetallePedidoDatoExportacion::dispatch($detallePedidoDatoExportacion)->onQueue('store_detalle_pedido_dato_exportacion');
    }
}
