<?php

namespace yura\Observers\ClasificacionVerde;

use yura\Jobs\Sincronizacion\loteRe\StoreLoteRe;
use yura\Modelos\LoteRE;

class LoteReObserver
{
    public function saved(LoteRE $loteRE)
    {
        StoreLoteRe::dispatch($loteRE)->onQueue('store_lote_re');
    }
}
