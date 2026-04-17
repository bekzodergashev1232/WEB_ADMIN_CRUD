<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

/**
 * class Tuman
 * tableName: tumanlar
 *
 * @property $id
 * @property $name_uz
 * @property $name_ru
 * @property $name_en
 * @property $viloyat_soato
 * @property $soato
 * @property $is_active
 * @property $viloyat_id
 * @property $old_id
 * @property $created_at
 * @property $updated_at
 *
 * */
class Tuman extends Model
{
    protected $table = 'tumanlar';
}
