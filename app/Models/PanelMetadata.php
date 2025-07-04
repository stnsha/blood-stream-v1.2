<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;

class PanelMetadata extends Model
{
    use HasFactory, SoftDeletes;

    protected $fillable = [
        'panel_item_id',
        'ordinal_id',
        'type',
        'identifier'
    ];
}
