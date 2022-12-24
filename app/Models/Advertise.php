<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Support\Facades\Storage;

class Advertise extends Model
{
    use HasFactory;

    protected $fillable = [
        'url', 'category', 'floor',
        'area', 'document_type', 'repair',
        'additional', 'description', 'phones',
        'longitude', 'latitude', 'identifier',
        'address', 'name', 'images', 'room_count',
        'username', 'price', 'land', 'district'
    ];

    protected $casts = [
        'additional' => 'array',
        'phones' => 'array',
        'images' => 'array',
    ];

    public function getImagesAttribute()
    {
        $images = json_decode($this->getAttributes()['images'], true);
        $returnArray = [];

        if ($images)
            foreach ($images as $image) {
                $returnArray[] = Storage::disk('digitalocean')->url($image);
            }

        return $returnArray;
    }
}
