<?php

namespace yura\Observers\Costos;

use yura\Jobs\Sincronizacion\Costos\StoreCostoSemanaManoObra;
use yura\Modelos\CostosSemanaManoObra;

class CostoSemanaManoObraObserver
{
    public function saved(CostosSemanaManoObra $costosSemanaManoObra)
    {
        StoreCostoSemanaManoObra::dispatch($costosSemanaManoObra)->onQueue('store_costo_semana_mano_obra');
    }
}
