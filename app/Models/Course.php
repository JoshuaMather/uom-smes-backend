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

    protected $appends = ['average_attendance'];

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

     /**
     * Get the average attendance for the course.
     */
    public function getAverageAttendanceAttribute() 
    {
        $averageAttendance = 0;

        $activities = Activity::where('course', $this->id)->pluck('id');

        $courseActivityList = StudentActivity::whereIn('activity', $activities)->get()->groupBy('student');

        foreach ($courseActivityList as $studentActivities) {
            $averageAttendance += $studentActivities->sum('attended') / count($studentActivities);
            
        }
        $averageAttendance = $averageAttendance / count($courseActivityList);
        $averageAttendance = number_format((float)$averageAttendance, 2);

        return $averageAttendance;
    }
}
