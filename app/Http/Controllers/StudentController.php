<?php

namespace App\Http\Controllers;

use App\Models\Student;
use App\Models\Tutor;
use Illuminate\Http\Request;

class StudentController extends Controller
{
    /**
     * Display a listing of the resource.
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
            $studentList = Student::with('user', 'personal_tutor.user')->get();
        } elseif ($tutor->role === 'year_tutor') {
            $studentList = Student::where('year', $tutor->year)->with('user', 'personal_tutor.user')->get();
        } else {
            $studentList = Student::where('personal_tutor', $tutorId)->with('user', 'personal_tutor.user')->get();
        }

        return response(['students' => $studentList]);
    }
}
