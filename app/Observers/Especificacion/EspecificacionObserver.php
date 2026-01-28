<?php

namespace yura\Observers\Especificacion;

use yura\Jobs\Sincronizacion\Especificacion\DeleteEspecificacion;
use yura\Jobs\Sincronizacion\Especificacion\StoreEspecificacion;
use yura\Modelos\Especificacion;

class EspecificacionObserver
{
    public function saved(Especificacion $especificacion)
    {
        StoreEspecificacion::dispatch($especificacion)->onQueue('store_especificacion');
    }

    public function deleted(Especificacion $especificacion)
    {
        DeleteEspecificacion::dispatch($especificacion)->onQueue('delete_especificacion');
    }
}
