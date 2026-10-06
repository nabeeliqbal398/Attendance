<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

/**
 * Region — administrative regions (province / district / sub-district / village).
 * Replaces the legacy `Wilayah` model that pointed at the `wilayah` table.
 *
 * The hierarchy is encoded in the `code` length:
 *   - 2 chars  → province
 *   - 5 chars  → district / regency
 *   - 8 chars  → sub-district
 *   - 13 chars → village
 */
class Region extends Model
{
    protected $table = 'regions';
    protected $primaryKey = 'code';
    protected $keyType = 'string';
    public $incrementing = false;
    public $timestamps = false;

    protected $fillable = ['code', 'name'];
}
