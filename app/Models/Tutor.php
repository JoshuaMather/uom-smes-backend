<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Tutor extends Model
{
    use HasFactory;

    /**
     * The table associated with the model.
     *
     * @var string
     */
    protected $table = 'tutors';

    /**
     * The attributes that are mass assignable.
     *
     * @var array<int, string>
     */
    protected $fillable = [
        'user',
        'role',
        'year',
    ];

    /**
     * Get user the tutor belongs to.
     */
    public function user()
    {
        return $this->belongsTo(User::class, 'user');
    }

    /**
     * Get concerns the tutor has made.
     */
    public function concerns()
    {
        return $this->hasMany(Concern::class, 'tutor');
    }

    /**
     * Get students the tutor is the personal tutor of.
     */
    public function student()
    {
        return $this->hasMany(Student::class, 'personal_tutor');
    }

    /**
     * Get course the tutor runs.
     */
    public function course()
    {
        return $this->hasOne(Course::class, 'tutor');
    }
}
