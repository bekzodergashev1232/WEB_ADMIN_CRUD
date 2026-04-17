<?php

namespace App\Models;

// use Illuminate\Contracts\Auth\MustVerifyEmail;
use Database\Factories\UserFactory;
use Illuminate\Database\Eloquent\Attributes\Fillable;
use Illuminate\Database\Eloquent\Attributes\Hidden;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\SoftDeletes;
use Illuminate\Foundation\Auth\User as Authenticatable;
use Illuminate\Notifications\Notifiable;
use Laravel\Passport\HasApiTokens;

/**
 * class User
 * tableName: users
 *
 * @property $id
 * @property $name
 * @property $email
 * @property $email_verified_at
 * @property $password
 * @property $remember_token
 * @property $updated_at
 * @property $created_at
 * @property $phone
 * @property $nick_name
 * @property $address
 * @property $viloyat_id
 * @property $tuman_id
 * @property $role_id
 * @property $created_by
 * @property $updated_by
 * @property $deleted_by
 * @property $deleted_at
 *
 * */

#[Hidden(['password', 'remember_token'])]
class User extends Authenticatable
{
    /** @use HasFactory<UserFactory> */
    use HasApiTokens, HasFactory, Notifiable, SoftDeletes;

    /**
     * Get the attributes that should be cast.
     *
     * @return array<string, string>
     */
    protected $fillable = [
        'name',
        'email',
        'password',
        'phone',
        'nick_name',
        'address',
        'viloyat_id',
        'tuman_id',
        'role_id',
        'created_by',
        'updated_by',
        'deleted_by',
    ];
    const SUPER_ADMIN = 1;
    const ADMIN = 2;
    const USER = 3;
    public $timestamps = true;
    protected function casts(): array
    {
        return [
            'email_verified_at' => 'datetime',
            'password' => 'hashed',
            'created_at' => 'datetime',
            'updated_at' => 'datetime',
        ];
    }
    public function role()
    {
        return $this->belongsTo(Role::class);
    }
}
