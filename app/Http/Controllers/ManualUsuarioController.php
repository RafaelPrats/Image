<?php

namespace yura\Http\Controllers;

use Illuminate\Http\Request;

class ManualUsuarioController extends Controller
{
    public function cargar_manual_usuario(Request $request)
    {
        return view('manual_usuario.comercializacion.' . $request->vista_actual . '.inicio');
    }
}
