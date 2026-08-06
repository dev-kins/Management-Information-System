<?php

namespace App\Http\Controllers\Admin;

use Illuminate\Http\Request;
use PhpOffice\PhpSpreadsheet\IOFactory;
use PhpOffice\PhpSpreadsheet\Writer\Xlsx;
use Carbon\Carbon;
use App\Models\User;
use App\Models\Enrollment;
use App\Models\Subject;
use App\Models\Grade;
use App\Models\Advisory;
use App\Http\Controllers\Controller;

class Form137Controller extends Controller
{
    private const LEVEL_MAP = [
        1 => '7',
        2 => '8',
        3 => '9',
        4 => '10',
    ];

    public function export($userId)
    {
        $user = User::findOrFail($userId);

        // Map grade_level_id → actual grade
        // Load enrollments
        $enrollments = Enrollment::where('user_id', $user->id)
            ->whereIn('grade_level_id', array_keys(self::LEVEL_MAP))
            ->orderBy('grade_level_id', 'asc')
            ->with(['attendances'])
            ->get()
            ->mapWithKeys(fn($enr) => [$enr->grade_level_id => $enr]);

        if ($enrollments->isEmpty()) {
            return back()->with('error', 'No enrollment records were found for this student.');
        }

        // Load Form 137 template
        $templatePath = $this->resolveTemplatePath('LSHS_Permanent Record Form-137.xlsx');
        $spreadsheet = IOFactory::load($templatePath);
        $sheet = $spreadsheet->getActiveSheet();

        //-----------------------------------
        // 🧩 LEARNER'S INFORMATION
        //-----------------------------------
        $firstEnrollment = $enrollments->first();
        $middle = $firstEnrollment->middle_name ? ' ' . $firstEnrollment->middle_name : '';
        $fullName = trim("{$firstEnrollment->last_name}, {$firstEnrollment->first_name}{$middle}");
        $name = trim("{$firstEnrollment->first_name}{$middle} {$firstEnrollment->last_name}");

        $sheet->setCellValue('B10', $fullName);
        $sheet->setCellValueExplicit('R10', $firstEnrollment->lrn ?? '', \PhpOffice\PhpSpreadsheet\Cell\DataType::TYPE_STRING);
        $sheet->setCellValue('AA10', $firstEnrollment->birthdate ? Carbon::parse($firstEnrollment->birthdate)->format('F d, Y') : '');
        $sheet->setCellValue('S11', $firstEnrollment->birthplace ?? '');

        //-----------------------------------
        // 📌 BLOCK CONFIG MAP
        //-----------------------------------
        $gradeBlocks = [
            '7' => [
                'school_year' => 'A25', 'grade_level' => 'C22', 'adviser' => 'B23',
                'q1' => 'J', 'q2' => 'K', 'q3' => 'L', 'q4' => 'M', 'final' => 'N', 'remarks' => 'O',
                'subjects_start' => 27, 'subjects_end' => 35,
                'gen_avg' => 'N37', 'gen_avg_remarks' => 'O37',
                'att_start_col' => 'C', 'att_end_col' => 'M',
                'att_days_row' => 39, 'att_present_row' => 40, 'att_tardy_row' => 41,
                'att_total_days' => 'N39', 'att_total_present' => 'N40', 'att_total_tardy' => 'N41',
            ],
            '8' => [
                'school_year' => 'Q25', 'grade_level' => 'S22', 'adviser' => 'R23',
                'q1' => 'Z', 'q2' => 'AA', 'q3' => 'AB', 'q4' => 'AC', 'final' => 'AD', 'remarks' => 'AE',
                'subjects_start' => 27, 'subjects_end' => 36,
                'gen_avg' => 'AD37', 'gen_avg_remarks' => 'AE37',
                'att_start_col' => 'S', 'att_end_col' => 'AC',
                'att_days_row' => 39, 'att_present_row' => 40, 'att_tardy_row' => 41,
                'att_total_days' => 'AD39', 'att_total_present' => 'AD40', 'att_total_tardy' => 'AD41',
            ],
            '9' => [
                'school_year' => 'A46', 'grade_level' => 'C43', 'adviser' => 'B44',
                'q1' => 'J', 'q2' => 'K', 'q3' => 'L', 'q4' => 'M', 'final' => 'N', 'remarks' => 'O',
                'subjects_start' => 48, 'subjects_end' => 57,
                'gen_avg' => 'N58', 'gen_avg_remarks' => 'O58',
                'att_start_col' => 'C', 'att_end_col' => 'M',
                'att_days_row' => 60, 'att_present_row' => 61, 'att_tardy_row' => 62,
                'att_total_days' => 'N60', 'att_total_present' => 'N61', 'att_total_tardy' => 'N62',
            ],
            '10' => [
                'school_year' => 'Q46', 'grade_level' => 'S43', 'adviser' => 'R44',
                'q1' => 'Z', 'q2' => 'AA', 'q3' => 'AB', 'q4' => 'AC', 'final' => 'AD', 'remarks' => 'AE',
                'subjects_start' => 48, 'subjects_end' => 57,
                'gen_avg' => 'AD58', 'gen_avg_remarks' => 'AE58',
                'att_start_col' => 'S', 'att_end_col' => 'AC',
                'att_days_row' => 60, 'att_present_row' => 61, 'att_tardy_row' => 62,
                'att_total_days' => 'AD60', 'att_total_present' => 'AD61', 'att_total_tardy' => 'AD62',
            ],
        ];

        //-----------------------------------
        // 🧮 Fill Grades & Attendance
        //-----------------------------------
// Inside the loop for each enrollment:
foreach ($enrollments as $gradeLevelId => $enrollment) {
    $grade = self::LEVEL_MAP[$gradeLevelId] ?? null;
    $map = $gradeBlocks[$grade] ?? null;
    if (!$map) continue;

    // Adviser (use grade_level_id)
    $advisory = Advisory::where('grade_level_id', $gradeLevelId)
        ->where('school_year', $enrollment->school_year)
        ->with('professor')
        ->first();
    $adviserName = $advisory ? ($advisory->professor->name ?? '') : '';
    $sheet->setCellValue($map['grade_level'], 'Grade ' . $grade);
    $sheet->setCellValue($map['adviser'], $adviserName);
    $sheet->getStyle($map['adviser'])->getAlignment()->setHorizontal('center');
    $sheet->setCellValue($map['school_year'], $enrollment->school_year ?? '');

    // Subjects & Grades
    $subjects = Subject::where('grade_level_id', $gradeLevelId)->get();
    $grades = Grade::where('user_id', $user->id)
        ->whereIn('subject_id', $subjects->pluck('id'))
        ->where('school_year', $enrollment->school_year)
        ->get()
        ->groupBy('subject_id');

    $this->fillGradeBlock($sheet, $map, $subjects, $grades);
    $this->fillAttendanceBlock($sheet, $map, $enrollment);
}


        //-----------------------------------
// 🧾 Summary of Credits
//-----------------------------------
$summaryBlocks = [
    '7'  => ['year' => 'C',  'credit' => 'F'],
    '8'  => ['year' => 'N',  'credit' => 'O'],
    '9'  => ['year' => 'S',  'credit' => 'V'],
    '10' => ['year' => 'AD', 'credit' => 'AE'],
];

$rowStart = 67;
$rowEnd   = 76;

foreach ($enrollments as $gradeLevelId => $enrollment) {
    $grade = self::LEVEL_MAP[$gradeLevelId] ?? null;
    if (!$grade || !isset($summaryBlocks[$grade])) continue;

    $block = $summaryBlocks[$grade];

    // Get subjects for this grade_level_id
    $subjects = Subject::where('grade_level_id', $gradeLevelId)->get();
    $row = $rowStart;

    foreach ($subjects as $subject) {
        if ($row > $rowEnd) break;
        $sheet->setCellValue($block['year'] . $row, $enrollment->school_year ?? '');
        $sheet->setCellValue($block['credit'] . $row, $subject->credits ?? 1);
        $sheet->getStyle($block['credit'] . $row)->getAlignment()->setHorizontal('center');
        $row++;
    }
}


        //-----------------------------------
        // 🪪 Certification
        //-----------------------------------
        $sheet->setCellValue('S80', $name);
        $sheet->getStyle('S80')->getFont()->getColor()->setRGB('000000');
        $sheet->setCellValue('C81', 'WHATEVER LEGAL PURPOSE IT MAY SERVE');
        $sheet->setCellValue('B83', Carbon::now()->format('F d, Y'));
        $admin = User::where('role', 'admin')
             ->where('status', 'active')
             ->firstOrFail(); // throws 404 if no active admin
        $adminName = $admin->name ?? trim(($admin->first_name ?? '') . ' ' . ($admin->last_name ?? ''));
        $sheet->setCellValue('S83', $adminName);


        //-----------------------------------
        // 💾 Export
        //-----------------------------------
        $fileName = "{$fullName}_Form137.xlsx";
        $tempPath = storage_path("app/public/{$fileName}");
        $writer = new Xlsx($spreadsheet);
        $writer->setPreCalculateFormulas(true);
        $writer->save($tempPath);

        return response()->download($tempPath)->deleteFileAfterSend(true);
    }

