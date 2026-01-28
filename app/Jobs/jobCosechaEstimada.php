<?php

namespace yura\Jobs;

use Artisan;
use Illuminate\Bus\Queueable;
use Illuminate\Queue\SerializesModels;
use Illuminate\Queue\InteractsWithQueue;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Foundation\Bus\Dispatchable;

class jobCosechaEstimada implements ShouldQueue
{
    use Dispatchable, InteractsWithQueue, Queueable, SerializesModels;

    /**
     * Create a new job instance.
     *
     * @return void
     */
    protected $variedad;
    protected $longitud;
    protected $fecha;
    public function __construct($variedad, $longitud, $fecha)
    {
        $this->variedad = $variedad;
        $this->longitud = $longitud;
        $this->fecha = $fecha;
    }

    /**
     * Execute the job.
     *
     * @return void
     */
    public function handle()
    {
        /*Artisan::call('cosecha:estimada', [
            'variedad' => $this->variedad,
            'longitud' => $this->longitud,
            'fecha' => $this->fecha,
        ]);*/
        Artisan::call('update:cosecha_estimada', [
            'variedad' => $this->variedad,
            'longitud' => $this->longitud,
            'fecha' => $this->fecha,
        ]);
    }
}
