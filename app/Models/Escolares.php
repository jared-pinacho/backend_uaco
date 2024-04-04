<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;

class Escolares extends Model
{
    use HasFactory;
    use SoftDeletes;

    protected $table = 'escolares';
    protected $primaryKey = 'matricula';
    protected $fillable = ['nombre','apellido_paterno','apellido_materno','fecha_nacimiento','sexo','estado_nacimiento','curp','rfc','nivel_educativo','perfil_academico','telefono','id_tiposangre','telefono_emergencia','padecimiento','id_direccion','clave_cuc','id'];

    protected $casts = [
        'matricula' => 'string', 
    ];

    public function direccion()
    {
        return $this->belongsTo(Direcciones::class, 'id_direccion','id_direccion');
    }

    public function cuc(){
        return $this->belongsTo(Cucs::class,'clave_cuc','clave_cuc');
        //primer clave del otro modelo, y segunda clave de este modelo
    }

    public function usuario()
    {
        return $this->belongsTo(User::class, 'id','id');
    }

    public function tiposangre(){
        return $this->belongsTo(TipoSangre::class,'id_tiposangre','id_tiposangre');
        //primer clave del otro modelo, y segunda clave de este modelo
    }

    public function nacionalidad(){
        return $this->belongsTo(Nacionalidades::class,'id_nacionalidad','id_nacionalidad');
        //primer clave del otro modelo, y segunda clave de este modelo
    }

    public function estado(){
        return $this->belongsTo(Estados::class, 'estado_nacimiento', 'id_estado');
        //primer clave del otro modelo, y segunda clave de este modelo
    }


    public function foraneos()
{
    return $this->hasMany(Foraneo::class, 'matricula', 'id_foraneo');
}


public function servicios()
{
    return $this->hasMany(Servicio::class, 'matricula', 'id_servicio');
}

public function anuncios()
{
    return $this->hasMany(Anuncio::class, 'matricula', 'id_anuncio');
}



}
