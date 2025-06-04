<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;

class DeliveryFile extends Model
{
    use HasFactory, SoftDeletes;

    protected $fillable = [
        'delivery_info_id',
        'sending_facility',
        'file_id', //MessageControlId
        'test_result_id',
        'status',
    ];
}
