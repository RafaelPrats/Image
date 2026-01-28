<?php

namespace yura\Observers\Proyeccion;

use yura\Jobs\Sincronizacion\Proyeccion\StoreResumenSaldoProyVentaSemanal;
use yura\Modelos\ResumenSaldoProyeccionVentaSemanal;

class ResumenSaldoProyVentaSemanalObserver
{
    public function saved(ResumenSaldoProyeccionVentaSemanal $resumenSaldoProyeccionVentaSemanal)
    {
        StoreResumenSaldoProyVentaSemanal::dispatch($resumenSaldoProyeccionVentaSemanal)->onQueue('store_resumen_saldo_proy_venta_semanal');
    }
}
