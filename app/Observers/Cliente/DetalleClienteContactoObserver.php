<?php

namespace yura\Observers\Cliente;

use yura\Jobs\Sincronizacion\Cliente\StoreDetalleClienteContacto;
use yura\Modelos\DetalleClienteContacto;

class DetalleClienteContactoObserver
{
    public function saved(DetalleClienteContacto $detalleContacto)
    {
        StoreDetalleClienteContacto::dispatch($detalleContacto)->onQueue('store_detalle_cliente_contacto');
    }
}
