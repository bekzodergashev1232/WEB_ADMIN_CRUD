<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

/**
 * class  Viloyat
 * tableName: viloyatlar
 *
 * @property $id
 * @property $name_uz
 * @property $name_ru
 * @property $viloyat_bill_id
 * @property $soato
 * @property $code
 * @property $old_id
 * @property $is_active
 * @property $created_at
 * @property $updated_at
 *
 * */
class Viloyat extends Model
{
    protected $table = 'viloyatlar';
}
