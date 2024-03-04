<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;

class Estados extends Model
{
    use HasFactory;
    use SoftDeletes;

    protected $table = 'estados';
    protected $primaryKey = 'id_estado';
    protected $fillable = ['nombre'];

    protected $casts = [
        'id_estado' => 'string', 
    ];

    public function municipios()
    {
        return $this->hasMany(Municipios::class, 'id_estado','id_estado');
    }

    public function consejeros(){
        return $this->hasMany(Consejeros::class,'estado_nacimiento','id_estado');
        //primer clave del otro modelo, y segunda clave de este modelo
    }

    public function escolares(){
        return $this->hasMany(Escolares::class,'estado_nacimiento','id_estado');
        //primer clave del otro modelo, y segunda clave de este modelo
    }

    public function facilitadores(){
        return $this->hasMany(Facilitadores::class,'estado_nacimiento','id_estado');
        //primer clave del otro modelo, y segunda clave de este modelo
    }

    public function estudiantes(){
        return $this->hasMany(Estudiantes::class,'estado_nacimiento','id_estado');
        //primer clave del otro modelo, y segunda clave de este modelo
    }
}
