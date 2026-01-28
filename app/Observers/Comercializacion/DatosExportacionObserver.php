<?php

namespace yura\Observers\Comercializacion;

use yura\Jobs\Sincronizacion\Comercializacion\DeleteDatosExportacion;
use yura\Jobs\Sincronizacion\Comercializacion\StoreDatosExportacion;
use yura\Modelos\DatosExportacion;

class DatosExportacionObserver
{
    public function saved(DatosExportacion $datoExportacion)
    {
        StoreDatosExportacion::dispatch($datoExportacion)->onQueue('store_datos_exportacion');
    }

}
