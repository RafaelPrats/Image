<?php

namespace yura\Observers\Especificacion;

use yura\Jobs\Sincronizacion\Especificacion\DeleteEspecificacion;
use yura\Jobs\Sincronizacion\Especificacion\StoreEspecificacionEmpaque;
use yura\Modelos\EspecificacionEmpaque;

class EspecificacionEmpaqueObserver
{
    public function saved(EspecificacionEmpaque $especificacionEmpaque)
    {
        StoreEspecificacionEmpaque::dispatch($especificacionEmpaque)->onQueue('store_especificacion_empaque');
    }

    public function deleted(EspecificacionEmpaque $especificacionEmpaqu)
    {
        DeleteEspecificacion::dispatch($especificacionEmpaqu)->onQueue('delete_especificacion_empaque');
    }
}
