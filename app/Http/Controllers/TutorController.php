<?php

namespace App\Http\Controllers;

use App\Models\Concern;
use App\Models\Course;
use App\Models\Student;
use App\Models\StudentActivity;
use App\Models\StudentAssignment;
use App\Models\StudentCourse;
use App\Models\Tutor;
use App\Models\TutorRequest;
use App\Models\User;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Hash;

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

        $concernsList = Concern::where('student', $studentId)->with('tutor.user')->get();

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
        
        $studentList = Student::join('student_course', 'students.id', '=', 'student_course.student')->where('student_course.course', $courseId)->with('user', 'personal_tutor.user', 'studentCourse')->withCount('concerns')->get();
        foreach ($studentList as $student) {
            $student->studentCourse = $student->studentCourse->filter(function ($item) use($course) {
                return $item->course==$course->id;
            })->values();
            $student->courseActivity = StudentActivity::join('activity', 'student_activity.activity', '=', 'activity.id')->where([['student', $student->id, ], ['activity.course', $courseId]])->get();
            $student->courseAssignments = StudentAssignment::join('assignment', 'student_assignment.assignment', '=', 'assignment.id')->where([['student', $student->id, ], ['assignment.course', $courseId]])->get();
        }

        $distribution = [];
        $current = [0,0,0,0,0,0,0,0,0,0];
        $predicted = [0,0,0,0,0,0,0,0,0,0];
        $studentCourses = StudentCourse::where('course', $courseId)->get();
        
        foreach ($studentCourses as $courseA) {
            $currentG = $courseA->grades['current'];
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
        

        return response([
            'success' => 200,
            'students' => $studentList,
            'course' => $course,
            'distribution' => $distribution
        ]);
    }
}
