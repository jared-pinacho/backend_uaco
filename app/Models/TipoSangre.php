<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;

class TipoSangre extends Model
{
    use HasFactory;
    use SoftDeletes;

    protected $table = 'tipo_sangres';
    protected $primaryKey = 'id_tiposangre';
    protected $fillable = ['nombre'];

    public function consejeros(){
        return $this->hasMany(Consejeros::class,'id_tiposangre','id_tiposangre');
        //primer clave del otro modelo, y segunda clave de este modelo
    }

    public function escolares(){
        return $this->hasMany(Escolares::class,'id_tiposangre','id_tiposangre');
        //primer clave del otro modelo, y segunda clave de este modelo
    }

    public function facilitadores(){
        return $this->hasMany(Facilitadores::class,'id_tiposangre','id_tiposangre');
        //primer clave del otro modelo, y segunda clave de este modelo
    }

    public function estudiantes(){
        return $this->hasMany(Estudiantes::class,'id_tiposangre','id_tiposangre');
        //primer clave del otro modelo, y segunda clave de este modelo
    }
}
