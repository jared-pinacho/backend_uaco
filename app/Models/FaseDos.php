<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;

class FaseDos extends Model
{
    use HasFactory;
    use SoftDeletes;

    protected $table = 'fase_dos';

    protected $primaryKey = 'id_faseDos';
  
    protected $fillable = ['reporte_uno','estatus','comentario','id_servicio'];

    protected $casts = [
        'id_faseDos'=> 'string'
      ];
    
      public function servicio()
{
    return $this->belongsTo(Servicio::class, 'id_servicio');
}

}
