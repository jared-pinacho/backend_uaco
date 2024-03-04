<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;

class Periodos extends Model
{
    use HasFactory;
    use SoftDeletes;
    protected $table = 'periodos';
    protected $primaryKey = 'id_periodo';
    protected $fillable =['nombre','periodicidad','fecha_inicio','fecha_final'];

    public function clases(){
        return $this->hasMany(Clases::class,'id_periodo','id_periodo');
    }

    public function grupos(){
        return $this->belongsToMany(Grupos::class,'estado_reporte_grupos','id_periodo','clave_grupo')->withPivot('aprobado_inicio', 'aprobado_final', 'semestre');
        //primer clave del modelo actual, y segunda clave con el otro modelo
    }
}
