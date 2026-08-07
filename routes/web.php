<?php

use Illuminate\Support\Facades\Route;
use Illuminate\Support\Facades\Mail;
use App\Models\User;
use App\Mail\StudentCredentialsMail;

use App\Http\Controllers\ProfileController;
use App\Http\Controllers\Auth\CustomLoginController;
use App\Http\Controllers\Auth\StudentRegisterController;
use App\Http\Controllers\Auth\AdminRegisterController;
use App\Http\Controllers\EnrollmentController;

use App\Http\Controllers\Student\DashboardController as StudentDashboardController;
use App\Http\Controllers\Professor\DashboardController as ProfessorDashboardController;
use App\Http\Controllers\Admin\DashboardController;
use App\Http\Controllers\Admin\AdminEnrollmentController;
use App\Http\Controllers\Admin\StudentController;
use App\Http\Controllers\Admin\ProfessorController;
use App\Http\Controllers\Admin\AnnouncementController;
use App\Http\Controllers\Professor\AnnouncementController as ProfessorAnnouncementController;
use App\Http\Controllers\Auth\AuthenticatedSessionController;
use App\Http\Controllers\Professor\GradeController;
use App\Http\Controllers\Student\StudentAnnouncementController;
use App\Http\Controllers\Student\StudentSettingsController;
use App\Http\Controllers\Professor\ChangePasswordController as ProfessorChangePasswordController;
use App\Http\Controllers\Admin\ChangePasswordController as AdminChangePasswordController;
use App\Http\Controllers\GradeConsolidationController;
use App\Http\Controllers\Admin\GradeSummaryController;
use App\Http\Controllers\ReportCardController;
use App\Http\Controllers\Professor\ClassesController;
use App\Http\Controllers\GradeConsolidatedController;
use App\Http\Controllers\Admin\ECRController;
use App\Http\Controllers\ExcelController;
use App\Http\Controllers\Professor\AttendanceController;
use App\Http\Controllers\Admin\FormController;
use App\Http\Controllers\Auth\ForgotPasswordController;
use App\Http\Controllers\Admin\AdminForgotPasswordController;
use App\Http\Controllers\Professor\ProfessorForgotPasswordController;
use App\Http\Controllers\Student\StudentForgotPasswordController;
use App\Http\Controllers\AdminGradeController;
use App\Http\Controllers\Professor\StudentController as ProfessorStudentController;
use App\Http\Controllers\SuperAdmin\DashboardController as SuperAdminDashboard;
use App\Http\Controllers\Admin\PromotionController;
use App\Http\Controllers\Admin\GradeApprovalController;
use App\Http\Controllers\Auth\StudentLogoutController;
use App\Http\Controllers\Auth\AdminLogoutController;
use App\Http\Controllers\Auth\ProfessorLogoutController;
use App\Http\Controllers\Admin\AlumniController;
use App\Http\Controllers\COEController;
use App\Http\Controllers\Admin\GradeLevelController;
use App\Http\Controllers\Admin\SubjectController;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Request;
use App\Http\Controllers\Admin\SettingsController;
use App\Http\Controllers\Auth\TwoFactorController;
use App\Http\Controllers\SuperAdmin\AdminManagementController;
use App\Http\Controllers\Auth\SuperAdminLoginController;
use App\Http\Controllers\SuperAdmin\BackupController;
use App\Http\Controllers\SuperAdmin\AuditController;


/*
|--------------------------------------------------------------------------
| Public Routes
|--------------------------------------------------------------------------
*/

// Visitor Tracking & Homepage
Route::get('/', function () {
    $ip = Request::ip();
    $today = date('Y-m-d');

    // // Record today’s visit only once per IP
    // $exists = DB::table('visitors')->where('ip_address', $ip)->where('visited_at', $today)->exists();
    // if (!$exists) {
    //     DB::table('visitors')->insert([
    //         'ip_address' => $ip,
    //         'visited_at' => $today,
    //         'created_at' => now(),
    //         'updated_at' => now(),
    //     ]);
    // }

    // $totalVisitors = DB::table('visitors')->count();
    // $todayVisitors = DB::table('visitors')->where('visited_at', $today)->count();

    return view('welcome');
});

