<?php

namespace yura\Observers\Cliente;

use yura\Jobs\Sincronizacion\Cliente\DeleteClienteDatosExportacion;
use yura\Jobs\Sincronizacion\Cliente\StoreClienteDatosExportacion;
use yura\Modelos\ClienteDatoExportacion;

class ClienteDatosExportacionObserver
{
    public function saved(ClienteDatoExportacion $clienteDatoExportacion)
    {
        StoreClienteDatosExportacion::dispatch($clienteDatoExportacion)->onQueue('store_cliente_datos_exportacion');
    }

    public function deleted(ClienteDatoExportacion $clienteDatoExportacion)
    {
        DeleteClienteDatosExportacion::dispatch($clienteDatoExportacion)->onQueue('delete_cliente_datos_exportacion');
    }
}
