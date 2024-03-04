<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;

class Materias extends Model
{
    use HasFactory;
    use SoftDeletes;
    protected $table = 'materias';
    protected $primaryKey = 'clave_materia';
    protected $fillable =['nombre','creditos'];

    protected $casts = [
        'clave_materia' => 'string', 
    ];

    public function carreras(){
        return $this->belongsToMany(Carreras::class,'programa_materias','clave_materia','clave_carrera');
        //primer clave del modelo actual, y segunda clave con el otro modelo
    }

    public function clases(){
        return $this->hasMany(Clases::class,'clave_materia','clave_materia');
    }
}
