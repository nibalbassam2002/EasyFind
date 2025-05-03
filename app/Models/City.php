<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use App\Models\User;

class City extends Model
{
    //
    public function users()
{
    return $this->hasMany(User::class, 'city_id');
}
}
