<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Course extends Model
{
    use HasFactory;

    /**
     * The table associated with the model.
     *
     * @var string
     */
    protected $table = 'course';

    /**
     * The attributes that are mass assignable.
     *
     * @var array<int, string>
     */
    protected $fillable = [
        'course_name',
        'course_code',
        'tutor',
    ];

    /**
     * Get tutor the course is run by.
     */
    public function tutor()
    {
        return $this->hasOne(Tutor::class, 'tutor');
    }

    /**
     * Get student course relation.
     */
    public function studentCourse()
    {
        return $this->hasMany(StudentCourse::class, 'course');
    }

    /**
     * Get activities for the course.
     */
    public function activities()
    {
        return $this->hasMany(Activity::class, 'course');
    }

    /**
     * Get assignments for the course.
     */
    public function assignments()
    {
        return $this->hasMany(Assignment::class, 'course');
    }
}
