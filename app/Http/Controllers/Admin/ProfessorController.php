<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use Illuminate\Support\Str;
use Illuminate\Support\Facades\Mail;
use App\Models\User;
use App\Models\Subject;
use App\Models\Advisory;
use App\Models\ProfessorSubject;
use App\Mail\NewProfessorCredentialsMail;
use App\Models\GradeLevel;
use Illuminate\Validation\Rule;
use App\Models\Professor;
use Illuminate\Support\Facades\DB;


class ProfessorController extends Controller
{
    protected function currentSchoolYear(): string
    {
        $year = now()->year;

        return now()->month >= 6
            ? $year . '-' . ($year + 1)
            : ($year - 1) . '-' . $year;
    }

    protected function professorUsersQuery()
    {
        return User::role('professor')
            ->where(function ($query) {
                $query->whereNull('role')
                    ->orWhere('role', 'professor');
            });
    }

    /**
     * Display a listing of professors
     */
    public function index(Request $request)
    {
        $search = $request->input('search');
        $gradeLevelFilter = $request->input('grade_level_id');
        $currentSchoolYear = $this->currentSchoolYear();
        $assignedGrades = Advisory::where('school_year', $currentSchoolYear)->get();


        $query = $this->professorUsersQuery()->with([
            'advisory' => fn ($advisoryQuery) => $advisoryQuery
                ->where('school_year', $currentSchoolYear)
                ->with('gradeLevel'),
        ]);

        if ($search) {
            $query->where(function ($q) use ($search) {
                $q->where('name', 'like', "%{$search}%")
                  ->orWhere('email', 'like', "%{$search}%");
            });
        }

        if ($gradeLevelFilter) {
            $query->whereHas('advisory', function($q) use ($gradeLevelFilter, $currentSchoolYear) {
                $q->where('grade_level_id', $gradeLevelFilter)
                    ->where('school_year', $currentSchoolYear);
            });
        }

        $professors = $query->latest()->paginate(10);
        $gradeLevels = GradeLevel::orderBy('id')->get();

        // Grouped assignments for display
        $assignments = ProfessorSubject::with(['subject', 'gradeLevel'])
            ->where('school_year', $currentSchoolYear)
            ->get()
            ->groupBy('user_id');

        // Group subjects by grade level (for assign modal)
        $subjectsGrouped = Subject::all()
            ->groupBy('grade_level_id')
            ->map(fn($subjects) => $subjects->map(fn($s) => ['id' => $s->id, 'name' => $s->name])->values());

        // Stats for cards
        $stats = [
            'total' => $this->professorUsersQuery()->count(),
            'withAdviser' => Advisory::where('school_year', $currentSchoolYear)->distinct('user_id')->count('user_id'),
            'withoutAdviser' => max(
                0,
                $this->professorUsersQuery()->count() - Advisory::where('school_year', $currentSchoolYear)->distinct('user_id')->count('user_id')
            ),
            'assignments' => ProfessorSubject::where('school_year', $currentSchoolYear)->count(),
        ];

        return view('admin.professors.index', compact(
            'professors',
            'assignments',
            'subjectsGrouped',
            'gradeLevels',
            'stats',
            'assignedGrades',
            'currentSchoolYear',
        ));
    }

    /**
     * Store a new professor
     */
    public function store(Request $request)
    {
        $request->validate([
            'name' => 'required|string|max:255',
            'email' => 'required|email:rfc|unique:users,email',
        ]);

        $temporaryPassword = Str::random(10);

        $user = DB::transaction(function () use ($request, $temporaryPassword) {
            $user = User::create([
                'name' => $request->name,
                'email' => strtolower($request->email),
                'password' => bcrypt($temporaryPassword),
                'role' => 'professor',
            ]);

            $user->assignRole('professor');
            Mail::to($user->email)->send(new NewProfessorCredentialsMail($user, $temporaryPassword));

            return $user;
        });

        return redirect()->route('admin.professors.index')
            ->with('success', 'Professor added and credentials emailed successfully.');
    }

