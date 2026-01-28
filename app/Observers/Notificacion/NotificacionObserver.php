<?php

namespace yura\Observers\Notificacion;

use yura\Jobs\Sincronizacion\Notificacion\StoreNotificacion;
use yura\Modelos\Notificacion;

class NotificacionObserver
{
    public function saved(Notificacion $notificacion)
    {
      StoreNotificacion::dispatch($notificacion)->onQueue('store_notificacion');
    }
}
