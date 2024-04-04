<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;

class Anuncio extends Model
{
    use HasFactory;
    use SoftDeletes;

    protected $table = 'anuncios';

    protected $primaryKey = 'id_anuncio';

    protected $fillable = ['titulo', 'descripcion','fecha','matricula'];

    protected $casts = [
        'id_anuncio' => 'anuncio'
    ];

   
    public function escolar()
    {
        return $this->belongsTo(Escolares::class,'matricula','id_anuncio');
    }

}
