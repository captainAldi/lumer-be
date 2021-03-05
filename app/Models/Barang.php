<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class Barang extends Model
{

    /**
     * The attributes that are mass assignable.
     *
     * @var array
     */
    protected $guarded = [];

    public function lelangs() {
        return $this->hasMany('App\Models\Lelang', 'barang_id', 'id');
    }
}
