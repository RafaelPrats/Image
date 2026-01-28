<?php

namespace yura\Observers\Indicador;

use yura\Jobs\Sincronizacion\Indicador\{ StoreIntervaloIndicador, DeleteIntervaloIndicador };
use yura\Modelos\IntervaloIndicador;

class IntervaloIndicadorObserver
{
    public function saved(IntervaloIndicador $intervaloIndicador)
    {
        StoreIntervaloIndicador::dispatch($intervaloIndicador)->onQueue('store_intervalo_indicador');
    }

    public function deleted(IntervaloIndicador $intervaloIndicado)
    {
        DeleteIntervaloIndicador::dispatch($intervaloIndicado)->onQueue('delete_intervalo_indicador');
    }
}
