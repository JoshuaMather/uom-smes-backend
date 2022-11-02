<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Assignment extends Model
{
    use HasFactory;

    /**
     * The table associated with the model.
     *
     * @var string
     */
    protected $table = 'assignment';

    /**
     * The attributes that are mass assignable.
     *
     * @var array<int, string>
     */
    protected $fillable = [
        'course',
        'assignment_name',
        'due_date',
        'type',
        'engagement_weight',
    ];

    /**
     * Get course the assignment belongs to.
     */
    public function course()
    {
        return $this->belongsTo(Course::class, 'course');
    }

    /**
     * Get student assignment relation.
     */
    public function studentAssignment()
    {
        return $this->hasMany(StudentAssignment::class, 'assignment');
    }
}
