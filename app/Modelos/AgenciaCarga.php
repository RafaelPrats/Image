<?php

namespace yura\Modelos;

use Illuminate\Database\Eloquent\Model;

class AgenciaCarga extends Model
{
    protected $table = 'agencia_carga';
    protected $primaryKey = 'id_agencia_carga';
    public $incrementing = false;
    public $timestamps = false;

    protected $fillable = [
        'nombre',
        'codigo',
        'identificacion',
        'orden',
    ];

    public function cliente_agencia_carga()
    {
        return $this->hasMany('\yura\Modelos\ClienteAgenciaCarga', 'id_agencia_carga');
    }

    public function codigo_venture_agencia_carga()
    {
        return $this->hasMany('\yura\Modelos\CodigoVentureAgenciaCarga', 'id_agencia_carga');
    }

    public function codigo_venture_agencia_carga_by_id_configuracion_empresa($id_configuracion_empresa)
    {
        return CodigoVentureAgenciaCarga::where([
            ['id_agencia_carga', $this->id_agencia_carga],
            ['id_configuracion_empresa', $id_configuracion_empresa]
        ])->first();
    }

    public function contacto_agencia_carga($id_cliente)
    {
        $cac = $this->cliente_agencia_carga->where('id_cliente',$id_cliente)->pluck('id_cliente_agencia_carga')->toArray();

        if(count($cac)){
            $ccac = ContactoClienteAgenciaCarga::where('id_cliente_agencia_carga',$cac)->first();
            if(isset($ccac)){
                return $ccac;
            }else{
                return null;
            }
        }else{
            return null;
        }

    }

}
