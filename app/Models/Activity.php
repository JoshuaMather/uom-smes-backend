<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Activity extends Model
{
    use HasFactory;

    /**
     * The table associated with the model.
     *
     * @var string
     */
    protected $table = 'activity';

    /**
     * The attributes that are mass assignable.
     *
     * @var array<int, string>
     */
    protected $fillable = [
        'course',
        'activity_name',
        'engagement_weight',
    ];

    /**
     * Get course the activity belongs to.
     */
    public function course()
    {
        return $this->belongsTo(Course::class, 'course');
    }

    /**
     * Get student activity relation.
     */
    public function studentActivity()
    {
        return $this->hasMany(StudentActivity::class, 'activity');
    }
}
