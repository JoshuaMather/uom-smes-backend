<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Student extends Model
{
    use HasFactory;

     /**
     * The table associated with the model.
     *
     * @var string
     */
    protected $table = 'students';

    /**
     * The attributes that are mass assignable.
     *
     * @var array<int, string>
     */
    protected $fillable = [
        'user',
        'personal_tutor',
        'year',
    ];

    protected $appends = ['attendance', 'average_grade'];


    /**
     * Get user the student belongs to.
     */
    public function user()
    {
        return $this->belongsTo(User::class, 'user');
    }

    /**
     * Get student's personal tutor.
     */
    public function personal_tutor()
    {
        return $this->belongsTo(Tutor::class, 'personal_tutor');
    }

    /**
     * Get student activity relation.
     */
    public function studentActivity()
    {
        return $this->hasMany(StudentActivity::class, 'student');
    }

    /**
     * Get student assignment relation.
     */
    public function studentAssignment()
    {
        return $this->hasMany(StudentAssignment::class, 'student');
    }

    /**
     * Get student course relation.
     */
    public function studentCourse()
    {
        return $this->hasMany(StudentCourse::class, 'student');
    }

    /**
     * Get student's last logins/git push.
     */
    public function studentLast()
    {
        return $this->hasMany(StudentLast::class, 'student')->orderBy('datetime', 'DESC');
    }

    /**
     * Get concerns the student belongs to.
     */
    public function concerns()
    {
        return $this->hasMany(Concern::class, 'student');
    }

    /**
     * Get the attendance for the student.
     */
    public function getAttendanceAttribute() 
    {
        $attended = StudentActivity::where([
            ['student', $this->id],
            ['attended', 1]
        ])->count();
        $total = StudentActivity::where('student', $this->id)->count();

        $attendance = 0;
        if($total !== 0) {
            $attendance = $attended / $total;
        }
        $attendance = round($attendance, 2);

        return $attendance;
    }

     /**
     * Get the attendance for the student.
     */
    public function getAverageGradeAttribute() 
    {
        
        $averageGrade = StudentAssignment::where('student', $this->id)->avg('grade');
        if(!$averageGrade){
            $averageGrade = 0;
        }
        $averageGrade = round($averageGrade, 2);

        return $averageGrade;
    }
}
