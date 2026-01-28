<?php

namespace yura\Observers\Usuarios;

use yura\Modelos\Usuario;
use yura\Jobs\Sincronizacion\Usuario\Store;

class UsuarioObserver
{
    public function saved(Usuario $usuario)
    {
        Store::dispatch($usuario)->onQueue('store_usuario');
    }

}
