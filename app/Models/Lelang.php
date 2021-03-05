<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class Lelang extends Model
{

    /**
     * The attributes that are mass assignable.
     *
     * @var array
     */
    protected $guarded = [];

    public function user() {
        return $this->belongsTo('App\Models\User', 'penawar_id', 'id');
    }

    public function barang() {
        return $this->belongsTo('App\Models\Barang', 'barang_id', 'id');
    }
}
