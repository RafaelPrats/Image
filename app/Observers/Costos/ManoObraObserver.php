<?php

namespace yura\Observers\Costos;

use yura\Jobs\Sincronizacion\Costos\StoreManoObra;
use yura\Modelos\ManoObra;

class ManoObraObserver
{
    public function saved(ManoObra $manoObra)
    {
        StoreManoObra::dispatch($manoObra)->onQueue('store_mano_obra');
    }
}
