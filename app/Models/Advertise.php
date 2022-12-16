<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Advertise extends Model
{
    use HasFactory;

    protected $fillable = [
        'url', 'category', 'floor', 'area', 'document_type', 'repair', 'additional', 'description', 'phones','longitude','latitude','identifier'
    ];

    protected $casts = [
        'additional' => 'array',
        'phones' => 'array',
    ];
}
