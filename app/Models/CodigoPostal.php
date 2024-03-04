<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;

class CodigoPostal extends Model
{
    use HasFactory;
    use SoftDeletes;

    protected $table = 'codigo_postal';
    protected $primaryKey = 'id_cp';
    protected $fillable = ['codigo'];

    protected $casts = [
        'id_cp' => 'string', 
    ];    

    public function colonias()
    {
        return $this->hasMany(Colonia::class, 'id_cp','id_cp');
    }
}
