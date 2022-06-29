<?php
namespace App\Models;

use Illuminate\Database\Eloquent\Model;
/**
 * genera un objeto a partir del esquema de la tabla de la base de datos
 * @param string $table Nombre de la tabla
 */
class telefono_cliente extends Model{
  protected $table = "telefono_cliente";
}