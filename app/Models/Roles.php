<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class Roles extends Model
{
    protected $table = 'roles';  // Make sure this matches your table name

    protected $fillable = ['designation', 'description'];

    public function users()
    {
        return $this->hasMany(User::class);
    }
}
