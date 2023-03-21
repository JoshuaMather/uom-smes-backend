<?php

namespace App\Http\Controllers;

use App\Models\MitCirc;
use App\Models\Student;
use App\Models\StudentAssignment;
use App\Models\StudentAssignmentMitCirc;
use App\Models\Tutor;
use App\Models\User;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Mail;

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
        $tutor = Tutor::where('id', $tutorId)->first();

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
        $student = Student::where('id', $studentId)->with('user', 'studentCourse.course', 'studentActivity.activity', 'studentAssignment.assignment', 'studentLast', 'concerns', 'personal_tutor.user')->first();
        foreach ($student->studentAssignment as $assignment) {
            $aId = $assignment->assignment;
            $studentAssignment = StudentAssignment::where([
                ['student', $studentId],
                ['assignment', $aId]
            ])->get();
            $samc = StudentAssignmentMitCirc::where('student_assignment', $studentAssignment[0]->id)->get();
            $mitCircList = [];
            foreach ($samc as $a) {
                array_push($mitCircList, MitCirc::where('id', $a->mit_circ)->get()[0]);
            }
            $assignment->mitcircs = $mitCircList;
        }

        return response(['student' => $student]);
    }

    /**
     * Email issue reported by student.
     *
     * @return \Illuminate\Http\Response
     */
    public function reportIssue(Request $request)
    {
        // the student that the data is for 
        $studentId = $request->student;
        $issue = $request->issue;
        $student = Student::where('id', $studentId)->with('user', 'personal_tutor.user')->first();
        $user = User::where('id', $student->user)->first();
        $personalTutor = User::where('id', $student->personal_tutor)->first();

        $data = array(
            'issue'=>$issue,
            'student_name'=>$user->name,
            'student_username'=>$user->name,
            'student_email'=>$user->email,
            'personal_tutor_email'=>$personalTutor->email
        );

        Mail::send(['text'=>'student-issue-mail'], $data, function($message) {
            $message->to('joshua.mather@student.manchester.ac.uk', 'TEST')->subject
                ('UOM SMES - Student Reported Issue');
            $message->from('joshua.mather@student.manchester.ac.uk','TEST');
        });

        return response(['success' => 200]);
    }
}