Route::post('/refresh-session', function () {
    request()->session()->touch(); // Update last activity timestamp
    return response()->json(['status' => 'ok']);
})->name('admin.refresh-session');


// Enrollment
Route::get('/enroll', [EnrollmentController::class, 'showForm'])->name('enroll.form');
Route::post('/enroll', [EnrollmentController::class, 'submit'])->name('enroll.submit');

// Student login
Route::get('/login/student', [CustomLoginController::class, 'showStudentLogin'])->name('login.student');
Route::post('/login/student', [CustomLoginController::class, 'login'])
    ->name('login.student.submit')
    ->middleware('throttle:5,1'); // 5 attempts per minute

// Professor login
Route::get('/login/professor', [CustomLoginController::class, 'showProfessorLogin'])->name('login.professor');
Route::post('/login/professor', [CustomLoginController::class, 'loginProfessor'])
    ->name('login.professor.submit')
    ->middleware('throttle:5,1');

// Admin login
Route::get('/login/admin', [CustomLoginController::class, 'showAdminLogin'])->name('login.admin');
Route::post('/login/admin', [CustomLoginController::class, 'loginAdmin'])
    ->name('login.admin.submit')
    ->middleware('throttle:5,1');

// Super Admin login
Route::get('/login/superadmin', [SuperAdminLoginController::class, 'show'])->name('login.superadmin');
Route::post('/login/superadmin', [SuperAdminLoginController::class, 'login'])->name('login.superadmin.submit');


// 2FA Verification
Route::get('/2fa', [TwoFactorController::class, 'index'])->name('2fa.index');
Route::post('/2fa', [TwoFactorController::class, 'store'])
->name('2fa.store')
->middleware('throttle:5,1');
Route::post('/2fa/resend', [TwoFactorController::class, 'resend'])->name('2fa.resend');


// Admin registration
Route::get('/register/admin', [AdminRegisterController::class, 'show'])->name('register.admin');
Route::post('/register/admin', [AdminRegisterController::class, 'register'])->name('register.admin.submit');

Route::middleware('guest')->group(function () {
    // Student forgot password
    Route::get('/student/forgot-password', [StudentForgotPasswordController::class, 'showLinkRequestForm'])->name('student.password.request');
    Route::post('/student/forgot-password', [StudentForgotPasswordController::class, 'sendResetLinkEmail'])->name('student.password.email');
    Route::get('/student/reset-password/{token}', [StudentForgotPasswordController::class, 'showResetForm'])->name('student.password.reset');
    Route::post('/student/reset-password', [StudentForgotPasswordController::class, 'reset'])->name('student.password.update');

    // Professor forgot password
    Route::get('/professor/forgot-password', [ProfessorForgotPasswordController::class, 'showLinkRequestForm'])->name('professor.password.request');
    Route::post('/professor/forgot-password', [ProfessorForgotPasswordController::class, 'sendResetLinkEmail'])->name('professor.password.email');
    Route::get('/professor/password/reset/{token}', [ProfessorForgotPasswordController::class, 'showResetForm'])->name('professor.password.reset');
    Route::post('/professor/password/reset', [ProfessorForgotPasswordController::class, 'reset'])->name('professor.password.update');

    // Admin forgot password
    Route::get('/admin/forgot-password', [AdminForgotPasswordController::class, 'showLinkRequestForm'])->name('admin.password.request');
    Route::post('/admin/forgot-password', [AdminForgotPasswordController::class, 'sendResetLinkEmail'])->name('admin.password.email');
    Route::get('/admin/password/reset/{token}', [AdminForgotPasswordController::class, 'showResetForm'])->name('admin.password.reset');
    Route::post('/admin/password/reset', [AdminForgotPasswordController::class, 'reset'])->name('admin.password.update');
});

