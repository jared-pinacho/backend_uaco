<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;

class FaseFinal extends Model
{
    use HasFactory;
    use SoftDeletes;

    protected $table = 'fase_finals';

    protected $primaryKey = 'id_faseFinal';
  
    protected $fillable = ['recibo','estatus','comentario','id_servicio'];

    protected $casts = [
        'id_faseFinals'=> 'string'
      ];
    
      public function servicio()
{
    return $this->belongsTo(Servicio::class, 'id_servicio');
}

}
