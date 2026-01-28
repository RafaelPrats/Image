<?php

namespace yura\Jobs;

use Illuminate\Bus\Queueable;
use Illuminate\Queue\SerializesModels;
use Illuminate\Queue\InteractsWithQueue;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Foundation\Bus\Dispatchable;

class EdicionStandings implements ShouldQueue
{
    use Dispatchable, InteractsWithQueue, Queueable, SerializesModels;

    /**
     * Create a new job instance.
     *
     * @return void
     */
    public $cpeOldPedido;
    public $pedido;
    public $idPedido;

    public function __construct($cpeOldPedido,$pedido,$idPedido)
    {
        $this->cpeOldPedido = $cpeOldPedido;
        $this->pedido = $pedido;
        $this->idPedido = $idPedido;
    }

    /**
     * Execute the job.
     *
     * @return void
     */
    public function handle()
    {
        EditaStandingOrder::dispatch($this->cpeOldPedido,$this->pedido,$this->idPedido)->onQueue('edita_standing_order');
    }
}
