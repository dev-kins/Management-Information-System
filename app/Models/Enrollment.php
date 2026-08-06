<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use App\Models\Enrollment;

class Enrollment extends Model
{
    protected $fillable = [
        'lrn','email', 'school_year', 'grade_level_id',
        'last_name', 'first_name', 'middle_name', 'extension_name',
        'birthdate', 'birthplace', 'sex', 'mother_tongue',
        'ip_community', 'ip_specify', 'is_4ps', 'household_id',
        'current_house', 'current_street', 'current_barangay', 'current_city', 
        'current_province', 'current_country', 'current_zip',
        'permanent_house', 'permanent_street', 'permanent_barangay', 
        'permanent_city', 'permanent_province', 'permanent_country', 
        'permanent_zip',
        'father_last', 'father_first', 'father_middle', 'father_contact',
        'mother_last', 'mother_first', 'mother_middle', 'mother_contact',
        'guardian_last', 'guardian_first', 'guardian_middle', 'guardian_contact',
        'modality', 'status', 'documents', 'user_id',
        // Optional fields (if you store them)
        'promotion_status', 'gpa', 'weighted_avg_msr', 'has_failing_grade'
    ];

    protected $casts = [
        'modality' => 'array',
        'birthdate' => 'date',
        'documents' => 'array',
    ];

    // ✅ Relationships
    // public function user()
    // {
    //     return $this->belongsTo(User::class, 'user_id');
    // }

    public function subjects()
    {
        return $this->belongsToMany(Subject::class);
    }

    public function grades()
    {
        return $this->hasMany(Grade::class, 'user_id', 'user_id');
    }

    public function student()
    {
        return $this->belongsTo(User::class, 'user_id');
    }

    // ----------------------------------------------------
    // ✅ Dynamic Computed Attributes
    // ----------------------------------------------------

    // 🎓 Compute GPA across all subjects
    public function getGpaAttribute()
    {
        $grades = $this->grades;

        if ($grades->isEmpty()) return 0;

        return ceil($grades->avg('grade'));
    }

    // 🔬 Compute weighted average for Math, Science, Research
    public function getWeightedAvgMsrAttribute()
    {
        $grades = $this->grades->filter(function ($g) {
            $subject = strtolower($g->subject->name ?? '');
            return in_array($subject, ['math', 'science', 'research']);
        });

        if ($grades->isEmpty()) return 0;

        return ceil($grades->avg('grade'));
    }

    // ⚠️ Detect any failing grade (below 75)
    public function getHasFailingGradeAttribute()
    {
        return $this->grades->contains(function ($g) {
            return $g->grade < 75;
        });
    }
//     public function grades()
// {
//     return $this->hasMany(Grade::class, 'user_id', 'user_id')
//                 ->where('school_year', $this->school_year);
// }

public function attendances()
{
    return $this->hasMany(Attendance::class, 'enrollment_id');
}

public function attendance()
{
    return $this->attendances();
}

public function user() {
    return $this->belongsTo(User::class);
}

public function gradeLevel() {
    return $this->belongsTo(GradeLevel::class, 'grade_level_id');
}

public function grade_level()
{
    return $this->belongsTo(GradeLevel::class, 'grade_level_id');
}




}
