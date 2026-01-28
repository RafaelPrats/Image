<?php

namespace yura\Observers\Comercializacion;

use yura\Jobs\Sincronizacion\Comercializacion\StoreContactoConsignatario;
use yura\Modelos\ContactoConsignatario;

class ContactoConsignatarioObserver
{
    public function saved(ContactoConsignatario $contactoConsignatario)
    {
        StoreContactoConsignatario::dispatch($contactoConsignatario)->onQueue('store_contacto_consignatario');
    }
}
