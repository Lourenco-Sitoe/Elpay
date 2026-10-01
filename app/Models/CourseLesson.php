<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;

class CourseLesson extends Model
{
    use HasFactory;

    protected $fillable = [
        'module_id', 'title', 'kind', 'body', 'url',
        'file_path', 'position', 'duration_minutes',
    ];

    public function module(): BelongsTo
    {
        return $this->belongsTo(CourseModule::class, 'module_id');
    }

    public function progress(): HasMany
    {
        return $this->hasMany(LessonProgress::class, 'lesson_id');
    }

    public function progressFor(int $userId): ?LessonProgress
    {
        return $this->progress->firstWhere('user_id', $userId)
            ?? LessonProgress::where('lesson_id', $this->id)->where('user_id', $userId)->first();
    }
}
