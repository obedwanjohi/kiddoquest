<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Factories\HasFactory;

class WorldLesson extends Model
{
    use HasFactory;

    protected $fillable = [
        'adventure_world_id',
        'lesson_id',
        'story_title',
        'sort_order',
    ];

    public function adventureWorld()
    {
        return $this->belongsTo(AdventureWorld::class);
    }

    public function lesson()
    {
        return $this->belongsTo(Lesson::class);
    }
}