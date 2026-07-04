<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Room extends Model
{
    use HasFactory;

    protected $fillable = [
        'code',
        'floor_id',
        'room_number',
        'type',
        'hourly_rate',
        'daily_rate',
        'active',
    ];

    public function floor()
    {
        return $this->belongsTo(Floor::class);
    }
}
