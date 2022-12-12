<?php

namespace App\Http\Controllers;

use App\Models\Concern;
use App\Models\Student;
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

        // $concernsList = Concern::where('student', $studentId)->with('tutor.user')->get();

        return response(['success' => 200]);
    }
}
