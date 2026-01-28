<?php

namespace yura\Modelos;

use Illuminate\Database\Eloquent\Model;

class CajaProyecto extends Model
{
    protected $table = 'caja_proyecto';
    protected $primaryKey = 'id_caja_proyecto';
    public $incrementing = false;
    public $timestamps = false;

    protected $fillable = [
        'id_proyecto',
        'cantidad',
        'orden',
        'id_empaque',
    ];

    public function detalles()
    {
        return $this->hasMany('\yura\Modelos\DetalleCajaProyecto', 'id_caja_proyecto');
    }

    public function proyecto()
    {
        return $this->belongsTo('\yura\Modelos\Proyecto', 'id_proyecto');
    }

    public function empaque()
    {
        return $this->belongsTo('\yura\Modelos\Empaque', 'id_empaque');
    }

    public function marcaciones()
    {
        return $this->hasMany('\yura\Modelos\CajaProyectoMarcacion', 'id_caja_proyecto');
    }

    public function getValorMarcacionByDatoExportacion($id_dato_exportacion)
    {
        return CajaProyectoMarcacion::where('id_caja_proyecto', $this->id_caja_proyecto)
            ->where('id_dato_exportacion', $id_dato_exportacion)
            ->get()
            ->first();
    }
}
