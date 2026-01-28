<?php

namespace yura\Observers\Comercializacion;

use yura\Jobs\Sincronizacion\Comercializacion\StoreDataTallos;
use yura\Modelos\DataTallos;

class DataTallosObserver
{
    public function saved(DataTallos $dataTallos)
    {
        StoreDataTallos::dispatch($dataTallos)->onQueue('store_data_tallos');
    }
}
