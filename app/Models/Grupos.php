<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;

class Grupos extends Model
{
    use HasFactory;
    use SoftDeletes;

    protected $table = 'grupos';
    protected $primaryKey = 'clave_grupo';
    protected $fillable = ['nombre','clave_carrera'];

    protected $casts = [
        'clave_grupo' => 'string', 
    ];

    public function carrera(){
        return $this->belongsTo(Carreras::class,'clave_carrera','clave_carrera');
        //primer clave del otro modelo, y segunda clave de este modelo
    }

    public function estudiantes(){
        return $this->belongsTo(Estudiantes::class,'clave_grupo','clave_grupo');
        //primer clave del otro modelo, y segunda clave de este modelo
    }

    public function periodos(){
        return $this->belongsToMany(Periodos::class,'estado_reporte_grupos','clave_grupo','id_periodo')->withPivot('aprobado_inicio', 'aprobado_final', 'semestre');
        //primer clave del modelo actual, y segunda clave con el otro modelo
    }
}
