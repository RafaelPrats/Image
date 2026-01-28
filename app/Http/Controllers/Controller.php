<?php

namespace yura\Http\Controllers;

use DB;
use Illuminate\Foundation\Bus\DispatchesJobs;
use Illuminate\Routing\Controller as BaseController;
use Illuminate\Foundation\Validation\ValidatesRequests;
use Illuminate\Foundation\Auth\Access\AuthorizesRequests;

class Controller extends BaseController
{
    use AuthorizesRequests, DispatchesJobs, ValidatesRequests;

    public static function checkPing()
    {
        if(env('ENTORNO') == 'LOCAL')
            $comando = "ping -c 1 ".config('database.connections.mysql_server.host');

        if(env('ENTORNO') == 'SERVER')
            $comando = "ping -c 1 ".config('database.connections.mysql_local.host');

        !isset($comando) &&
            dd('NO ESTA LA VARIABLE ENTORNO EN EL ARCHIVO .ENV');

        $output = shell_exec($comando);
        return strpos($output,'1 received');
    }

    public static function objetoConsulta($tabla,$where)
    {
        return self::conexion()->table($tabla)->where($where);
    }

    public static function conexion()
    {
        if(env('ENTORNO') == 'LOCAL')
            $conexion= DB::connection('mysql_server');

        if(env('ENTORNO') == 'SERVER')
            $conexion= DB::connection('mysql_local');

        !isset($conexion) &&
            dd('NO ESTA LA VARIABLE ENTORNO EN EL ARCHIVO .ENV');

        return $conexion;
    }

}
