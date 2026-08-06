<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use PhpOffice\PhpSpreadsheet\IOFactory;
use PhpOffice\PhpSpreadsheet\Writer\Xlsx;
use PhpOffice\PhpSpreadsheet\Cell\Coordinate;
use Carbon\Carbon;
use App\Models\User;
use App\Models\Enrollment;
use App\Models\Subject;
use App\Models\Grade;
use App\Models\Attendance;

class ExcelController extends Controller
{
    public function exportAll(Request $request)
    {
        $gradeLevel = $request->input('grade_level');

        $enrollments = Enrollment::where('status', 'approved')
            ->when($gradeLevel, fn($q) => $q->where('grade_level', $gradeLevel))
            ->get();

        if ($enrollments->isEmpty()) {
            abort(404, 'No approved enrollments found.');
        }

        $templatePath = $this->resolveTemplatePath('template final.xlsx');
        $spreadsheet = IOFactory::load($templatePath);
        $sheet = $spreadsheet->getActiveSheet();

        // Constants
        $colsPerSet = 330;
        $studentsPerSet = 20;
        $pageStartColumns = [
            1 => 'A', 2 => 'AH', 3 => 'BO', 4 => 'CV', 5 => 'EC',
            6 => 'FJ', 7 => 'GQ', 8 => 'HX', 9 => 'JE', 10 => 'KL',
        ];

        $studentCount = 0;

        foreach ($enrollments as $enrollment) {
            $user = User::find($enrollment->user_id);
            if (!$user) continue;

            $subjects = Subject::where('grade_level', $enrollment->grade_level)->get();
            if ($subjects->isEmpty()) continue;

            $gradesCount = Grade::where('user_id', $user->id)
                ->whereIn('subject_id', $subjects->pluck('id'))
                ->count();
            if ($gradesCount === 0) continue;

            $indexInSet = $studentCount % $studentsPerSet;
            $setNumber = intdiv($studentCount, $studentsPerSet);
            $pageInBlock = intdiv($indexInSet, 2) % 10 + 1;
            $positionInPage = $indexInSet % 2;
            $leftFormCol = $pageStartColumns[$pageInBlock];

            $this->fillStudentForm(
                $sheet,
                $user,
                $enrollment,
                $subjects,
                $leftFormCol,
                $positionInPage,
                $colsPerSet,
                $setNumber
            );

            $studentCount++;
        }

        $fileName = 'Report_Cards_All.xlsx';
        $tempPath = storage_path("app/public/{$fileName}");
        $writer = new Xlsx($spreadsheet);
        $writer->setPreCalculateFormulas(false);
        $writer->save($tempPath);

        return response()->download($tempPath)->deleteFileAfterSend(true);
    }

