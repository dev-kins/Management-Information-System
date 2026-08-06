<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Support\Facades\Schema;

class ProfessorSubject extends Model
{
    use HasFactory;

    protected $fillable = [
        'user_id',
        'subject_id',
        'grade_level_id',
        'school_year',
    ];

    public function getTable()
    {
        if (Schema::hasTable('professor_subjects')) {
            return 'professor_subjects';
        }

        return 'professor_subject';
    }

    // Relation to Subject
    public function subject()
    {
        return $this->belongsTo(Subject::class);
    }
    // Relation to Professor (User)
    public function professor()
    {
        return $this->belongsTo(User::class, 'user_id');    
    }
public function gradeLevel()
{
    return $this->belongsTo(GradeLevel::class, 'grade_level_id');
}

}
