<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;

class Foraneo extends Model
{
    use HasFactory;
    use SoftDeletes;

    protected $table = 'foraneos';

    protected $primaryKey = 'id_foraneo';

    protected $fillable = ['nombre','apellido_paterno','apellido_materno','edad','telefono','correo','semestre','discapacidad','institucion','matricula_escolar','licenciatura','programa','horas','titular_dep','cargo_titular','grado_titular','resp_seg','CUC','fecha_inicio','fecha_final','matricula','estatus','sexo','id_lenguaindigena'
       
    ];

    protected $casts = [
        'id_foraneo' => 'string', 
    ];



    public function lenguaindigena()
    {
        return $this->belongsTo(LenguasIndigenas::class, 'id_lenguaindigena');
    }

    public function escolar()
    {
        return $this->belongsTo(Foraneo::class, 'matricula', 'id_foraneo');
    }
}
