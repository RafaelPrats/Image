<?php

namespace yura\Observers\Notificacion;

use yura\Jobs\Sincronizacion\Notificacion\DeleteUserNotification;
use yura\Jobs\Sincronizacion\Notificacion\StoreUserNotification;
use yura\Modelos\UserNotification;

class UserNotificationObserver
{
    public function saved(UserNotification $userNotification)
    {
        StoreUserNotification::dispatch($userNotification)->onQueue('store_user_notification');
    }

    public function deleted(UserNotification $userNotification)
    {
        DeleteUserNotification::dispatch($userNotification)->onQueue('delete_user_notification');
    }
}
