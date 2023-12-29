<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Note extends Model
{
    use HasFactory;

    public function task()
    {
        return $this->belongsTo(Task::class);
    }
    
    public function getAttachmentsAttribute($value)
    {
        $data = json_decode($value, true);
        $link = [];
        foreach ((array) $data as $key => $url) {
            $link[$key] = \Storage::disk('public')->url($url);
        }
        return $link;
    }
}
