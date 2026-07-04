<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Sale extends Model
{
    use HasFactory;

    protected $fillable = [
        'code',
        'document_type',
        'series',
        'correlative',
        'client_id',
        'user_id',
        'total',
        'subtotal',
        'igv',
        'status',
        'payment_type_id',
    ];

    public function items()
    {
        return $this->hasMany(SaleItem::class);
    }

    public function rentals()
    {
        return $this->hasMany(RoomRental::class);
    }

    public function client()
    {
        return $this->belongsTo(Client::class);
    }

    public function paymentType()
    {
        return $this->belongsTo(PaymentType::class);
    }

    public function user()
    {
        return $this->belongsTo(User::class);
    }
}
