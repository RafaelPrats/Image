<?php

namespace yura\Observers\ClasificacionBlanco;

use yura\Jobs\Sincronizacion\ClasificacionBlanco\StoreclasificacionBlanco;
use yura\Modelos\ClasificacionBlanco;

class ClasificacionBlancoObserver
{
    public function saved(ClasificacionBlanco $clasificacionBlanco)
    {
        StoreclasificacionBlanco::dispatch($clasificacionBlanco)->onQueue('store_clasificacion_blanco');
    }
}
