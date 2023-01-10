<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Student extends Model
{
    use HasFactory;

     /**
     * The table associated with the model.
     *
     * @var string
     */
    protected $table = 'students';

    /**
     * The attributes that are mass assignable.
     *
     * @var array<int, string>
     */
    protected $fillable = [
        'user',
        'personal_tutor',
        'year',
    ];

    protected $appends = ['engagement'];


    /**
     * Get user the student belongs to.
     */
    public function user()
    {
        return $this->belongsTo(User::class, 'user');
    }

    /**
     * Get student's personal tutor.
     */
    public function personal_tutor()
    {
        return $this->belongsTo(Tutor::class, 'personal_tutor');
    }

    /**
     * Get student activity relation.
     */
    public function studentActivity()
    {
        return $this->hasMany(StudentActivity::class, 'student');
    }

    /**
     * Get student assignment relation.
     */
    public function studentAssignment()
    {
        return $this->hasMany(StudentAssignment::class, 'student');
    }

    /**
     * Get student course relation.
     */
    public function studentCourse()
    {
        return $this->hasMany(StudentCourse::class, 'student');
    }

    /**
     * Get student's last logins/git push.
     */
    public function studentLast()
    {
        return $this->hasMany(StudentLast::class, 'student')->orderBy('datetime', 'DESC');
    }

    /**
     * Get concerns the student belongs to.
     */
    public function concerns()
    {
        return $this->hasMany(Concern::class, 'student');
    }

     /**
     * Get the current and predicted grades, attendance and engagement score for the student.
     */
    public function getEngagementAttribute() 
    {
        $studentCourseInfo = StudentCourse::where('student', $this->id)->get();
        $attendance = 0;
        $attendEngagement = 0;
        $grade = 0;
        $maxGrade = 0;
        $predict = 0;
        $engagement = 0;

        if(count($studentCourseInfo) !== 0) {
            foreach ($studentCourseInfo as $course) {
                $attendanceData = $course->attendance;
                $attendance += $attendanceData['attendance'];
                $attendEngagement += $attendanceData['attend_engagement'];

                $grade += $course->grades['current'];
                $maxGrade += $course->grades['max_current'];
                $predict += $course->grades['predict'];
            }
            $attendance = $attendance / count($studentCourseInfo);
            $attendance = round($attendance, 2);
            $attendEngagement = $attendEngagement / count($studentCourseInfo);
            $attendEngagement = round($attendEngagement, 2);

            $grade = $grade / count($studentCourseInfo);
            $grade = round($grade, 2);
            $maxGrade = $maxGrade / count($studentCourseInfo);
            $maxGrade = round($maxGrade, 2);
            $predict = $predict / count($studentCourseInfo);
            $predict = round($predict, 2);
        }

        if($maxGrade!=0) {
            $engagement = (0.3*($grade/$maxGrade)) + (0.3 * $predict) + (0.4*$attendEngagement);
            $engagement = round($engagement, 2);

        }

        return [
            'attendance' => $attendance,
            'predict' => $predict,
            'current' => $grade,
            'max_current' => $maxGrade,
            'engagement' => $engagement,
        ];
    }
}
