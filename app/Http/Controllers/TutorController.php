<?php

namespace App\Http\Controllers;

use App\Models\Assignment;
use App\Models\Concern;
use App\Models\Course;
use App\Models\Student;
use App\Models\StudentActivity;
use App\Models\StudentAssignment;
use App\Models\StudentCourse;
use App\Models\Tutor;
use App\Models\TutorRequest;
use App\Models\User;
use Carbon\Carbon;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\Mail;

class TutorController extends Controller
{
     /**
     * Retrieve all tutors.
     *
     * @return \Illuminate\Http\Response
     */
    public function index(Request $request)
    {
        // the list of all tutor
        $tutorList = Tutor::with('user', 'course')->get();

        return response(['tutors' => $tutorList]);
    }

    /**
     * Report concern about a student.
     *
     * @return \Illuminate\Http\Response
     */
    public function reportConcern(Request $request)
    {
        $newConcern = new Concern;
        $newConcern->tutor = $request->tutor;
        $newConcern->student = $request->student;
        $newConcern->concern = $request->concern;
        $newConcern->date_reported = Carbon::now();
        $newConcern->save();

        return response(['success' => 200]);
    }

    /**
     * Get concerns for a student.
     *
     * @return \Illuminate\Http\Response
     */
    public function getConcerns(Request $request)
    {
        $studentId = $request->student;

        $concernsList = Concern::where('student', $studentId)->with('tutor.user')->orderBy('date_reported','DESC')->get();

        return response(['concerns' => $concernsList]);
    }

    /**
     * Register new tutor to be accepted.
     *
     * @return \Illuminate\Http\Response
     */
    public function requestRegister(Request $request)
    {
        //check if details already exist
        if (User::where('username', '=', $request->username)->count() > 0 || TutorRequest::where('username', '=', $request->username)->count() > 0) {
            return response(
                ['success' => 400,
                'error' => 'Tutor with given username already exists']
        );
        }
        
        if (User::where('email', '=', $request->email)->count() > 0 || TutorRequest::where('email', '=', $request->email)->count() > 0) {
            return response(
                ['success' => 400,
                'error' => 'Tutor with given email already exists']
        );
        }

        $newTutorRequest = new TutorRequest;
        $newTutorRequest->username = $request->username;
        $newTutorRequest->password = Hash::make($request->password);;
        $newTutorRequest->email = $request->email;
        $newTutorRequest->name = $request->name;
        if($request->role==='year_tutor'){
            $newTutorRequest->role = $request->role;
            $newTutorRequest->year = $request->year;
        } else if($request->role==='admin') {
            $newTutorRequest->role = $request->role;
        }
        $newTutorRequest->save();

        $data = array(
            'username'=>$request->username,
            'email'=>$request->email,
            'name'=>$request->name,
            'role'=>$request->role,
            'year'=>$request->year
        );
   
        Mail::send(['text'=>'register-tutor-mail'], $data, function($message) {
            $message->to('joshua.mather@student.manchester.ac.uk', 'TEST')->subject
                ('UOM SMES - New tutor requested');
            $message->from('joshua.mather@student.manchester.ac.uk','TEST');
        });

        return response(['success' => 200]);
    }

    /**
     * Get requests for new tutors.
     *
     * @return \Illuminate\Http\Response
     */
    public function getTutorRequests(Request $request)
    {
        $tutorId = $request->tutor;
        $tutor = Tutor::where('id', $tutorId)->first();
        if($tutor->role !== 'admin'){
            return response(['success' => 400]);
        }

        // return list of requests
        $tutorRequests = TutorRequest::all();

        return response(['tutorRequests' => $tutorRequests]);
    }

    /**
     * Accept requests for new tutor.
     *
     * @return \Illuminate\Http\Response
     */
    public function acceptTutorRequests(Request $request)
    {
        $tutorId = $request->tutor;
        $requestId = $request->requestId;
        $tutor = Tutor::where('id', $tutorId)->first();
        if($tutor->role !== 'admin'){
            return response(['success' => 400]);
        }

        $tutorRequest = TutorRequest::find($requestId)->first();

        $user = new User();
        $user->username = $tutorRequest->username;
        $user->email = $tutorRequest->email;
        $user->password = $tutorRequest->password;
        $user->name = $tutorRequest->name;
        $user->save();

        $tutor = new Tutor();
        $tutor->user = $user->id;
        if(!$tutorRequest->role){
            $tutor->role = '';
        } else {
            $tutor->role = $tutorRequest->role;
        }
        if(!$tutorRequest->year){
            $tutor->year = '';
        } else {
            $tutor->year = $tutorRequest->year;
        }
        $tutor->save();

        TutorRequest::find($requestId)->delete();

        return response(['success' => 200]);
    }

