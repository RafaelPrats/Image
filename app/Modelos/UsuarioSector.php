<?php

namespace yura\Modelos;

use Illuminate\Database\Eloquent\Model;

class UsuarioSector extends Model
{
    protected $table = 'usuario_sector';
    protected $primaryKey = 'id_usuario_sector';
    public $incrementing = false;
    public $timestamps = false;

    protected $fillable = [
        'id_usuario',
        'id_sector',
    ];

    public function usuario()
    {
        return $this->belongsTo('\yura\Modelos\Usuario', 'id_usuario');
    }

    public function sector()
    {
        return $this->belongsTo('\yura\Modelos\Sector', 'id_sector');
    }
}
