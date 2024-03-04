<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;

class Carreras extends Model
{
    use HasFactory;
    use SoftDeletes;

    protected $table = 'carreras';
    protected $primaryKey = 'clave_carrera';
    protected $fillable = ['nombre','grado','creditos','periodicidad','duracion','modalidad'];

    protected $casts = [
        'clave_carrera' => 'string', 
    ];

    public function cucs(){
        return $this->belongsToMany(Cucs::class,'cuc_carrera','clave_carrera','clave_cuc');
        //primer clave del modelo actual, y segunda clave con el otro modelo
    }

    public function grupos(){
        return $this->hasMany(Grupos::class,'clave_carrera','clave_carrera');
        //primer clave del otro modelo, y segunda clave de este modelo
    }

    public function materias(){
        return $this->belongsToMany(Materias::class,'programa_materias','clave_carrera','clave_materia');
        //primer clave del modelo actual, y segunda clave con el otro modelo
    }

    public function clases(){
        return $this->hasMany(Clases::class,'clave_carrera','clave_carrera');
    }


}
