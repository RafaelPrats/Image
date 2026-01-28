<?php

namespace yura\Modelos;

use DB;
use Illuminate\Database\Eloquent\Model;
use Carbon\Carbon;

class Proyecto extends Model
{
    protected $table = 'proyecto';
    protected $primaryKey = 'id_proyecto';
    public $incrementing = false;
    public $timestamps = false;

    protected $fillable = [
        'id_cliente',
        'estado',
        'fecha',
        'fecha_registro',
        'tipo',
        'packing',
        'orden_fija',
        'id_consignatario',
        'id_agencia_carga',
    ];

    public function cajas()
    {
        return $this->hasMany('\yura\Modelos\CajaProyecto', 'id_proyecto');
    }

    public function cliente()
    {
        return $this->belongsTo('\yura\Modelos\Cliente', 'id_cliente');
    }

    public function empresa()
    {
        return $this->belongsTo('\yura\Modelos\ConfiguracionEmpresa', 'id_empresa');
    }

    public function consignatario()
    {
        return $this->belongsTo('\yura\Modelos\Consignatario', 'id_consignatario');
    }

    public function agencia_carga()
    {
        return $this->belongsTo('\yura\Modelos\AgenciaCarga', 'id_agencia_carga');
    }

    public function aerolinea()
    {
        return $this->belongsTo('\yura\Modelos\Aerolinea', 'id_aerolinea');
    }

    public function mixtos()
    {
        return $this->hasMany('\yura\Modelos\Mixtos', 'id_proyecto');
    }

    public function getTotalPiezas()
    {
        return DB::table('caja_proyecto')
            ->select(DB::raw('sum(cantidad) as cant'))
            ->where('id_proyecto', $this->id_proyecto)
            ->get()[0]->cant;
    }

    public function getTotales()
    {
        return DB::table('caja_proyecto as c')
            ->join('detalle_caja_proyecto as det', 'det.id_caja_proyecto', '=', 'c.id_caja_proyecto')
            ->select(
                DB::raw('sum(c.cantidad * det.ramos_x_caja) as ramos'),
                DB::raw('sum(c.cantidad * det.ramos_x_caja * det.tallos_x_ramo) as tallos'),
            )
            ->where('c.id_proyecto', $this->id_proyecto)
            ->get()[0];
    }

    public function getTotalesMixtos()
    {
        return DB::table('caja_proyecto as c')
            ->join('detalle_caja_proyecto as det', 'det.id_caja_proyecto', '=', 'c.id_caja_proyecto')
            ->join('variedad as v', 'v.id_variedad', '=', 'det.id_variedad')
            ->select(
                DB::raw('sum(c.cantidad * det.ramos_x_caja) as ramos'),
                DB::raw('sum(c.cantidad * det.ramos_x_caja * det.tallos_x_ramo) as tallos'),
            )
            ->where('c.id_proyecto', $this->id_proyecto)
            ->where('v.assorted', 1)
            ->get()[0];
    }

    public function getTotalFulls()
    {
        $r = 0;
        foreach ($this->cajas as $caja) {
            $empaque = $caja->empaque;
            $r += explode('|', $empaque->nombre)[1] * $caja->cantidad;
        }
        return $r;
    }

    public function getMarcaciones()
    {
        $r = [];
        foreach ($this->cajas as $caja) {
            $marcaciones = $caja->marcaciones;
            foreach ($marcaciones as $val) {
                if (!in_array($val->valor, $r)) {
                    $r[] = $val->valor;
                }
            }
        }
        return $r;
    }

    public function getDatosExportacion()
    {
        return DB::table('caja_proyecto_marcacion as cm')
            ->join('caja_proyecto as cp', 'cp.id_caja_proyecto', '=', 'cm.id_caja_proyecto')
            ->join('dato_exportacion as de', 'de.id_dato_exportacion', '=', 'cm.id_dato_exportacion')
            ->select('de.nombre', 'cm.id_dato_exportacion')->distinct()
            ->where('cp.id_proyecto', $this->id_proyecto)
            ->get();
    }

    public function getCodigoDae()
    {
        $consignatario = $this->consignatario;
        if ($this->codigo_pais != '') {
            $codigo_pais = $this->codigo_pais;
        } elseif ($consignatario != '') {
            $codigo_pais = $consignatario->codigo_pais;
        } else {
            $codigo_pais = '';
        }
        $carbon = Carbon::class;
        $mes = $carbon::parse(opDiasFecha('+', 1, $this->fecha))->format('m');
        $anno = $carbon::parse(opDiasFecha('+', 1, $this->fecha))->format('Y');
        $codigo = DB::table('codigo_dae')
            ->where('anno', $anno)
            ->where('mes', $mes)
            ->where('codigo_pais', $codigo_pais)
            ->where('estado', 1)
            ->get()
            ->first();
        return $codigo != '' ? $codigo->codigo_dae : '';
    }

    public function getMonto()
    {
        $monto = 0;
        foreach ($this->cajas as $caja) {
            foreach ($caja->detalles as $det) {
                $venta = $caja->cantidad * $det->ramos_x_caja * $det->precio;
                $monto += $venta;
            }
        }
        return $monto;
    }

    public function getMixtosDistribuidos()
    {
        $r = DB::table('mixtos')
            ->select(
                DB::raw('sum(ramos * piezas) as ramos'),
                DB::raw('sum(tallos) as tallos'),
            )
            ->where('id_proyecto', $this->id_proyecto)
            ->get()[0];
        return $r;
    }

    public function getHojaRuta()
    {
        $detalle = DetalleHojaRuta::where('id_proyecto', $this->id_proyecto)->get()->first();
        return $detalle != '' ? $detalle->hoja_ruta : '';
    }
}
