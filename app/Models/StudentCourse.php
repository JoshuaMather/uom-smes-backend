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

    protected $appends = ['grades', 'attendance'];

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
    public function getAttendanceAttribute() 
    {
        $student = $this->student;
        $course = $this->course;

        $attended = StudentActivity::join('activity', 'student_activity.activity', '=', 'activity.id')->where([
            ['student', $student],
            ['attended', 1],
            ['activity.course', $course]
        ])->count();
        
        $total = StudentActivity::join('activity', 'student_activity.activity', '=', 'activity.id')->where([
            ['student', $student],
            ['activity.course', $course]
        ])->count();

        $attendance = 0;
        if($total !== 0) {
            $attendance = $attended / $total;
        }
        $attendance = round($attendance, 2);

        return $attendance;
    }

    /**
     * Get the predicted grade for the student for the course.
     */
    public function getGradesAttribute() 
    {
        $student = $this->student;
        $course = $this->course;

        $assignmentsForCourse = Assignment::where('course', $course)->get();
        $grade = 0;
        $weightSummative = 0; // need to adjust weights to just include summative
        $prediction = 0;
        $currentAverageGrade = 0;
        $count = 0;
        $upcoming = [];
        foreach ($assignmentsForCourse as $assignment) {
            $assignmentByStudent = StudentAssignment::where('student', $student)->where('assignment', $assignment->id)->get();

            if(str_contains($assignment->type, '_s')){
                $weightSummative += $assignment->engagement_weight;
                if($assignmentByStudent[0]->grade===null){
                    $grade += 0;
                    array_push($upcoming, $assignment);
                } else {
                    $grade += $assignmentByStudent[0]->grade * $assignment->engagement_weight;
                }
            }

            if($assignmentByStudent[0]->grade===null){
                $prediction += 0;
            } else {
                $currentAverageGrade += $assignmentByStudent[0]->grade;
                $count += 1; 
            }
        }

        $prediction = $grade;
        if($weightSummative!=0){
            $grade = ($grade / $weightSummative); // scale grade - summative weights may not add to 1 if there are formative assignments
            $grade = round($grade, 2);
        }

        if($count!=0){
            $currentAverageGrade = ($currentAverageGrade / $count);
            $currentAverageGrade = round($currentAverageGrade, 2);
        }
        // apply current grade including formative to upcoming summative
        $upcomingPredict = 0;
        foreach ($upcoming as $element) {
            $upcomingPredict += $currentAverageGrade * $element->engagement_weight;
        }

        $prediction += $upcomingPredict;
        if($weightSummative!=0){
            $prediction = ($prediction / $weightSummative);
            $prediction = round($prediction, 2);
        }

        return [
            'predict' => $prediction,
            'current' => $grade,
            'average' => $currentAverageGrade,
            'upcoming' => $upcoming
        ];
    }

    /**
     * Get the grade distribution for the course.
     */
    public function getGradeDistributionAttribute() 
    {
        $distribution = [];
        $current = [0,0,0,0,0,0,0,0,0,0];
        $predicted = [0,0,0,0,0,0,0,0,0,0];
        $studentCourses = StudentCourse::where('course', $this->course)->get();
        
        foreach ($studentCourses as $course) {
            $currentG = $course->grades['current'];
            if($currentG>=0 &&$currentG<=0.1) {
                $current[0] += 1;
            } else if($currentG>0.1 && $currentG<=0.2) {
                $current[1] += 1;
            } else if($currentG>0.2 && $currentG<=0.3) {
                $current[2] += 1;
            }  else if($currentG>0.3 && $currentG<=0.4) {
                $current[3] += 1;
            }  else if($currentG>0.4 && $currentG<=0.5) {
                $current[4] += 1;
            }  else if($currentG>0.5 && $currentG<=0.6) {
                $current[5] += 1;
            }  else if($currentG>0.6 && $currentG<=0.7) {
                $current[6] += 1;
            }  else if($currentG>0.7 && $currentG<=0.8) {
                $current[7] += 1;
            }  else if($currentG>0.8 && $currentG<=0.9) {
                $current[8] += 1;
            }  else if($currentG>0.9 && $currentG<=1) {
                $current[9] += 1;
            }     

            $predictG = $course->grades['predict'];
            if($predictG>=0 &&$predictG<=0.1) {
                $predicted[0] += 1;
            } else if($predictG>0.1 && $predictG<=0.2) {
                $predicted[1] += 1;
            } else if($predictG>0.2 && $predictG<=0.3) {
                $predicted[2] += 1;
            }  else if($predictG>0.3 && $predictG<=0.4) {
                $predicted[3] += 1;
            }  else if($predictG>0.4 && $predictG<=0.5) {
                $predicted[4] += 1;
            }  else if($predictG>0.5 && $predictG<=0.6) {
                $predicted[5] += 1;
            }  else if($predictG>0.6 && $predictG<=0.7) {
                $predicted[6] += 1;
            }  else if($predictG>0.7 && $predictG<=0.8) {
                $predicted[7] += 1;
            }  else if($predictG>0.8 && $predictG<=0.9) {
                $predicted[8] += 1;
            }  else if($predictG>0.9 && $predictG<=1) {
                $predicted[9] += 1;
            }     
        }

        array_push($distribution, ['label' => '0-10', 'current' => $current[0], 'predicted' => $predicted[0]]);
        array_push($distribution, ['label' => '11-20', 'current' => $current[1], 'predicted' => $predicted[1]]);
        array_push($distribution, ['label' => '21-30', 'current' => $current[2], 'predicted' => $predicted[2]]);
        array_push($distribution, ['label' => '31-40', 'current' => $current[3], 'predicted' => $predicted[3]]);
        array_push($distribution, ['label' => '41-50', 'current' => $current[4], 'predicted' => $predicted[4]]);
        array_push($distribution, ['label' => '51-60', 'current' => $current[5], 'predicted' => $predicted[5]]);
        array_push($distribution, ['label' => '61-70', 'current' => $current[6], 'predicted' => $predicted[6]]);
        array_push($distribution, ['label' => '71-80', 'current' => $current[7], 'predicted' => $predicted[7]]);
        array_push($distribution, ['label' => '81-90', 'current' => $current[8], 'predicted' => $predicted[8]]);
        array_push($distribution, ['label' => '91-100', 'current' => $current[9], 'predicted' => $predicted[9]]);
        
        return $distribution;
    }
}
