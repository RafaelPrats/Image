<?php

namespace yura\Observers\Notificacion;

use yura\Jobs\Sincronizacion\Notificacion\{ StoreNotificacionUsuario, DeleteNotificacionUsuario };
use yura\Modelos\NotificacionUsuario;

class NotificacionUsuarioObserver
{
    public function saved(NotificacionUsuario $notificacionUsuario)
    {
        StoreNotificacionUsuario::dispatch($notificacionUsuario)->onQueue('store_notificacion_usuario');
    }

    public function deleted(NotificacionUsuario $notificacionUsuario)
    {
        DeleteNotificacionUsuario::dispatch($notificacionUsuario)->onQueue('delete_notificacion_usuario');
    }
}
