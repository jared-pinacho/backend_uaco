<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;

class Colonia extends Model
{
    use HasFactory;
    use SoftDeletes;

    protected $table = 'colonias';
    protected $primaryKey = 'id_colonia';
    protected $fillable = ['nombre', 'id_cp', 'id_municipio'];


    public function cp()
    {
        return $this->belongsTo(CodigoPostal::class, 'id_cp','id_cp');
    }

    public function municipio()
    {
        return $this->belongsTo(Municipios::class, 'id_municipio','id_municipio');
    }

    public function direcciones()
    {
        return $this->hasMany(Direcciones::class, 'id_colonia','id_colonia');
    }
}
