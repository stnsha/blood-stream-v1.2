<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;

class PanelItems extends Model
{
    use HasFactory, SoftDeletes;

    protected $fillable = [
        'panel_id',
        'name',
        'decimal_point',
        'unit',
        'ordinal_id',
        'type',
        'identifier'
    ];
}
