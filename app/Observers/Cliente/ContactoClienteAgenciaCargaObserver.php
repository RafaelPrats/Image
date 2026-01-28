<?php

namespace yura\Observers\Cliente;

use yura\Jobs\Sincronizacion\Cliente\StoreContactoClienteAgenciaCarga;
use yura\Modelos\ContactoClienteAgenciaCarga;

class ContactoClienteAgenciaCargaObserver
{
    public function saved(ContactoClienteAgenciaCarga $contactoClienteAgenciaCarga)
    {
        StoreContactoClienteAgenciaCarga::dispatch($contactoClienteAgenciaCarga)->onQueue('store_contacto_cliente_agencia_carga');
    }
}
