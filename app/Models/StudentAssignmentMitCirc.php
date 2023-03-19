<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class StudentAssignmentMitCirc extends Model
{
    use HasFactory;

    /**
     * The table associated with the model.
     *
     * @var string
     */
    protected $table = 'student_assignment_mit_circ';

    /**
     * The attributes that are mass assignable.
     *
     * @var array<int, string>
     */
    protected $fillable = [
        'student_assignment',
        'mit_circ',
    ];

    /**
     * Get the student assignment.
     */
    public function studentAssignment()
    {
        return $this->belongsTo(StudentAssignment::class, 'student_assignment');
    }

    /**
     * Get the mit circ.
     */
    public function mitCirc()
    {
        return $this->belongsTo(MitCirc::class, 'mit_circ');
    }
}
