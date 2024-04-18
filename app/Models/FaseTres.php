<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;

class FaseTres extends Model
{
    use HasFactory;
    use SoftDeletes;

    protected $table = 'fase_tres';

    protected $primaryKey = 'id_faseTres';
  
    protected $fillable = ['reporte_dos','estatus_envio','comentario','id_servicio'];

    protected $casts = [
        'id_faseTres'=> 'string'
      ];
    
      public function servicio()
      {
          return $this->belongsTo(Servicio::class, 'id_servicio');
      }
      

}
