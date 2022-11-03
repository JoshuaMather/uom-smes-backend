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
        return $this->hasMany(StudentLast::class, 'student');
    }

    /**
     * Get concerns the student belongs to.
     */
    public function concerns()
    {
        return $this->hasMany(Concern::class, 'student');
    }
}
