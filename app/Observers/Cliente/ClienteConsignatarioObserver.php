<?php

namespace yura\Observers\Cliente;

use yura\Jobs\Sincronizacion\Cliente\DeleteClienteConsignatario;
use yura\Jobs\Sincronizacion\Cliente\StoreClienteConsignatario;
use yura\Modelos\ClienteConsignatario;

class ClienteConsignatarioObserver
{
    public function saved(ClienteConsignatario $clienteConsignatario)
    {
        StoreClienteConsignatario::dispatch($clienteConsignatario)->onQueue('store_cliente_consignatario');
    }

    public function deleted(ClienteConsignatario $clienteConsignatario)
    {
        DeleteClienteConsignatario::dispatch($clienteConsignatario)->onQueue('delete_cliente_consignatario');
    }

}
