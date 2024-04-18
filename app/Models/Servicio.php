<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;

class Servicio extends Model
{
    use HasFactory;
   

    protected $table = 'servicios';

    protected $primaryKey = 'id_servicio';

    protected $fillable = ['modalidad', 'tipo_dep', 'nombre_dep', 'titular_dep', 'cargo_tit', 'grado_tit', 'responsable', 'programa', 'actividad', 'fecha_ini', 'fecha_fin', 'horas', 'matricula','matricula_escolar','estatus','estatus_envio','comentario', 'id_direccion'];

    protected $casts = [
        'id_servicio' => 'string'
    ];


    public function direccion()
    {
        return $this->belongsTo(Direcciones::class, 'id_direccion','id_direccion');
    }


    public function faseUno()
    {
        return $this->hasOne(FaseUno::class, 'id_servicio');
    }
    public function faseDos()
    {
        return $this->hasOne(FaseDos::class, 'id_servicio');
    }
    public function faseTres()
    {
        return $this->hasOne(FaseTres::class, 'id_servicio');
    }
    public function faseCuatro()
    {
        return $this->hasOne(FaseCuatro::class, 'id_servicio');
    }
    public function faseCinco()
    {
        return $this->hasOne(FaseCinco::class, 'id_servicio');
    }
    public function faseFinal()
    {
        return $this->hasOne(FaseFinal::class, 'id_servicio');
    }
 
    
    public function estudiantes()
    {
        return $this->belongsTo(Estudiantes::class, 'matricula', 'matricula');
    }

    public function escolares()
{
    return $this->belongsTo(Escolares::class, 'id_servicio', 'matricula_escolar');
}

}
