<?php

namespace yura\Console\Commands;

use Illuminate\Console\Command;
use Illuminate\Support\Facades\Artisan;

class ReiniciarJobsFallidos extends Command
{
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'reiniciar:jobs';

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = 'Reinicia Jobs de sincronización fallidos en las colas';

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
        info('reiniciando jobs fallido');
        Artisan::call("queue:retry",['id'=>'all']);
        
    }
}
