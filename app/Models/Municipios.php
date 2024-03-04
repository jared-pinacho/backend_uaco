<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;

class Municipios extends Model
{
    use HasFactory;
    use SoftDeletes;

    protected $table = 'municipios';
    protected $primaryKey = 'id_municipio';
    protected $fillable = ['nombre','id_estado'];

    protected $casts = [
        'id_municipio' => 'string', 
    ];

    public function estado()
    {
        return $this->belongsTo(Estados::class, 'id_estado','id_estado');
    }

    public function colonias()
    {
        return $this->hasMany(Colonia::class, 'id_municipio','id_municipio');
    }
}
