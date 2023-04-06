<?php

namespace App\Console\Commands;

use App\Models\Activity;
use App\Models\Assignment;
use App\Models\Course;
use App\Models\Student;
use App\Models\StudentActivity;
use App\Models\StudentAssignment;
use App\Models\StudentCourse;
use App\Models\StudentLast;
use App\Models\User;
use Illuminate\Console\Command;
use Illuminate\Support\Carbon;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Str;

class updateTable extends Command
{
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'update:table';

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = 'Helper to create database data';

    /**
     * Execute the console command.
     *
     * @return int
     */
    public function handle()
    {
        // for ($i=1; $i < 500; $i++) { 
        //     $user = User::find($i);
        //     $pass = Hash::make($user->password);
        //     // User::where('id', $i)->update(array('email' => substr($user->email, 0, strpos($user->email, '@')) . '@manchester.ac.uk'));
        //     User::where('id', $i)->update(array('password' => $pass));
        // }

        // create studentCourse entries
        // $students = Student::all();
        // $courses = Course::all()->toArray();
        // foreach ($students as $student) {
        //     $chosenCourses = array_rand($courses, 6);
        //     foreach ($chosenCourses as $courseIdx) {
        //         $studentCourse = new StudentCourse();
        //         $studentCourse->student = $student->id;
        //         $studentCourse->course = $courses[$courseIdx]['id'];
        //         $studentCourse->save();
        //     }
        // }

        // for each student taking a course create entries for assignment and activities 
        // $list = StudentCourse::all();
        // foreach ($list as $entry) {
        //     $course = $entry->course;
        //     $student = $entry->student;

        //     // create student activity data
        //     $activities = Activity::where('course', $course)->get();
        //     foreach ($activities as $activity) {
        //         for ($i=1; $i < 11; $i++) { // only first 10 weeks
        //             $chance = rand(0,100);
        //             $attend = '';
        //             if($chance > 80) {
        //                 $attend = False;
        //             } else {
        //                 $attend = True;
        //             }
        //             // have some weeks not happened yet
        //             $newEntry = ['student' => $student, 'activity' => $activity->id, 'week' => $i, 'attended' => $attend];
        //             StudentActivity::insert($newEntry);
                    
        //         }
        //     }

        //     //create student assignment data
        //     $assignments = Assignment::where('course', $course)->get();
        //     $date = '2022-12-04 18:00:00'; // used as current date

        //     foreach ($assignments as $assignment) {
        //         $chance = rand(0,100);
        //         $submitDate = '';

        //         $date1 = Carbon::create($date);
        //         $date2 = Carbon::create($assignment->due_date);
        //         $result = $date2->gt($date1);

        //         if($result) {
        //             $newEntry = ['student' => $student, 'assignment' => $assignment->id, 'date_submitted' => null, 'grade' => null];
        //             StudentAssignment::insert($newEntry);
        //         } else {
        //             if(Str::contains($assignment->type, 'exam')){
        //                 if($chance > 95) {
        //                     $submitDate = null; // didn't sit exam 
        //                 } else {
        //                     $submitDate = $assignment->due_date;
        //                 }
        //             } else{
        //                 if($chance > 80) {
        //                     $submitDate = "2023-12-01 12:00:00"; // some future date
        //                 } else {
        //                     $submitDate = $assignment->due_date;
        //                 }
        //             }

    
        //             // chance used for more realistic distribution with less studends getting a very low or very high grade
        //             $chance = rand(0,100);
        //             $grade = 0;
        //             if($chance > 80) {
        //                 $grade = mt_rand(80, 100) / 100;
        //             } elseif ($chance < 10) {
        //                 $grade = mt_rand(0, 30) / 100;
        //             } else {
        //                 $grade = mt_rand(40, 80) / 100;
        //             }
    
        //             $newEntry = ['student' => $student, 'assignment' => $assignment->id, 'date_submitted' => $submitDate, 'grade' => $grade];
        //             StudentAssignment::insert($newEntry);
        //         }
        //     }
        // }



        // add degree
        // $list = Student::all();
        // foreach ($list as $entry) {
        //     $chance = rand(0,100);
        //     $degree = '';
        //     if($chance > 70) {
        //         $degree = 'Computer Science and Maths';
        //     } elseif ($chance < 20) {
        //         $degree = 'Artificial Intelligence';
        //     } else {
        //         $degree = 'Computer Science';
        //     }

        //     $entry->degree = $degree;
        //     $entry->save();
        // }

        // $duplicated = DB::table('student_course')
        //     ->select('student', 'course')
        //     ->groupBy('student', 'course')
        //     ->havingRaw('COUNT(*) > 1')
        //     ->get();

        // foreach($duplicated as $record) {
        //     $dontDeleteThisRow = StudentCourse::where(['student'=>$record->student,'course'=>$record->course])->first();
        //     StudentCourse::where(['student'=>$record->student,'course'=>$record->course])->where('id', '!=', $dontDeleteThisRow->id)->delete();
        //     // $record->delete();
        // }

        // error_log($duplicated);

        // add week to history table
        // $list = StudentLast::whereDate('datetime', '<=', date("2022-10-02"))->whereDate('datetime', '>=', date("2022-09-26"))->get();
        // foreach ($list as $entry) {
        //     $entry->week = 1;
        //     $entry->save();
        // }
        // $list = StudentLast::whereDate('datetime', '<=', date("2022-10-09"))->whereDate('datetime', '>=', date("2022-10-03"))->get();
        // foreach ($list as $entry) {
        //     $entry->week = 2;
        //     $entry->save();
        // }
        // $list = StudentLast::whereDate('datetime', '<=', date("2022-10-16"))->whereDate('datetime', '>=', date("2022-10-10"))->get();
        // foreach ($list as $entry) {
        //     $entry->week = 3;
        //     $entry->save();
        // }
        // $list = StudentLast::whereDate('datetime', '<=', date("2022-10-23"))->whereDate('datetime', '>=', date("2022-10-17"))->get();
        // foreach ($list as $entry) {
        //     $entry->week = 4;
        //     $entry->save();
        // }
        // $list = StudentLast::whereDate('datetime', '<=', date("2022-10-30"))->whereDate('datetime', '>=', date("2022-10-24"))->get();
        // foreach ($list as $entry) {
        //     $entry->week = 5;
        //     $entry->save();
        // }
        // $list = StudentLast::whereDate('datetime', '<=', date("2022-11-06"))->whereDate('datetime', '>=', date("2022-10-31"))->get();
        // foreach ($list as $entry) {
        //     $entry->week = 6;
        //     $entry->save();
        // }
        // $list = StudentLast::whereDate('datetime', '<=', date("2022-11-13"))->whereDate('datetime', '>=', date("2022-11-07"))->get();
        // foreach ($list as $entry) {
        //     $entry->week = 7;
        //     $entry->save();
        // }
        // $list = StudentLast::whereDate('datetime', '<=', date("2022-11-20"))->whereDate('datetime', '>=', date("2022-11-14"))->get();
        // foreach ($list as $entry) {
        //     $entry->week = 8;
        //     $entry->save();
        // }
        // $list = StudentLast::whereDate('datetime', '<=', date("2022-11-27"))->whereDate('datetime', '>=', date("2022-11-21"))->get();
        // foreach ($list as $entry) {
        //     $entry->week = 9;
        //     $entry->save();
        // }
        // $list = StudentLast::whereDate('datetime', '<=', date("2022-12-04"))->whereDate('datetime', '>=', date("2022-11-28"))->get();
        // foreach ($list as $entry) {
        //     $entry->week = 10;
        //     $entry->save();
        // }
        // $list = StudentLast::whereDate('datetime', '<=', date("2022-12-11"))->whereDate('datetime', '>=', date("2022-12-05"))->get();
        // foreach ($list as $entry) {
        //     $entry->week = 11;
        //     $entry->save();
        // }
        // $list = StudentLast::whereDate('datetime', '<=', date("2022-12-18"))->whereDate('datetime', '>=', date("2022-12-12"))->get();
        // foreach ($list as $entry) {
        //     $entry->week = 12;
        //     $entry->save();
        // }

        // 2022-09-26  2022-12-18



        // set better late submit dates
        // 2023-12-01 12:00:00	
        // $assignments = StudentAssignment::all();
        // foreach ($assignments as $assignment) {
        //     if($assignment->date_submitted == "2023-12-01 12:00:00"){
        //         $assignmentData = Assignment::find($assignment->assignment);
        //         $assignmentDueDate = $assignmentData->due_date;
        //         // $today = Carbon::createFromFormat('d/m/Y H:i:s',  '19/02/2019 00:00:00'); 
        //         $dueDate = Carbon::createFromFormat('Y-m-d H:i:s', $assignmentDueDate);
        //         $days_late = rand(1, 10);
        //         $newDate = Carbon::createFromFormat('Y-m-d H:i:s', $assignmentDueDate)->addDays($days_late);
        //         // error_log($days_late);
        //         // error_log($dueDate);
        //         // error_log($newDate);

        //         $assignment->date_submitted = $newDate;
        //         $assignment->save();
        //     }
        // }


        // set some assignments to null
        // $assignments = StudentAssignment::all();
        // foreach ($assignments as $assignment) {
        //     $chance = rand(0,100);
        //     if($chance > 97) {
        //         $assignment->grade = null; 
        //         $assignment->save();
        //     }
        // }

        // reduce attendance for some students
        // $activities = StudentActivity::where('student', 13)->get();
        // foreach ($activities as $activity) {
        //     $chance = rand(0,100);
        //     if($chance > 10) {
        //         $activity->attended = 0; 
        //         $activity->save();
        //     }
        // }

        // adjust date submitted
        // $assignments = StudentAssignment::all();
        // foreach ($assignments as $assignment) {
        //     if($assignment->date_submitted){
        //         $submit = Carbon::create($assignment->date_submitted);
        //         $hours = rand(1,24);
        //         $minutes = rand(1,60);
        //         $time = $submit->subHours($hours);
        //         $time = $time->subMinutes($minutes);
        //         $assignment->date_submitted = $time;
        //         $assignment->save();
        //     }
        // }

    }
}
