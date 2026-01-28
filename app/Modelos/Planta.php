<?php

namespace yura\Modelos;

use Illuminate\Database\Eloquent\Model;

class Planta extends Model
{
    protected $table = 'planta';
    protected $primaryKey = 'id_planta';
    public $incrementing = false;
    public $timestamps = false;

    protected $fillable = [
        'id_planta',
        'nombre',   // unico
        'siglas',   // unico
        'fecha_registro',
        'estado',
    ];

    public function variedades()
    {
        return $this->hasMany('\yura\Modelos\Variedad', 'id_planta')->orderBy('nombre');
    }

    public function variedades_activos()
    {
        return $this->hasMany('\yura\Modelos\Variedad', 'id_planta')->where('estado', '=', 1)->orderBy('nombre');
    }

    public function variedadByOrdenNintanga()
    {
        if($this->id_planta == 2 || $this->id_planta == 3){

            if($this->id_planta == 2){

               return Variedad::whereIn('id_variedad',[1,2,3,4,5,6,7,8,9,10,11,12])->get();

            }else if($this->id_planta == 3){

                return Variedad::whereIn('id_variedad',[13,14,15,24,16,17,18,19,20,21,22,23])->get();

            }

        }else if($this->id_planta == 8){

            return Variedad::whereIn('id_variedad',[36,37,38,39,40,41,35,49,50])->get();

        }else if($this->id_planta == 9){

            return Variedad::whereIn('id_variedad',[43,44,45,46,47,42])->get();

        }else if($this->id_planta == 11){

            return Variedad::whereIn('id_variedad',[55,52,51,53])->get();

        }else if($this->id_planta == 4 || $this->id_planta == 5 || $this->id_planta == 6){

            if($this->id_planta ==4){
                return Variedad::whereIn('id_variedad',[32,33,34])->get();
            }else if($this->id_planta ==5){
                return Variedad::whereIn('id_variedad',[26,27,28])->get();
            }else if($this->id_planta ==6){
                return Variedad::whereIn('id_variedad',[29,30,31])->get();
            }

        }else{

            return Variedad::where([
                ['id_planta',$this->id_planta],
                ['estado',1]
            ])->get();

        }
    }
}