    /**
     * Decline requests for new tutor.
     *
     * @return \Illuminate\Http\Response
     */
    public function declineTutorRequests(Request $request)
    {
        $tutorId = $request->tutor;
        $requestId = $request->requestId;

        $tutor = Tutor::where('id', $tutorId)->first();
        if($tutor->role !== 'admin'){
            return response(['success' => 400]);
        }

        TutorRequest::find($requestId)->delete();

        return response(['success' => 200]);
    }

    /**
     * Get course info and relating students for tutor.
     *
     * @return \Illuminate\Http\Response
     */
    public function getTutorCourses(Request $request)
    {
        $tutorId = $request->tutor;
        $courseId = $request->course;
        
        $tutor = Tutor::where('id', $tutorId)->first();
        $course = Course::where('id', $courseId)->first();
        
        if($course->tutor !== $tutor->id && $tutor->role!='admin'){
            return response([
                'success' => 400,
                'error' => 'This tutor does not run this course',
                'tutor' => $tutor
            ]);
        }
        
        $studentList = Student::join('student_course', 'students.id', '=', 'student_course.student')->where('student_course.course', $courseId)->with('user', 'personal_tutor.user')->withCount('concerns')->get();
        foreach ($studentList as $student) {
            $student->studentCourse = StudentCourse::where([['student', $student->id, ], ['course', $courseId]])->get();
            $student->courseActivity = StudentActivity::join('activity', 'student_activity.activity', '=', 'activity.id')->where([['student', $student->id, ], ['activity.course', $courseId]])->get();
            $student->courseAssignments = StudentAssignment::join('assignment', 'student_assignment.assignment', '=', 'assignment.id')->where([['student', $student->id, ], ['assignment.course', $courseId]])->get();
        }

        $distribution = [];
        $current = [0,0,0,0,0,0,0,0,0,0];
        $predicted = [0,0,0,0,0,0,0,0,0,0];
        $allCurrent = [];
        $allPredicted = [];
        $studentCourses = StudentCourse::where('course', $courseId)->get();
        
        foreach ($studentCourses as $courseA) {
            $currentG = $courseA->grades['current'];
            array_push($allCurrent, $currentG);
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

            $predictG = $courseA->grades['predict'];
            array_push($allPredicted, $predictG);
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

        // stats
        $minCurrent = min($allCurrent);
        $maxCurrent = max($allCurrent);

        $meanCurrent = array_sum($allCurrent)/count($allCurrent);
        $meanCurrent = round($meanCurrent, 2);

        $sortedCurrent = $allCurrent;
        sort($sortedCurrent);
        $count = count($sortedCurrent);
        $middleval = floor(($count-1)/2);
        if ($count % 2) {
            $medianCurrent = $sortedCurrent[$middleval];
        } else {
            $low = $sortedCurrent[$middleval];
            $high = $sortedCurrent[$middleval+1];
            $medianCurrent = (($low+$high)/2);
        }

        $alterCurrent = $allCurrent;
        $alterCurrent = array_map(function($el) { return (int)($el * 100); }, $alterCurrent);
        
        $modeCurrent = '';
        $modeRangeCurrent = array_keys($current, max($current));
        if($modeRangeCurrent[0] == 0) {
            $modeCurrent = '0-10';
        } elseif($modeRangeCurrent[0] == 1) {
            $modeCurrent = '11-20';
        } elseif($modeRangeCurrent[0] == 2) {
            $modeCurrent = '21-30';
        } elseif($modeRangeCurrent[0] == 3) {
            $modeCurrent = '31-40';
        } elseif($modeRangeCurrent[0] == 4) {
            $modeCurrent = '41-50';
        } elseif($modeRangeCurrent[0] == 5) {
            $modeCurrent = '51-60';
        } elseif($modeRangeCurrent[0] == 6) {
            $modeCurrent = '61-70';
        } elseif($modeRangeCurrent[0] == 7) {
            $modeCurrent = '71-80';
        } elseif($modeRangeCurrent[0] == 8) {
            $modeCurrent = '81-90';
        } elseif($modeRangeCurrent[0] == 9) {
            $modeCurrent = '91-100';
        }

        $varianceCurrent = 0.0;
        foreach ($allCurrent as $item) {
            $varianceCurrent += pow(abs($item - $meanCurrent), 2);
        }
        $varianceCurrent = round($varianceCurrent, 2);

        $sdCurrent = sqrt($varianceCurrent);
        $sdCurrent = round($sdCurrent, 2);


        $minPredicted = min($allPredicted);
        $maxPredicted = max($allPredicted);

        $meanPredicted = array_sum($allPredicted)/count($allPredicted);
        $meanPredicted = round($meanPredicted, 2);

        $sortedPredicted = $allPredicted;
        sort($sortedPredicted);
        $count = count($sortedPredicted);
        $middleval = floor(($count-1)/2);
        if ($count % 2) {
            $medianPredicted = $sortedPredicted[$middleval];
        } else {
            $low = $sortedPredicted[$middleval];
            $high = $sortedPredicted[$middleval+1];
            $medianPredicted = (($low+$high)/2);
        }

        $alterPredicted = $allPredicted;
        $alterPredicted = array_map(function($el) { return (int)($el * 100); }, $alterPredicted);

        $modePredicted = '';
        $modeRangePredicted = array_keys($predicted, max($predicted));
        if($modeRangePredicted[0] == 0) {
            $modePredicted = '0-10';
        } elseif($modeRangePredicted[0] == 1) {
            $modePredicted = '11-20';
        } elseif($modeRangePredicted[0] == 2) {
            $modePredicted = '21-30';
        } elseif($modeRangePredicted[0] == 3) {
            $modePredicted = '31-40';
        } elseif($modeRangePredicted[0] == 4) {
            $modePredicted = '41-50';
        } elseif($modeRangePredicted[0] == 5) {
            $modePredicted = '51-60';
        } elseif($modeRangePredicted[0] == 6) {
            $modePredicted = '61-70';
        } elseif($modeRangePredicted[0] == 7) {
            $modePredicted = '71-80';
        } elseif($modeRangePredicted[0] == 8) {
            $modePredicted = '81-90';
        } elseif($modeRangePredicted[0] == 9) {
            $modePredicted = '91-100';
        }

        $variancePredicted = 0.0;
        foreach ($allPredicted as $item) {
            $variancePredicted += pow(abs($item - $meanPredicted), 2);
        }
        $variancePredicted = round($variancePredicted, 2);

        $sdPredicted = sqrt($variancePredicted);
        $sdPredicted = round($sdPredicted, 2);
        

        return response([
            'success' => 200,
            'students' => $studentList,
            'course' => $course,
            'distribution' => $distribution,
            'statsCurrent' => [
                'minCurrent' => $minCurrent,
                'maxCurrent' => $maxCurrent,
                'meanCurrent' => $meanCurrent,
                'medianCurrent' => $medianCurrent,
                'modeCurrent' => $modeCurrent,
                'varianceCurrent' => $varianceCurrent,
                'sdCurrent' => $sdCurrent
            ],
            'statsPredicted' => [
                'minPredicted' => $minPredicted,
                'maxPredicted' => $maxPredicted,
                'meanPredicted' => $meanPredicted,
                'medianPredicted' => $medianPredicted,
                'modePredicted' => $modePredicted,
                'variancePredicted' => $variancePredicted,
                'sdPredicted' => $sdPredicted
            ]
        ]);
    }

    /**
     * Get assignment info and relating course info and students for tutor.
     *
     * @return \Illuminate\Http\Response
     */
    public function getTutorCourseAssignment(Request $request)
    {
        $tutorId = $request->tutor;
        $assignmentId = $request->assignment;
        
        $tutor = Tutor::where('id', $tutorId)->first();
        $assignment = Assignment::where('id', $assignmentId)->first();
        $course = Course::where('id', $assignment->course)->first();


        if($course->tutor !== $tutor->id && $tutor->role!='admin'){
            return response([
                'success' => 400,
                'error' => 'This tutor does not run this course',
                'tutor' => $tutor
            ]);
        }

        $studentList = Student::join('student_assignment', 'students.id', '=', 'student_assignment.student')->where('student_assignment.assignment', $assignmentId)->with('user', 'personal_tutor.user')->get();

        $assignments = Assignment::where('course', $course->id)->get();
        $summativeWeight = 0;
        foreach ($assignments as $a) {
            if(str_contains($a->type, '_s')){
                $summativeWeight += $a->engagement_weight;
            }
        }
        $gradeWeight = 0;
        if($summativeWeight != 0) {
            $gradeWeight = $assignment->engagement_weight / $summativeWeight;
        };
        $assignment->gradeWeight = $gradeWeight;
        $assignment->gradeWeight = round($assignment->gradeWeight, 2);

        $averageGrade = StudentAssignment::where('assignment', $assignmentId)->pluck('grade')->avg();
        $assignment->averageGrade = $averageGrade;
        $assignment->averageGrade = round($assignment->averageGrade, 2);

        $notMarked = StudentAssignment::where('assignment', $assignmentId)->whereNull('grade')->count();
        $total = StudentAssignment::where('assignment', $assignmentId)->count();
        $marked = $total - $notMarked;


        
        $distribution = [];
        $gradeDist = [0,0,0,0,0,0,0,0,0,0];
        $gradesList = StudentAssignment::where('assignment', $assignmentId)->pluck('grade');
        
        foreach ($gradesList as $gradeVal) {
            if($gradeVal>=0 &&$gradeVal<=0.1) {
                $gradeDist[0] += 1;
            } else if($gradeVal>0.1 && $gradeVal<=0.2) {
                $gradeDist[1] += 1;
            } else if($gradeVal>0.2 && $gradeVal<=0.3) {
                $gradeDist[2] += 1;
            }  else if($gradeVal>0.3 && $gradeVal<=0.4) {
                $gradeDist[3] += 1;
            }  else if($gradeVal>0.4 && $gradeVal<=0.5) {
                $gradeDist[4] += 1;
            }  else if($gradeVal>0.5 && $gradeVal<=0.6) {
                $gradeDist[5] += 1;
            }  else if($gradeVal>0.6 && $gradeVal<=0.7) {
                $gradeDist[6] += 1;
            }  else if($gradeVal>0.7 && $gradeVal<=0.8) {
                $gradeDist[7] += 1;
            }  else if($gradeVal>0.8 && $gradeVal<=0.9) {
                $gradeDist[8] += 1;
            }  else if($gradeVal>0.9 && $gradeVal<=1) {
                $gradeDist[9] += 1;
            }     
        }

        array_push($distribution, ['label' => '0-10', 'grade' => $gradeDist[0]]);
        array_push($distribution, ['label' => '11-20', 'grade' => $gradeDist[1]]);
        array_push($distribution, ['label' => '21-30', 'grade' => $gradeDist[2]]);
        array_push($distribution, ['label' => '31-40', 'grade' => $gradeDist[3]]);
        array_push($distribution, ['label' => '41-50', 'grade' => $gradeDist[4]]);
        array_push($distribution, ['label' => '51-60', 'grade' => $gradeDist[5]]);
        array_push($distribution, ['label' => '61-70', 'grade' => $gradeDist[6]]);
        array_push($distribution, ['label' => '71-80', 'grade' => $gradeDist[7]]);
        array_push($distribution, ['label' => '81-90', 'grade' => $gradeDist[8]]);
        array_push($distribution, ['label' => '91-100', 'grade' => $gradeDist[9]]);

        // stats
        $min = min($gradesList->toArray());
        $max = max($gradesList->toArray());

        $mean = array_sum($gradesList->toArray())/count($gradesList->toArray());
        $mean = round($mean, 2);

        $median = $gradesList->median();

        $mode = '';
        $modeRange = array_keys($gradeDist, max($gradeDist));
        if($modeRange[0] == 0) {
            $mode = '0-10';
        } elseif($modeRange[0] == 1) {
            $mode = '11-20';
        } elseif($modeRange[0] == 2) {
            $mode = '21-30';
        } elseif($modeRange[0] == 3) {
            $mode = '31-40';
        } elseif($modeRange[0] == 4) {
            $mode = '41-50';
        } elseif($modeRange[0] == 5) {
            $mode = '51-60';
        } elseif($modeRange[0] == 6) {
            $mode = '61-70';
        } elseif($modeRange[0] == 7) {
            $mode = '71-80';
        } elseif($modeRange[0] == 8) {
            $mode = '81-90';
        } elseif($modeRange[0] == 9) {
            $mode = '91-100';
        }


        $variance = 0.0;
        foreach ($gradesList->toArray() as $item) {
            $variance += pow(abs($item - $mean), 2);
        }
        $variance = round($variance, 2);

        $sd = sqrt($variance);
        $sd = round($sd, 2);

        return response([
            'success' => 200,
            'students' => $studentList,
            'assignment' => $assignment,
            'distribution' => $distribution,
            'stats' => [
                'min' => $min,
                'max' => $max,
                'mean' => $mean,
                'median' => $median,
                'mode' => $mode,
                'variance' => $variance,
                'sd' => $sd
            ],
            'marked' => $marked,
            'total' => $total
        ]);
    }
}
