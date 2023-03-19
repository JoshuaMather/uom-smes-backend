<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class MitCirc extends Model
{
    use HasFactory;

    /**
     * The table associated with the model.
     *
     * @var string
     */
    protected $table = 'mit_circ';

    /**
     * The attributes that are mass assignable.
     *
     * @var array<int, string>
     */
    protected $fillable = [
        'type',
        'description',
    ];

    /**
     * Get the assignment mit circ.
     */
    public function studentAssignmentMitCirc()
    {
        return $this->hasMany(StudentAssignmentMitCirc::class, 'mit_circ');
    }
}
