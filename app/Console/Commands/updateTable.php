<?php

namespace App\Console\Commands;

use App\Models\Activity;
use App\Models\Assignment;
use App\Models\StudentActivity;
use App\Models\StudentAssignment;
use App\Models\StudentCourse;
use App\Models\User;
use Illuminate\Console\Command;
use Illuminate\Support\Facades\Hash;

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

        // for each student taking a course create entries for assignment and activities 
        // $list = StudentCourse::all();
        // foreach ($list as $entry) {
        //     $course = $entry->course;
        //     $student = $entry->student;

        //     // create student activity data
        //     $activities = Activity::where('course', $course)->get();
        //     foreach ($activities as $activity) {
        //         for ($i=1; $i < 13; $i++) { 
        //             $chance = rand(0,100);
        //             $attend = '';
        //             if($chance > 80) {
        //                 $attend = False;
        //             } else {
        //                 $attend = True;
        //             }
        //             $newEntry = ['student' => $student, 'activity' => $activity->id, 'week' => $i, 'attended' => $attend];
        //             StudentActivity::insert($newEntry);
                    
        //         }
        //     }

        //     //create student assignment data
        //     $assignments = Assignment::where('course', $course)->get();
        //     foreach ($assignments as $assignment) {
        //         $chance = rand(0,100);
        //         $submitDate = '';
        //         if($chance > 80) {
        //             $submitDate = "2023-12-01 12:00:00"; // some future date
        //         } else {
        //             $submitDate = $assignment->due_date;
        //         }

        //         // chance used for more realistic distribution with less studends getting a very low or very high grade
        //         $chance = rand(0,100);
        //         $grade = 0;
        //         if($chance > 80) {
        //             $grade = mt_rand(80, 100) / 100;
        //         } elseif ($chance < 10) {
        //             $grade = mt_rand(0, 30) / 100;
        //         } else {
        //             $grade = mt_rand(40, 80) / 100;
        //         }

        //         $newEntry = ['student' => $student, 'assignment' => $assignment->id, 'date_submitted' => $submitDate, 'grade' => $grade];
        //         StudentAssignment::insert($newEntry);
        //     }
        // }

    }
}
