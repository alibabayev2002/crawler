<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Target extends Model
{
    use HasFactory;

    protected $fillable = [
        'status','url'
    ];

    public const PARSED = 'parsed';
    public const NOT_PARSED = 'not_parsed';
    public const PARSING = 'parsing';
}
