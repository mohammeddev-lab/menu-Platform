<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class MenuView extends Model
{
    use HasFactory;

    public $timestamps = false;

    protected $fillable = [
        'restaurant_id',
        'ip_address',
        'user_agent',
        'device_type',
        'viewed_at',
    ];

    protected $casts = [
        'viewed_at' => 'datetime',
    ];

    public function restaurant()
    {
        return $this->belongsTo(Restaurant::class);
    }
}