    //-----------------------------------
    // 🧩 Helpers: Fill Grades & Attendance
    //-----------------------------------
    protected function fillGradeBlock($sheet, $map, $subjects, $grades)
    {
        $row = $map['subjects_start'];
        $totalFinal = 0;
        $subjectCount = 0;

        foreach ($subjects as $subject) {
            if ($row > $map['subjects_end']) break;
            $gradeSet = $grades[$subject->id] ?? collect();

            $qGrades = [];
            for ($i = 1; $i <= 4; $i++) {
                $g = $gradeSet->firstWhere('quarter', $i)->grade ?? 0;
                $g = is_numeric($g) ? (float)$g : 0;
                $sheet->setCellValue($map['q' . $i] . $row, $g);
                $sheet->getStyle($map['q' . $i] . $row)->getFont()->getColor()->setRGB('000000');
                $qGrades[] = $g;
            }

            $filled = array_filter($qGrades, fn($v) => $v > 0);
            if (count($filled) > 0) {
                $final = (int) ceil(array_sum($filled) / count($filled));
                $sheet->setCellValue($map['final'] . $row, $final);
                $sheet->getStyle($map['final'] . $row)->getFont()->getColor()->setRGB('000000');
                $sheet->getStyle($map['final'] . $row)->getAlignment()->setHorizontal('center');

                $sheet->setCellValue($map['remarks'] . $row, $final >= 75 ? 'PASSED' : 'FAILED');
                $sheet->getStyle($map['remarks'] . $row)->getFont()->getColor()->setRGB('000000');

                $totalFinal += $final;
                $subjectCount++;
            }

            $row++;
        }

        if ($subjectCount > 0) {
            $genAvg = (int) ceil($totalFinal / $subjectCount);
            $sheet->setCellValue($map['gen_avg'], $genAvg);
            $sheet->getStyle($map['gen_avg'])->getFont()->getColor()->setRGB('000000');
            $sheet->getStyle($map['gen_avg'])->getAlignment()->setHorizontal('center');

            $sheet->setCellValue($map['gen_avg_remarks'], $genAvg >= 75 ? 'PASSED' : 'FAILED');
            $sheet->getStyle($map['gen_avg_remarks'])->getFont()->getColor()->setRGB('000000');
        }
    }

