<?php

namespace yura\Console\Commands;

use Illuminate\Console\Command;
use yura\Modelos\ConfiguracionEmpresa;
use yura\Modelos\Pedido;

class AsignarPackingPedido extends Command
{
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'asignar:packing';

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = 'Asignar packing a los pedidos';

    /**
     * Create a new command instance.
     *
     * @return void
     */
    public function __construct()
    {
        parent::__construct();
    }

    /**
     * Execute the console command.
     *
     * @return mixed
     */
    public function handle()
    {
        $pedidos = Pedido::where('estado',1)
        ->where('fecha_pedido','>=','2022-12-02')
        ->orderBy('fecha_pedido','asc')->get();

        $bar = $this->output->createProgressBar(count($pedidos));

        foreach($pedidos as $pedido){

            $p =Pedido::find($pedido->id_pedido);

            $ce = ConfiguracionEmpresa::All()->first();

            if(isset($p)){

                $p->packing = $ce->numero_packing+1;
                $p->save();

                $ce->numero_packing = $p->packing;
                $ce->save();

            }

            $bar->advance();

        }

        $bar->finish();
        $this->info('✔ Packings asignados con éxito ✔');
    }
}
