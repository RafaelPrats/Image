<?php

namespace yura\Jobs;

use Illuminate\Bus\Queueable;
use Illuminate\Queue\SerializesModels;
use Illuminate\Queue\InteractsWithQueue;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Foundation\Bus\Dispatchable;
use Illuminate\Support\Facades\Artisan;

class DistribucionRecetas implements ShouldQueue
{
    use Dispatchable, InteractsWithQueue, Queueable, SerializesModels;

    /**
     * Create a new job instance.
     *
     * @return void
     */
    public $pedido;
  
    public function __construct($pedido)
    {
        $this->pedido = $pedido;
    }

    /**
     * Execute the job.
     *
     * @return void
     */
    public function handle()
    {
        Artisan::call('distribucion:recetas', [
            'pedido' => $this->pedido
        ]);
    }
}
