<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;

class Direcciones extends Model
{
    use HasFactory;
    use SoftDeletes;
    protected $table = 'direcciones';
    protected $primaryKey = 'id_direccion';
    protected $fillable = ['calle','num_exterior','id_colonia'];
    public function cuc()
    {
        return $this->hasOne(Cucs::class, 'id_direccion');
    }

    public function consejero()
    {
        return $this->hasOne(Consejeros::class, 'id_direccion');
    }

    public function escolar()
    {
        return $this->hasOne(Escolares::class, 'id_direccion');
    }

    public function facilitador()
    {
        return $this->hasOne(Facilitadores::class, 'id_direccion');
    }

    public function estudiante()
    {
        return $this->hasOne(Estudiantes::class, 'id_direccion');
    }

    public function servicio()
    {
        return $this->hasOne(Servicio::class, 'id_direccion');
    }


    public function colonia()
    {
        return $this->belongsTo(Colonia::class, 'id_colonia','id_colonia');
    }
}
