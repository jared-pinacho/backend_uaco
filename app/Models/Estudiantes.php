<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;

class Estudiantes extends Model
{
    use HasFactory;
    use SoftDeletes;
    
    protected $table = 'estudiantes';
    protected $primaryKey = 'matricula';
    protected $fillable = ['nombre','apellido_paterno','apellido_materno','edad','curp','sexo','estado_nacimiento','fecha_nacimiento','nivel_educativo','telefono','telefono_emergencia',
    'id_direccion','id_nacionalidad','id_tiposangre','padecimiento','discapacidad','regular','semestre','estatus','creditos_acumulados','id_lenguaindigena','id_puebloindigena','clave_grupo','id','servicio_estatus','estatus_envio','comentario'];
    
    protected $casts = [
        'matricula' => 'string', 
    ];

    public function direccion()
    {
        return $this->belongsTo(Direcciones::class, 'id_direccion','id_direccion');
    }

    public function usuario()
    {
        return $this->belongsTo(User::class, 'id','id');
    }

    public function tiposangre(){
        return $this->belongsTo(TipoSangre::class,'id_tiposangre','id_tiposangre');
        //primer clave del otro modelo, y segunda clave de este modelo
    }

    public function lenguaindigena(){
        return $this->belongsTo(LenguasIndigenas::class,'id_lenguaindigena','id_lenguaindigena');
        //primer clave del otro modelo, y segunda clave de este modelo
    }

    public function puebloindigena(){
        return $this->belongsTo(PueblosIndigenas::class,'id_puebloindigena','id_puebloindigena');
        //primer clave del otro modelo, y segunda clave de este modelo
    }

    public function grupo(){
        return $this->belongsTo(Grupos::class,'clave_grupo','clave_grupo');
        //primer clave del otro modelo, y segunda clave de este modelo
    }

    public function clases(){
        return $this->belongsToMany(Clases::class,'clase_estudiantes','matricula','clave_clase');
        //primer clave del modelo actual, y segunda clave con el otro modelo
    }

    public function nacionalidad(){
        return $this->belongsTo(Nacionalidades::class,'id_nacionalidad','id_nacionalidad');
        //primer clave del otro modelo, y segunda clave de este modelo
    }

    public function estado(){
        return $this->belongsTo(Estados::class, 'estado_nacimiento', 'id_estado');
        //primer clave del otro modelo, y segunda clave de este modelo
    }

    public function documento()
    {
        return $this->hasOne(estadoDocumentacion::class, 'matricula');
    }

    public function servicio()
{
    return $this->hasOne(Servicio::class, 'matricula_escolar', 'matricula');
}
}
