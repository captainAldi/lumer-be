<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class User extends Model
{

    /**
     * The attributes that are mass assignable.
     *
     * @var array
     */
    protected $fillable = [
        'name', 
        'email', 
        'password', 
        'role', 
        'rememberToken', 
        'api_token',
        'email_verified_at'
    ];

    /**
     * The attributes excluded from the model's JSON form.
     *
     * @var array
     */
    protected $hidden = [
        'password', 'rememberToken', 'api_token'
    ];

    public function lelangs() {
        return $this->hasMany('App\Models\Lelang', 'penawar_id', 'id');
    }
}
