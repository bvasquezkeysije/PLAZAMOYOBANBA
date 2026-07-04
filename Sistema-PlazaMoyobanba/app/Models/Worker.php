<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Spatie\Permission\Models\Role;

class Worker extends Model
{
    use HasFactory;

    protected $fillable = [
        'code',
        'full_name',
        'document_number',
        'phone',
        'email',
        'address',
        'role_id',
        'is_active',
    ];

    protected $casts = [
        'is_active' => 'boolean',
    ];

    public function role()
    {
        return $this->belongsTo(Role::class, 'role_id');
    }
}

