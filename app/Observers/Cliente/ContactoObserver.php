<?php

namespace yura\Observers\Cliente;

use yura\Jobs\Sincronizacion\Cliente\StoreContacto;
use yura\Modelos\Contacto;

class ContactoObserver
{
    public function saved(Contacto $contacto)
    {
        StoreContacto::dispatch($contacto)->onQueue('store_contacto');
    }
}
