<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;

class Clases extends Model
{
    use HasFactory;
    use SoftDeletes;

    protected $table = 'clases';
    protected $primaryKey = 'clave_clase';
    protected $fillable =['nombre','salon','id_periodo','clave_materia','matricula','hora_inicio',
    'hora_final','status','clave_cuc','clave_carrera','status_escolar','status_facilitador'];

    protected $casts = [
        'clave_clase' => 'string', 
    ];

    public function periodo(){
        return $this->belongsTo(Periodos::class,'id_periodo','id_periodo');
        //primer clave del otro modelo, y segunda clave de este modelo
    }

    public function materia(){
        return $this->belongsTo(Materias::class,'clave_materia','clave_materia');
        //primer clave del otro modelo, y segunda clave de este modelo
    }

    public function facilitador(){
        return $this->belongsTo(Facilitadores::class,'matricula','matricula');
        //primer clave del otro modelo, y segunda clave de este modelo
    }

    public function cuc(){
        return $this->belongsTo(Cucs::class,'clave_cuc','clave_cuc');
        //primer clave del otro modelo, y segunda clave de este modelo
    }
    
    public function carrera(){
        return $this->belongsTo(Carreras::class,'clave_carrera','clave_carrera');
        //primer clave del otro modelo, y segunda clave de este modelo
    }

    public function dias(){
        return $this->belongsToMany(Dias::class,'clase_dias','clave_clase','id_dia');
        //primer clave del modelo actual, y segunda clave con el otro modelo
    }

    public function estudiantes(){
        return $this->belongsToMany(Estudiantes::class,'clase_estudiantes','clave_clase','matricula');
        //primer clave del modelo actual, y segunda clave con el otro modelo
    }
}
