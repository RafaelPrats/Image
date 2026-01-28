<?php

namespace yura\Modelos;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Support\Facades\DB;

class Despacho extends Model
{
    protected $table = 'despacho';
    protected $primaryKey = 'id_despacho';
    public $incrementing = false;
    public $timestamps = false;

    protected $fillable = [
        'id_despacho',
        'id_transportista',
        'id_camion',
        'id_chofer',
        'fecha_despacho',
        'sello_salida',
        'semana',
        'rango_temp',
        'n_viaje',
        'hora_salida',
        'temp',
        'kilometraje',
        'sellos',
        'fecha_registro',
        'horario',
        'resp_ofi_despacho',
        'id_resp_ofi_despacho',
        'aux_cuarto_fri',
        'id_aux_cuarto_fri',
        'guardia_turno',
        'id_guardia_turno',
        'asist_comercial_ext',
        'id_asist_comrecial_ext',
        'resp_transporte',
        'id_resp_transporte',
        'n_despacho',
        'sello_adicional',
        'estado',
        'mail_resp_ofi_despacho',
        'id_configuracion_empresa'
    ];

    public function detalles()
    {
        return $this->hasMany('\yura\Modelos\DetalleDespacho', 'id_despacho');
    }

    public function getDetalles()
    {
        $query = $this->detalles;
        foreach ($this->detalles as $x => $det_desp) {
            $pedido = $det_desp->pedido;

            $caja_full = 0;
            foreach ($pedido->detalles as $det_ped) {
                foreach ($det_ped->cliente_especificacion->especificacion->especificacionesEmpaque as $esp_emp) {
                    $caja_full += $esp_emp->cantidad * $det_ped->cantidad * explode('|', $esp_emp->empaque->nombre)[1];
                }
            }

            $query[] = [
                'pos' => $x + 1,
                'agencia' => $pedido->detalles['0']->agencia_carga->nombre,
                'cliente' => $pedido->cliente->detalle()->nombre,
                'consignatario' => isset($pedido->envios[0]) && isset($pedido->envios[0]->consignatario) ? $pedido->envios[0]->consignatario->nombre : '',
                'packing' => $pedido->packing,
                'piezas' => $det_desp->cantidad,
                'caja_full' => $caja_full,
                'guia_madre' => isset($pedido->envios[0]) ? $pedido->envios[0]->guia_madre : '',
                'guia_hija' => isset($pedido->envios[0]) ? $pedido->envios[0]->guia_hija : '',
            ];
        }
        $agencias = [];
        foreach ($query as $q) {
            if (!in_array($q['agencia'], $agencias)) {
                $agencias[] = $q['agencia'];
            }
        }
        dd($query);
    }

    public function conductor()
    {
        return $this->belongsTo('\yura\Modelos\Conductor', 'id_conductor');
    }

    public function camion()
    {
        return $this->belongsTo('\yura\Modelos\Camion', 'id_camion');
    }

    public function transportista()
    {
        $this->belongsTo('\yura\Modelos\Transportista', 'id_transportista');
    }
}
