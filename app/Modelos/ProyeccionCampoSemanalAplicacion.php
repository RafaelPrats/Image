<?php

namespace yura\Modelos;

use Illuminate\Database\Eloquent\Model;

class ProyeccionCampoSemanalAplicacion extends Model
{
    protected $table = 'proyeccion_campo_semanal_aplicacion';
    protected $primaryKey = 'id_proyeccion_campo_semanal_aplicacion';
    public $incrementing = false;
    public $timestamps = false;

    protected $fillable = [
        'id_proyeccion_campo_semanal',
        'app_nombre',
        'app_repeticion',
        'app_litro_x_cama',
        'fecha',
        'estado',   // Programado; Ejecutado; Adicional; Cancelado; Modificado(programado); X(programado para proyecciones)
        'camas',
    ];

    public function proyeccion_campo_semanal()
    {
        return $this->belongsTo('\yura\Modelos\ProyeccionCampoSemanal', 'id_proyeccion_campo_semanal');
    }

    public function detalles()
    {
        return $this->hasMany('\yura\Modelos\DetalleProyeccionCampoSemanalAplicacion', 'id_proyeccion_campo_semanal_aplicacion');
    }

    public function getEstado()
    {
        $estados = [
            'P' => 'Programado',
            'E' => 'Ejecutado',
            'X' => 'Programado',
            'A' => 'Adicional',
            'C' => 'Cancelado',
            'M' => 'Modificado',
        ];
        return $estados[$this->estado];
    }
}
