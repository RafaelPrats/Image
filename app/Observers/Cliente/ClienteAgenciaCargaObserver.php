<?php

namespace yura\Observers\Cliente;

use yura\Jobs\Sincronizacion\Cliente\DeleteClienteAgenciaCarga;
use yura\Jobs\Sincronizacion\Cliente\StoreClienteAgenciaCarga;
use yura\Modelos\ClienteAgenciaCarga;

class ClienteAgenciaCargaObserver
{

    public function saved(ClienteAgenciaCarga $clienteAgenciaCarga)
    {
        StoreClienteAgenciaCarga::dispatch($clienteAgenciaCarga)->onQueue('store_cliente_agencia_carga');
    }

    public function deleted(ClienteAgenciaCarga $clienteAgenciaCarga)
    {
        DeleteClienteAgenciaCarga::dispatch($clienteAgenciaCarga)->onQueue('delete_cliente_agencia_carga');
    }
}
