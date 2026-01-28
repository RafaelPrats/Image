<?php

namespace yura\Observers\Usuarios;

use yura\Modelos\UsuarioSector;
use yura\Jobs\Sincronizacion\UsuarioSector\{
    Store, Delete
};

class UsuarioSectorObserver
{
    public function saved(UsuarioSector $usuarioSector)
    {
        Store::dispatch($usuarioSector)->onQueue('store_usuario_sector');
    }

    public function deleted(UsuarioSector $usuarioSector)
    {
        Delete::dispatch($usuarioSector)->onQueue('delete_usuario_sector');
    }
}
