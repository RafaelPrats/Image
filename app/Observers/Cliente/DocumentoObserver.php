<?php

namespace yura\Observers\Cliente;

use yura\Jobs\Sincronizacion\Cliente\StoreDocumento;
use yura\Modelos\Documento;

class DocumentoObserver
{
    public function saved(Documento $documento)
    {
        StoreDocumento::dispatch($documento)->onQueue('store_documento');
    }
}