/*
|--------------------------------------------------------------------------
| Authenticated Routes 
|--------------------------------------------------------------------------
*/
Route::middleware(['auth'])->group(function () {

    /*
    |--------------------------------------------------------------------------
    | Profile Routes
    |--------------------------------------------------------------------------
    */
    Route::get('/profile', [ProfileController::class, 'edit'])->name('profile.edit');
    Route::patch('/profile', [ProfileController::class, 'update'])->name('profile.update');
    Route::delete('/profile', [ProfileController::class, 'destroy'])->name('profile.destroy');

    /*
    |--------------------------------------------------------------------------
    | Student Routes
    |--------------------------------------------------------------------------
    */
    Route::prefix('student')->group(function () {

        Route::get('/dashboard', [StudentDashboardController::class, 'index'])->name('student.dashboard');

        // Grades
        Route::get('/grades', [\App\Http\Controllers\Student\GradesController::class, 'index'])->name('student.grades');
        Route::get('/academic-standing', [\App\Http\Controllers\Student\StandingController::class, 'index'])->name('student.academic-standing');

        // Announcements
        Route::get('announcements', [StudentAnnouncementController::class, 'index'])->name('announcements.index');
        Route::get('announcements/{announcement}', [StudentAnnouncementController::class, 'show'])->name('announcements.show');
        Route::post('announcements/{announcement}/read', [StudentAnnouncementController::class, 'markAsRead'])->name('student.announcements.read');

        // Settings
        Route::get('/settings', [StudentSettingsController::class, 'index'])->name('student.settings');
        Route::post('/settings/change-password', [StudentSettingsController::class, 'changePassword'])->name('settings.change-password');

        // Report card requests
        Route::post('/report-card-request', [ReportCardController::class, 'request'])->name('reportcard.request');
        Route::get('/report-forms', function () {return view('student.report_forms');})->name('student.reportforms');
        Route::post('/request-form', [ReportCardController::class, 'requestForm'])->name('student.form.request');
        Route::post('/coe/request', [COEController::class, 'request'])->name('coe.request');


        // Logout
        Route::post('/logout', [StudentLogoutController::class, 'logout'])->name('student.logout');

    });

    /*
    |--------------------------------------------------------------------------
    | Professor Routes
    |--------------------------------------------------------------------------
    */
    Route::prefix('professor')->group(function () {

        Route::get('/dashboard', [ProfessorDashboardController::class, 'index'])->name('professor.dashboard');

        // Grades
        // Route::get('/classes', [ProfessorDashboardController::class, 'classes'])->name('professor.classes');
        Route::get('/grades', [GradeController::class, 'index'])->name('professor.grades');
        Route::get('/grades/create', [GradeController::class, 'create'])->name('professor.grades.create');
        Route::get('/reports', [ProfessorDashboardController::class, 'reports'])->name('professor.reports');

        // Announcements
        Route::get('/announcements', [ProfessorAnnouncementController::class, 'index'])->name('professor.announcements');
        Route::get('/announcements/create', [ProfessorAnnouncementController::class, 'create'])->name('professor.announcements.create');
        Route::post('/announcements', [ProfessorAnnouncementController::class, 'store'])->name('professor.announcements.store');
        Route::get('/announcements/{announcement}/edit', [ProfessorAnnouncementController::class, 'edit'])->name('professor.announcements.edit');
        Route::put('/announcements/{announcement}', [ProfessorAnnouncementController::class, 'update'])->name('professor.announcements.update');
        Route::delete('/announcements/{announcement}', [ProfessorAnnouncementController::class, 'destroy'])->name('professor.announcements.destroy');

        // Change Password
        // Classes & Advisory
        // Route::get('/my-classes', [ClassesController::class, 'index'])->name('professor.classes');
        Route::get('/teacher/advisory', [ProfessorDashboardController::class, 'advisory'])->name('teacher.advisory');
        Route::get('/teacher/advisory/student/{id}', [ProfessorDashboardController::class, 'advisoryView'])->name('teacher.advisory.view');

        // Attendance
        Route::get('/attendance', [AttendanceController::class, 'index'])->name('professor.attendance.index');
        Route::post('/attendance/store', [AttendanceController::class, 'store'])->name('professor.attendance.store');
        Route::get('/attendance/fetch', [AttendanceController::class, 'fetch'])->name('professor.attendance.fetch');
        
        //Grade Consolidation
        Route::get('/grades/consolidation', [GradeConsolidationController::class, 'index'])->name('grades.consolidation');
        Route::post('/grades/store', [GradeConsolidationController::class, 'store'])->name('grades.store');

        //Grade Consolidated
        Route::get('/grades/consolidated', [GradeConsolidatedController::class, 'index'])->name('grades.consolidated');

        //Honor
        Route::get('/teacher/honor/filter', [ProfessorDashboardController::class, 'filterHonor'])->name('teacher.honor.filter');

        // Logout
        Route::post('/logout', [ProfessorLogoutController::class, 'logout'])->name('professor.logout');

        //Change Password
        Route::get('/change-password', [ProfessorChangePasswordController::class, 'showChangeForm'])->name('professor.change-password.form');
        Route::post('/change-password', [ProfessorChangePasswordController::class, 'updatePassword'])->name('professor.change-password.update');

    });

    /*
    |--------------------------------------------------------------------------
    | Admin Routes
    |--------------------------------------------------------------------------
    */
    
    Route::prefix('admin')->group(function () {

        Route::get('/dashboard', [DashboardController::class, 'index'])->name('admin.dashboard');

        // Report Card Requests
        Route::get('excel/export-all/{grade_level?}', [ECRController::class, 'exportAll'])->name('excel.exportAll');
        Route::get('excel/export-single/{user}', [ECRController::class, 'exportSingle'])->name('excel.exportSingle');

        // Enrollment management
        Route::get('/enrollments/pending', [AdminEnrollmentController::class, 'pending'])->name('admin.enrollments.pending');
        Route::get('/enrollments', [AdminEnrollmentController::class, 'index'])->name('admin.enrollments.index');
        Route::post('/enrollments/{enrollment}/approve', [AdminEnrollmentController::class, 'approve'])->name('admin.enrollments.approve');
        Route::post('/enrollments/{enrollment}/reject', [AdminEnrollmentController::class, 'reject'])->name('admin.enrollments.reject');
        Route::get('/enrollments/archive', [EnrollmentController::class, 'archive'])->name('enrollments.archive');
        Route::get('/enrollments/{enrollment}/export', [EnrollmentController::class, 'export'])->name('admin.enrollments.export');

        // Professors management
        Route::get('/professors', [ProfessorController::class, 'index'])->name('admin.professors.index');
        Route::get('/professors/create', [ProfessorController::class, 'create'])->name('admin.professors.create');
        Route::post('/professors', [ProfessorController::class, 'store'])->name('admin.professors.store');
        Route::get('/professors/{professor}/edit', [ProfessorController::class, 'edit'])->name('admin.professors.edit');
        Route::put('/professors/update/{id}', [ProfessorController::class, 'update'])->name('admin.professors.update');
        Route::delete('/professors/{professor}', [ProfessorController::class, 'destroy'])->name('admin.professors.destroy');
        Route::post('/professors/assign', [ProfessorController::class, 'assignSubject'])->name('admin.professors.assign');

        // Advisory assignments
        Route::post('/professors/{professor}/assign-adviser', [ProfessorController::class, 'assignAdviser'])->name('admin.professors.assignAdviser');
        Route::delete('/professors/{id}/remove-adviser', [ProfessorController::class, 'removeAdviser'])->name('admin.professors.removeAdviser');
        Route::delete('/remove-assignment/{id}', [ProfessorController::class, 'removeAssignment'])->name('admin.professors.removeAssignment');

        // Students management
        Route::get('/students', [StudentController::class, 'index'])->name('admin.students.index');
        Route::get('/students/create', [StudentController::class, 'create'])->name('admin.students.create');
        Route::post('/students', [StudentController::class, 'store'])->name('admin.students.store');
        Route::get('/students/{user}/edit', [StudentController::class, 'edit'])->name('admin.students.edit');
        Route::put('/students/{user}', [StudentController::class, 'update'])->name('admin.students.update');
        Route::delete('/students/delete/{id}', [StudentController::class, 'destroy'])->name('admin.students.destroy');

        // Promotion routes
        Route::post('/students/{id}/promote', [StudentController::class, 'promote'])->name('admin.students.promote');
        Route::post('/students/promote/bulk', [StudentController::class,'promoteBulk'])->name('admin.students.promote.bulk');
        Route::post('/students/{id}/update-completion', [StudentController::class, 'updateCompletion'])->name('admin.students.updateCompletion');
        Route::post('/students/{id}/mark-completion', [StudentController::class, 'markCompletion'])->name('admin.students.markCompletion');
        Route::post('/students/{id}/mark-promotion', [StudentController::class, 'markPromotion'])->name('admin.students.markPromotion');
        Route::post('/students/{id}/approve-promotion', [StudentController::class, 'approvePromotion'])->name('admin.promotion.approve');
        Route::post('/students/promote-approved/{id}', [StudentController::class,'promoteApproved']);
        Route::get('/students/view-grades/{id}', [App\Http\Controllers\Admin\StudentController::class, 'viewGrades'])->name('admin.students.viewGrades');

        // Announcements
        Route::get('/announcements', [AnnouncementController::class, 'index'])->name('admin.announcements.index');
        Route::get('/announcements/create', [AnnouncementController::class, 'create'])->name('admin.announcements.create');
        Route::post('/announcements', [AnnouncementController::class, 'store'])->name('admin.announcements.store');
        Route::get('/announcements/{announcement}/edit', [AnnouncementController::class, 'edit'])->name('admin.announcements.edit');
        Route::put('/announcements/{announcement}',[AnnouncementController::class, 'update'])->name('admin.announcements.update');
        Route::delete('/announcements/{announcement}', [AnnouncementController::class, 'destroy'])->name('admin.announcements.destroy');
        Route::get('/announcements/{announcement}', [AnnouncementController::class, 'show'])->name('admin.announcements.show');

        // Forms & Form137
        Route::resource('forms', FormController::class)
            ->except(['show'])
            ->names([
                'index' => 'admin.forms.index',
                'create' => 'admin.forms.create',
                'store' => 'admin.forms.store',
                'edit' => 'admin.forms.edit',
                'update' => 'admin.forms.update',
                'destroy' => 'admin.forms.destroy',
            ]);
        Route::get('forms/download/{form}', [FormController::class, 'download'])->name('admin.forms.download');
        Route::get('forms/preview/{form}', [FormController::class, 'preview'])->name('admin.forms.preview');
        Route::get('/form137/export/{id}', [App\Http\Controllers\Admin\Form137Controller::class, 'export'])->name('admin.form137.export');

        // Grade approvals
        Route::get('/grades', [AdminGradeController::class, 'index'])->name('admin.grades.index');
        Route::get('/grades/{school_year}', [AdminGradeController::class, 'show'])->name('admin.grades.show');

        // Grade approval actions
        Route::post('/grades/approve/student/{studentId}', [GradeApprovalController::class, 'approveAll'])->name('admin.grades.approve');
        Route::post('/grades/return/student/{studentId}', [GradeApprovalController::class, 'returnGrades'])->name('admin.grades.return');
        Route::post('/grades/approve/all', [GradeApprovalController::class, 'approveAllPending'])->name('admin.grades.approve.all');
        Route::post('/grades/approve/student/{student}', [GradeApprovalController::class, 'approveStudent'])->name('admin.grades.approve.student');
        Route::get('/grades/student/{studentId}', [GradeApprovalController::class, 'getStudentGrades']);
        Route::get('/grade-approvals', [GradeApprovalController::class, 'index'])->name('admin.grade-approvals.index');

        // Promotion evaluation
        Route::get('/promotion/evaluate/{schoolYear}', [PromotionController::class, 'viewEvaluation'])->name('admin.promotion.viewEvaluation');
        Route::post('/promotion/evaluate/{schoolYear}', [PromotionController::class, 'evaluate'])->name('admin.promotion.evaluate');
        Route::get('/promotion/view/{schoolYear?}', [PromotionController::class, 'viewEvaluation']);
        Route::get('/evaluate', [PromotionController::class, 'evaluateView'])->name('admin.promotion.evaluateView');
        Route::get('/review', [PromotionController::class, 'reviewView'])->name('admin.promotion.reviewView');
        Route::post('/review/{id}', [PromotionController::class, 'updateStatus'])->name('admin.promotion.updateStatus');
        Route::post('/overview', [PromotionController::class, 'overview'])->name('admin.promotion.view');
        Route::match(['get', 'post'], '/overview', [PromotionController::class, 'manage'])->name('promotion.overview');
        Route::get('promotion/review', [PromotionController::class, 'reviewPromotion'])->name('admin.promotion.review');

        //Alumni Management
        Route::get('/alumni', [AlumniController::class, 'index'])->name('admin.alumni.index');

        // Report card requests
        Route::get('/report-card/requests', [ReportCardController::class, 'index'])->name('admin.reportcard.index');
        Route::get('/form-requests/archive', [ReportCardController::class, 'archive'])->name('admin.reportcard.archive');
        Route::post('/report-card/requests/{id}/approve', [ReportCardController::class, 'approve'])->name('admin.reportcard.approve');
        Route::post('/report-card/requests/{id}/decline', [ReportCardController::class, 'decline'])->name('admin.reportcard.decline');

        //Report Card Export
        // Route::get('excel/exportAll/{grade_level?}', [ECRController::class, 'exportAll'])->name('excel.exportAll');
        // Route::get('/excel/exportSingle/{id}', [ECRController::class, 'exportSingle'])->name('excel.exportSingle');

        //Summary of Grades
        Route::get('/grade-summary', [GradeSummaryController::class, 'index'])->name('grades.summary');

        // Class Records
        Route::get('class-record', [ECRController::class, 'index'])->name('classrecord.index');

        //Audit Trail Logs
        Route::get('/audit-trails', [App\Http\Controllers\Admin\AuditTrailController::class, 'index'])->name('admin.audit-trails.index');

        // Grade Levels & Subjects
        Route::resource('grade-levels', GradeLevelController::class)->except(['show'])->names([
            'index' => 'admin.grade-levels.index',
            'create' => 'admin.grade-levels.create',
            'store' => 'admin.grade-levels.store',
            'edit' => 'admin.grade-levels.edit',
            'update' => 'admin.grade-levels.update',
            'destroy' => 'admin.grade-levels.destroy',
        ]);
        Route::resource('subjects', SubjectController::class)->except(['show'])->names([
            'index' => 'admin.subjects.index',
            'create' => 'admin.subjects.create',
            'store' => 'admin.subjects.store',
            'edit' => 'admin.subjects.edit',
            'update' => 'admin.subjects.update',
            'destroy' => 'admin.subjects.destroy',
        ]);

        // Grade Returned Resolution
        Route::patch('grades/returned/{grade}/resolve', [DashboardController::class, 'markResolved'])->name('grades.markResolved');

        // Settings
        Route::get('/settings', [SettingsController::class, 'index'])->name('admin.settings.index');
        Route::post('/settings/update', [SettingsController::class, 'update'])->name('admin.settings.update');
        Route::post('/settings/general', [SettingsController::class, 'updateGeneral'])->name('admin.settings.updateGeneral');
        Route::get('/settings/backup/database', [SettingsController::class, 'backupDatabase'])
     ->name('admin.settings.backup.database');

        Route::post('/settings/security', [SettingsController::class, 'updateSecurity'])->name('admin.settings.updateSecurity');

        // Logout
        Route::post('/logout', [AdminLogoutController::class, 'logout'])->name('admin.logout');

        //Change Password
        Route::get('/change-password', [AdminChangePasswordController::class, 'show'])->name('admin.change-password.form');
        Route::post('/change-password', [AdminChangePasswordController::class, 'updatePassword'])->name('admin.change-password.update');

    });

    /*
    |--------------------------------------------------------------------------
    | Super Admin Routes
    |--------------------------------------------------------------------------
    */

    // Route::prefix('superadmin')
    // ->group(function () {

    //     Route::get('/dashboard', [SuperAdminDashboard::class, 'index'])->name('super_admin.dashboard');

    //     // Admin Management
    //     Route::get('/', [AdminManagementController::class, 'index'])->name('index');
    //     Route::post('/{admin}/activate', [AdminManagementController::class, 'activate'])->name('activate');
    //     Route::post('/{admin}/deactivate', [AdminManagementController::class, 'deactivate'])->name('deactivate');
    // });

    Route::prefix('superadmin')
    ->group(function () {
                
        Route::get('/dashboard', [SuperAdminDashboard::class, 'index'])->name('super_admin.dashboard');

        Route::get('/admins', [AdminManagementController::class, 'index'])
            ->name('admins.index');

        Route::post('/admins/{admin}/activate', [AdminManagementController::class, 'activate'])
            ->name('admins.activate');

        Route::post('/admins/{admin}/deactivate', [AdminManagementController::class, 'deactivate'])
            ->name('admins.deactivate');
        
        Route::post('/backup/create', [BackupController::class, 'create'])->name('super_admin.backup.create');
        Route::get('/audit', [AuditController::class, 'index'])->name('super_admin.audit.index');

    });


});
