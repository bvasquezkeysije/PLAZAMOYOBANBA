<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class GuestRegister extends Model
{
    use HasFactory;

    protected $fillable = [
        'code',
        'sale_id',
        'room_id',
        'created_by',
        'full_name',
        'document_type',
        'document_number',
        'nationality',
        'check_in_at',
        'check_out_at',
        'status',
        'notes',
    ];

    public function room()
    {
        return $this->belongsTo(Room::class);
    }

    public function sale()
    {
        return $this->belongsTo(Sale::class);
    }

    public function creator()
    {
        return $this->belongsTo(User::class, 'created_by');
    }
}

