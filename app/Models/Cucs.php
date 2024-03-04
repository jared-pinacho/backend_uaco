<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;

class Cucs extends Model
{
    use HasFactory;
    use SoftDeletes;

    protected $table = 'cucs';
    protected $primaryKey = 'clave_cuc';
    protected $fillable = ['nombre','numero','id_direccion'];

    protected $casts = [
        'clave_cuc' => 'string', 
    ];

    public function direccion()
    {
        return $this->belongsTo(Direcciones::class, 'id_direccion','id_direccion');
    }

    public function carreras(){
        return $this->belongsToMany(Carreras::class,'cuc_carrera','clave_cuc','clave_carrera');
        //primer clave del modelo actual, y segunda clave con el otro modelo
    }

    public function facilitadores(){
        return $this->belongsToMany(Facilitadores::class,'cuc_facilitadores','clave_cuc','matricula');
        //primer clave del modelo actual, y segunda clave con el otro modelo
    }

    public function grupos(){
    return $this->hasMany(Grupos::class, 'clave_carrera', 'clave_carrera');
    }

    public function consejeros(){
        return $this->hasMany(Consejeros::class,'clave_cuc','clave_cuc');
        //primer clave del otro modelo, y segunda clave de este modelo
    }

    public function escolares(){
        return $this->hasMany(Escolares::class,'clave_cuc','clave_cuc');
        //primer clave del otro modelo, y segunda clave de este modelo
    }

    public function clases(){
        return $this->hasMany(Clases::class,'clave_cuc','clave_cuc');
    }
}
