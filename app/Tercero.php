<?php

namespace BiciRegistro;

use Illuminate\Database\Eloquent\Model;

class Tercero extends Model
{
  protected $fillable = [
      'vehiculo_id','codigo_tercero','activo'
  ];

  public function vehiculo(){
      return $this->belongsTo(Vehiculo::class);
  }
}
