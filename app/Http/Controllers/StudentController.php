<?php

namespace App\Http\Controllers;

use App\Models\Student;
use App\Models\Tutor;
use App\Models\User;
use Illuminate\Http\Request;

class StudentController extends Controller
{
    /**
     * Retrieve all students from a request made by a tutor.
     *
     * @return \Illuminate\Http\Response
     */
    public function studentList(Request $request)
    {
        // the tutor that made the request
        $tutorId = $request->tutor;
        $tutor = Tutor::find($tutorId);

        $studentList = [];
        if($tutor->role === 'admin'){
            $studentList = Student::with('user', 'personal_tutor.user')->withCount('concerns')->get();
        } elseif ($tutor->role === 'year_tutor') {
            $studentList = Student::where('year', $tutor->year)->with('user', 'personal_tutor.user')->withCount('concerns')->get();
        } else {
            $studentList = Student::where('personal_tutor', $tutorId)->with('user', 'personal_tutor.user')->withCount('concerns')->get();
        }

        return response(['students' => $studentList]);
    }

     /**
     * Get data for a student.
     *
     * @return \Illuminate\Http\Response
     */
    public function studentData(Request $request)
    {
        // the student that the data is for 
        $studentId = $request->student;
        $userId = $request->user;
        $student = Student::where('id', $studentId)->with('user', 'studentCourse.course', 'studentActivity.activity', 'studentAssignment.assignment.course', 'studentLast', 'concerns', 'personal_tutor.user')->first();

        // if tutor making request append whole course grade distribution
        $user = User::where('id', $userId)->with('tutor')->first();
        if($user->tutor!==null) {
            $student->studentCourse->append('grade_distribution');
        }

        // engagement

        return response(['student' => $student]);
    }
}
