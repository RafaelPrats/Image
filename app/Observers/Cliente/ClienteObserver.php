<?php

namespace yura\Observers\Cliente;

use yura\Jobs\Sincronizacion\Cliente\StoreCliente;
use yura\Modelos\Cliente;

class ClienteObserver
{
    public function saved(Cliente $cliente)
    {
        StoreCliente::dispatch($cliente)->onQueue('store_cliente');
    }
}