    protected function fillStudentForm($sheet, $user, $enrollment, $subjects, $leftFormCol, $positionInPage, $colsPerSet, $setNumber)
    {
        $baseColNum = Coordinate::columnIndexFromString($leftFormCol) + ($colsPerSet * $setNumber);
        $offset = $positionInPage === 1 ? Coordinate::columnIndexFromString('T') - Coordinate::columnIndexFromString('C') : 0;
        $baseColNum += $offset;

        $col = fn($letter) => Coordinate::stringFromColumnIndex(
            Coordinate::columnIndexFromString($letter) + $baseColNum - Coordinate::columnIndexFromString('A')
        );

        // --- Header ---
        $sheet->setCellValue($col('C') . '8', $user->name);
        $sheet->setCellValue($col('K') . '1', $user->lrn ?? '');
        $sheet->setCellValue($col('O') . '8', $user->sex ?? ($enrollment->sex ?? ''));
        $sheet->setCellValue($col('L') . '8', $enrollment->birthdate ? Carbon::parse($enrollment->birthdate)->age : '');
        $sheet->setCellValue($col('L') . '9', $enrollment->grade_level);
        $sheet->setCellValue($col('C') . '9', $enrollment->curriculum ?? 'Science');
        $sheet->setCellValue($col('C') . '10', $enrollment->school_year);

        // --- Grades ---
        $gradesArray = $enrollment->grades()
            ->whereIn('subject_id', $subjects->pluck('id'))
            ->get()
            ->groupBy('subject_id')
            ->map(fn($grades) => $grades->pluck('grade', 'quarter')->toArray());

        $startRow = 14;
        $totalFinal = 0;
        $subjectCount = 0;

        foreach ($subjects as $i => $subject) {
            $row = $startRow + $i;
            if ($row > 23) break;

            $sheet->setCellValue($col('B') . $row, $subject->name);

            $subjectGrades = $gradesArray[$subject->id] ?? [];
            $q1 = $subjectGrades['1'] ?? null;
            $q2 = $subjectGrades['2'] ?? null;
            $q3 = $subjectGrades['3'] ?? null;
            $q4 = $subjectGrades['4'] ?? null;

            $sheet->setCellValue($col('G') . $row, $q1);
            $sheet->setCellValue($col('H') . $row, $q2);
            $sheet->setCellValue($col('I') . $row, $q3);
            $sheet->setCellValue($col('J') . $row, $q4);

            $filled = array_filter([$q1, $q2, $q3, $q4], fn($g) => is_numeric($g));
            if (count($filled) > 0) {
                $final = round(array_sum($filled) / count($filled), 2);
                $sheet->setCellValue($col('K') . $row, $final);
                $sheet->setCellValue($col('L') . $row, $final >= 75 ? 'PASSED' : 'FAILED');
                $totalFinal += $final;
                $subjectCount++;
            } else {
                $sheet->setCellValue($col('K') . $row, '');
                $sheet->setCellValue($col('L') . $row, '');
            }
        }

        if ($subjectCount > 0) {
            $generalAverage = round($totalFinal / $subjectCount, 2);
            $sheet->setCellValue($col('K') . '24', $generalAverage);
            $sheet->setCellValue($col('L') . '24', $generalAverage >= 75 ? 'PASSED' : 'FAILED');
        }

        // --- Attendance ---
        $attendanceRecords = $enrollment->attendance()
            ->where('school_year', $enrollment->school_year)
            ->get();

        $isRight = $positionInPage === 1;
        $monthCols = $isRight ? ['T','U','V','W','X','Y','Z','AA','AB','AC'] : ['C','D','E','F','G','H','I','J','K','L'];
        $totalCol = $isRight ? 'AF' : 'O';

        $daysOfSchoolRow = 27;
        $daysPresentRow = 28;
        $timesTardyRow = 29;

        $monthSequence = ['Jul','Aug','Sep','Oct','Nov','Dec','Jan','Feb','Mar','Apr'];
        $keyedAttendance = [];

        foreach ($attendanceRecords as $rec) {
            try {
                $monthShort = Carbon::parse($rec->month)->format('M');
            } catch (\Exception $e) {
                $monthShort = substr(trim($rec->month), 0, 3);
            }
            $keyedAttendance[$monthShort] = $rec;
        }

        $totalDays = $totalPresent = $totalTardy = 0;

        foreach ($monthSequence as $i => $monthName) {
            $colLetter = $monthCols[$i];

            // Month header vertical
            $sheet->setCellValue($colLetter . '26', $monthName);
            $sheet->getStyle($colLetter . '26')->getAlignment()->setTextRotation(90);
            $sheet->getStyle($colLetter . '26')->getAlignment()->setHorizontal(\PhpOffice\PhpSpreadsheet\Style\Alignment::HORIZONTAL_CENTER);
            $sheet->getStyle($colLetter . '26')->getAlignment()->setVertical(\PhpOffice\PhpSpreadsheet\Style\Alignment::VERTICAL_CENTER);

            $rec = $keyedAttendance[$monthName] ?? null;

            $sheet->setCellValue($colLetter . $daysOfSchoolRow, $rec && $rec->days_of_school ? $rec->days_of_school : '');
            $sheet->setCellValue($colLetter . $daysPresentRow, $rec && $rec->days_present ? $rec->days_present : '');
            $sheet->setCellValue($colLetter . $timesTardyRow, $rec && $rec->times_tardy ? $rec->times_tardy : '');

            $totalDays += $rec && is_numeric($rec->days_of_school) ? $rec->days_of_school : 0;
            $totalPresent += $rec && is_numeric($rec->days_present) ? $rec->days_present : 0;
            $totalTardy += $rec && is_numeric($rec->times_tardy) ? $rec->times_tardy : 0;
        }

        $sheet->setCellValue($totalCol . $daysOfSchoolRow, $totalDays ?: '');
        $sheet->setCellValue($totalCol . $daysPresentRow, $totalPresent ?: '');
        $sheet->setCellValue($totalCol . $timesTardyRow, $totalTardy ?: '');
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

        abort(500, 'Excel export template not found. Please place it in public/templates or storage/app/templates.');
    }
}
