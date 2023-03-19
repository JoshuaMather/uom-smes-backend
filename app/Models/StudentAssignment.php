<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class StudentAssignment extends Model
{
    use HasFactory;

    /**
     * The table associated with the model.
     *
     * @var string
     */
    protected $table = 'student_assignment';

    /**
     * The attributes that are mass assignable.
     *
     * @var array<int, string>
     */
    protected $fillable = [
        'student',
        'assignment',
        'date_submitted',
        'grade',
    ];

    /**
     * Get the student.
     */
    public function student()
    {
        return $this->belongsTo(Student::class, 'student');
    }

    /**
     * Get the assignment.
     */
    public function assignment()
    {
        return $this->belongsTo(Assignment::class, 'assignment');
    }

    /**
     * Get the assignment mit circ.
     */
    public function studentAssignmentMitCirc()
    {
        return $this->hasMany(StudentAssignmentMitCirc::class, 'student_assignment');
    }

    /**
     * Get the assignment mit circ.
     */
    public function getStudentAssignmentMitCircAttribute()
    {
        return $this->id;
        $mitcirc = null;
        try {
            MitCirc::where('student_assignment', $this->id)->get();
        } catch (\Throwable $th) {
            return $mitcirc;
        }
        if(MitCirc::where('student_assignment', $this->id)->get()){
            $mitcirc = MitCirc::where('student_assignment', $this->id)->get();

        }
        return $mitcirc;
    }
}
