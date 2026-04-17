<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;

/**
 * class Role
 * tableName: roles
 *
 * @property $id
 * @property $name
 * @property $value
 * @property $deleted_at
 * @property $deleted_by
 * @property $created_by
 * @property $created_at
 * @property $updated_at
 * */
class Role extends Model
{
    use SoftDeletes;


    protected $fillable = [
        'name',
        'value',
        'deleted_by',
        'created_by',
        'deleted_at',
    ];
    public $timestamps = true;
    protected $casts = [
        'created_at' => 'datetime',
        'updated_at' => 'datetime',
    ];

    public function users() {
        return $this->hasOne(User::class);
    }

    public function getActiveUsers(){
        return Role::with(['user' => function ($query) {
            $query->whereNull('deleted_at');
        }])->get();
    }

}
