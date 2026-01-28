<?php

namespace yura\Observers\ProyNintanga;

use yura\Jobs\Sincronizacion\ProyNintanga\DeleteDistribucionVariedad;
use yura\Jobs\Sincronizacion\ProyNintanga\StoreDistribucionVariedad;
use yura\Modelos\DistribucionVariedad;

class DistribucionVariedadObserver
{
   public function saved(DistribucionVariedad $distribucionVariedad)
   {
        StoreDistribucionVariedad::dispatch($distribucionVariedad)->onQueue('store_distribucion_variedad');
   }

   public function deleted(DistribucionVariedad $distribucionVariedad)
   {
        DeleteDistribucionVariedad::dispatch($distribucionVariedad)->onQueue('delete_distribucion_variedad');
   }
}
