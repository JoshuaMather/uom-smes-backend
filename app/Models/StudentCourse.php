<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class StudentCourse extends Model
{
    use HasFactory;

    /**
     * The table associated with the model.
     *
     * @var string
     */
    protected $table = 'student_course';

    /**
     * The attributes that are mass assignable.
     *
     * @var array<int, string>
     */
    protected $fillable = [
        'student',
        'course',
    ];

    protected $appends = ['predicted_grade', 'current_grade'];

    /**
     * Get the student.
     */
    public function student()
    {
        return $this->belongsTo(Student::class, 'student');
    }

    /**
     * Get the course.
     */
    public function course()
    {
        return $this->belongsTo(Course::class, 'course');
    }

    /**
     * Get the predicted grade for the student for the course.
     */
    public function getPredictedGradeAttribute() 
    {
        $student = $this->student;
        $course = $this->course;

        $assignmentsForCourse = Assignment::where('course', $course)->get();
        $prediction = 0;
        foreach ($assignmentsForCourse as $assignment) {
            $assignmentByStudent = StudentAssignment::where('student', $student)->where('assignment', $assignment->id)->get();
            $prediction += $assignmentByStudent[0]->grade * $assignment->engagement_weight;
        }

        $prediction = round($prediction, 2);

        return $prediction;
    }

    /**
     * Get the current grade for the student for the course.
     */
    public function getCurrentGradeAttribute() 
    {
        $student = $this->student;
        $course = $this->course;

        $assignmentsForCourse = Assignment::where('course', $course)->get();
        $grade = 0;
        $weight = 0; // need to adjust weights to just include summative
        foreach ($assignmentsForCourse as $assignment) {
            $assignmentByStudent = StudentAssignment::where('student', $student)->where('assignment', $assignment->id)->get();
            if(str_contains($assignment->type, '_s')){
                $weight += $assignment->engagement_weight;
                $grade += $assignmentByStudent[0]->grade * $assignment->engagement_weight;
            }
        }

        $grade = ($grade / $weight);
        $grade = round($grade, 2);

        return $grade;
    }
}
