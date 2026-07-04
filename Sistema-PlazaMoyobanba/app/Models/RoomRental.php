<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class RoomRental extends Model
{
    use HasFactory;

    protected $fillable = [
        'sale_id',
        'room_id',
        'start_at',
        'end_at',
        'hours',
        'days',
        'rate',
        'subtotal',
    ];

    public function room()
    {
        return $this->belongsTo(Room::class);
    }
}
