<?php

namespace yura\Observers\Comercializacion;

use yura\Jobs\Sincronizacion\Comercializacion\DeleteCodigoVentureAgenciaCarga;
use yura\Jobs\Sincronizacion\Comercializacion\StoreCodigoVentureAgenciaCarga;
use yura\Modelos\CodigoVentureAgenciaCarga;

class CodigoVentureAgenciaCargaObserver
{
    public function saved(CodigoVentureAgenciaCarga $codigoVentureAgenciaCarga)
    {
        StoreCodigoVentureAgenciaCarga::dispatch($codigoVentureAgenciaCarga)->onQueue('store_codigo_venture_agencia_carga');
    }

    public function deleted(CodigoVentureAgenciaCarga $codigoVentureAgenciaCarga)
    {
        DeleteCodigoVentureAgenciaCarga::dispatch($codigoVentureAgenciaCarga)->onQueue('delete_codigo_venture_agencia_carga');
    }
}
