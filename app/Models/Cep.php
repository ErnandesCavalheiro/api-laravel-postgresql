<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Cep extends Model
{
    use HasFactory;

    protected $fillable = [
        'cep' => 'required|string',
        'logradouro' => 'required|string',
        'bairro' => 'required|string',
        'localidade' => 'required|string',
        'uf' => 'required|string' 
    ];

    public static function findByCep($cep)
    {
        return self::where('cep', $cep)->first();
    }

    public static function getAll()
    {
        return self::all();
    }
}