    /**
     * Assign a subject to a professor
     */
    public function assignSubject(Request $request)
    {
        $request->validate([
            'user_id' => 'required|exists:users,id',
            'subject_id' => [
                'required',
                Rule::exists('subjects', 'id')->where(fn ($query) => $query->where('grade_level_id', $request->grade_level_id)),
            ],
            'grade_level_id' => 'required|exists:grade_levels,id',
        ]);

        $this->professorUsersQuery()->findOrFail($request->user_id);
        $currentSchoolYear = $this->currentSchoolYear();

        $exists = ProfessorSubject::where([
            'user_id' => $request->user_id,
            'subject_id' => $request->subject_id,
            'grade_level_id' => $request->grade_level_id,
            'school_year' => $currentSchoolYear,
        ])->exists();

        if ($exists) {
            return redirect()->back()->with('error', 'This assignment already exists.');
        }

        ProfessorSubject::create([
            'user_id' => $request->user_id,
            'subject_id' => $request->subject_id,
            'grade_level_id' => $request->grade_level_id,
            'school_year' => $currentSchoolYear,
        ]);


        return redirect()->back()->with('success', 'Subject assigned successfully.');
    }

    /**
     * Update professor info
     */
    public function update(Request $request, $id)
    {
        $request->validate([
            'name' => 'required|string|max:255',
            'email' => 'required|email:rfc|unique:users,email,' . $id,
        ]);

        $professor = $this->professorUsersQuery()->findOrFail($id);
        $professor->update([
            'name' => $request->name,
            'email' => strtolower($request->email),
        ]);

        return redirect()->route('admin.professors.index')
            ->with('success', 'Professor updated successfully.');
    }

    /**
     * Delete a professor and their assignments
     */
    public function destroy($id)
    {
        $professor = $this->professorUsersQuery()->findOrFail($id);

        // Remove all assignments & advisory
        ProfessorSubject::where('user_id', $professor->id)->delete();
        Advisory::where('user_id', $professor->id)->delete();

        $professor->delete();

        return redirect()->route('admin.professors.index')
            ->with('success', 'Professor deleted successfully.');
    }

    /**
     * Assign or update adviser
     */

public function assignAdviser(Request $request, $professorId)
{
    $professor = $this->professorUsersQuery()->findOrFail($professorId); // Use User model
    $existingAdvisoryId = Advisory::where('user_id', $professor->id)
        ->where('school_year', $request->school_year)
        ->value('id');

    // Validate input
    $request->validate([
        'grade_level_id' => [
            'required',
            'exists:grade_levels,id',
            Rule::unique('advisories', 'grade_level_id')
                ->where(fn ($query) => $query->where('school_year', $request->school_year))
                ->ignore($existingAdvisoryId),
        ],
        'school_year' => ['required', 'regex:/^\d{4}-\d{4}$/'],
    ], [
        'grade_level_id.unique' => 'Another professor is already assigned as adviser for this grade level in the selected school year.',
        'school_year.regex' => 'School year must use the format YYYY-YYYY.',
    ]);

    // Update or create advisory
    Advisory::updateOrCreate(
        [
            'user_id' => $professor->id,
            'school_year' => $request->school_year,
        ],
        [
            'grade_level_id' => $request->grade_level_id,
        ]
    );

    return redirect()->back()->with('success', 'Adviser assigned successfully.');
}





    /**
     * Remove adviser
     */
    public function removeAdviser($id)
    {
        Advisory::where('user_id', $id)
            ->where('school_year', $this->currentSchoolYear())
            ->delete();
        return back()->with('success', 'Adviser removed successfully.');
    }

    /**
     * Remove a subject assignment
     */
    public function removeAssignment($id)
    {
        ProfessorSubject::findOrFail($id)->delete();
        return redirect()->back()->with('success', 'Assignment removed successfully.');
    }
}