    protected function fillAttendanceBlock($sheet, $map, $enrollment)
{
    // Fixed 12 months from August to June
    $monthMap = [
        'Aug' => 'August', 'Sep' => 'September', 'Oct' => 'October', 'Nov' => 'November',
        'Dec' => 'December', 'Jan' => 'January', 'Feb' => 'February', 'Mar' => 'March',
        'Apr' => 'April', 'May' => 'May', 'Jun' => 'June'
    ];
    $monthAbbrs = array_keys($monthMap);

    $cols = $this->columnRange($map['att_start_col'], $map['att_end_col']);
    $usableCols = array_slice($cols, 0, count($monthAbbrs)); // Make sure only 12 columns are used

    $attendanceCollection = $enrollment->attendances
        ->where('school_year', $enrollment->school_year)
        ->values();

    $totalSchool = $totalPresent = $totalTardy = 0;

    foreach ($monthAbbrs as $idx => $abbr) {
        $col = $usableCols[$idx] ?? null;
        if (!$col) continue;

        $att = $attendanceCollection->firstWhere('month', $monthMap[$abbr]);
        $daysSchool = $att->days_of_school ?? 0;
        $daysPresent = $att->days_present ?? 0;
        $timesTardy = $att->times_tardy ?? 0;

        $sheet->setCellValue($col . $map['att_days_row'], $daysSchool);
        $sheet->setCellValue($col . $map['att_present_row'], $daysPresent);
        $sheet->setCellValue($col . $map['att_tardy_row'], $timesTardy);

        $totalSchool += $daysSchool;
        $totalPresent += $daysPresent;
        $totalTardy += $timesTardy;
    }

    if (!empty($map['att_total_days'])) $sheet->setCellValue($map['att_total_days'], $totalSchool);
    if (!empty($map['att_total_present'])) $sheet->setCellValue($map['att_total_present'], $totalPresent);
    if (!empty($map['att_total_tardy'])) $sheet->setCellValue($map['att_total_tardy'], $totalTardy);
}


    protected function columnRange($start, $end)
    {
        $columns = [];
        $current = $start;
        while (true) {
            $columns[] = $current;
            if ($current === $end) break;
            $current = $this->nextColumn($current);
            if (count($columns) > 100) break;
        }
        return $columns;
    }

    protected function nextColumn($col)
    {
        $len = strlen($col);
        $col = strtoupper($col);
        $i = $len - 1;
        $carry = 1;
        $result = '';
        while ($i >= 0) {
            $c = ord($col[$i]) - 65;
            $sum = $c + $carry;
            $carry = intdiv($sum, 26);
            $digit = $sum % 26;
            $result = chr(65 + $digit) . $result;
            $i--;
        }
        if ($carry > 0) {
            $result = chr(65 + $carry - 1) . $result;
        }
        return $result;
    }

    protected function resolveTemplatePath(string $fileName): string
    {
        $candidates = [
            storage_path('app/templates/' . $fileName),
            public_path('templates/' . $fileName),
        ];

        foreach ($candidates as $path) {
            if (is_file($path)) {
                return $path;
            }
        }

        abort(500, 'Form 137 template not found. Please place it in public/templates or storage/app/templates.');
    }
}
