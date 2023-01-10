<?php

namespace App\Http\Controllers\Auth;

use App\Http\Controllers\Controller;
use App\Models\Course;
use App\Models\User;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\Validator;
use Illuminate\Support\Str;

class ApiAuthController extends Controller
{
    public function login (Request $request) {
        $validator = Validator::make($request->all(), [
            'username' => 'required|string',
            'password' => 'required|string',
        ]);
        if ($validator->fails())
        {
            return response(['errors'=>$validator->errors()->all()], 422);
        }


        $user = User::where('username', $request->username)->with('tutor', 'tutor.course.assignments', 'student')->first();
        // only have student or tutor
        if($user->tutor===null){
            unset($user->tutor);
        } else if($user->student===null) {
            unset($user->student);  
        }

        $courses = Course::with('assignments')->get();

        if ($user) {
            if (Hash::check($request->password, $user->password)) {
                $token = Str::random(60);
 
                $user->forceFill([
                    'api_token' => hash('sha256', $token),
                ])->save();
        
                $response = ['token' => $token, 'user' => $user, 'all_courses' => $courses];
                return response($response, 200);
            } else {
                $response = ["message" => "Incorrect Login Details"];
                return response($response, 422);
            }
        } else {
            $response = ["message" =>'User Does Not Exist'];
            return response($response, 422);
        }
    }
}
