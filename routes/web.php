<?php

use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Route;
use App\Http\Controllers\CheckInController;
use App\Http\Controllers\CheckoutController;
use App\Http\Controllers\FrontendController;
use App\Http\Controllers\Admin\RoleController;
use App\Http\Controllers\Auth\LoginController;
use App\Http\Controllers\Admin\AddonController;
use App\Http\Controllers\EnvironmentController;
use App\Http\Controllers\PurchaseCodeController;
use App\Http\Controllers\Admin\ProfileController;
use App\Http\Controllers\Admin\SettingController;
use App\Http\Controllers\Admin\VisitorController;
use App\Http\Controllers\Admin\EmployeeController;
use App\Http\Controllers\Admin\InternalStaffDataController;
use App\Http\Controllers\Admin\LanguageController;
use App\Http\Controllers\Admin\AdminUserController;
use App\Http\Controllers\Admin\DashboardController;
use App\Http\Controllers\Admin\AttendanceController;
use App\Http\Controllers\Admin\DepartmentsController;

use App\Http\Controllers\Admin\PreRegisterController;
use App\Http\Controllers\Admin\DesignationsController;
use App\Http\Controllers\Admin\LocalizationController;
use App\Http\Controllers\Admin\VisitorReportController;
use App\Http\Controllers\Admin\VisitDestinationController;
use App\Http\Controllers\Admin\VisitDestinationQueueController;
use App\Http\Controllers\Admin\WebNotificationController;
use App\Http\Controllers\Admin\AttendanceReportController;
use App\Http\Controllers\Admin\PreRegistersReportController;

Route::group(['middleware' => ['installed']], function () {
    Auth::routes(['verify' => false]);
});
Route::group(['prefix' => 'install', 'as' => 'LaravelInstaller::', 'middleware' => ['web', 'install']], function () {
    Route::post('environment/saveWizard', [EnvironmentController::class, 'saveWizard'])->name('environmentSaveWizard');

    Route::get('purchase-code', [PurchaseCodeController::class, 'index'])->name('purchase_code');

    Route::post('purchase-code', [PurchaseCodeController::class, 'action'])->name('purchase_code.check');
});

Route::redirect('/', '/admin/dashboard')->middleware('backend_permission');
Route::redirect('/admin', '/admin/dashboard')->middleware('backend_permission');

Route::group(['prefix' => 'admin', 'middleware' => ['installed'], 'namespace' => 'Admin', 'as' => 'admin.'], function () {
    Route::get('login', [LoginController::class, 'showLoginForm']);
});

Route::get('admin/lang/{locale}', [LocalizationController::class, 'index'])->middleware(['installed'])->name('admin.lang.index');

// Carga de data interna SENIAT pública (sin login), accesible por enlace directo
Route::group(['prefix' => 'admin', 'middleware' => ['installed'], 'as' => 'admin.'], function () {
    Route::get('internal-staff-data', [InternalStaffDataController::class, 'create'])->name('internal-staff-data.create');
    Route::post('internal-staff-data', [InternalStaffDataController::class, 'store'])->name('internal-staff-data.store');
    Route::post('internal-staff-data/massive', [InternalStaffDataController::class, 'storeMassive'])->name('internal-staff-data.store-massive');
    Route::get('internal-staff-data/massive/template', [InternalStaffDataController::class, 'downloadMassiveTemplate'])->name('internal-staff-data.download-template');
    Route::get('internal-staff-data/designations/search', [InternalStaffDataController::class, 'searchDesignations'])->name('internal-staff-data.designations.search');
    Route::get('internal-staff-data/departments/search', [InternalStaffDataController::class, 'searchDepartments'])->name('internal-staff-data.departments.search');
    Route::get('internal-staff-data/designation/{designation}', [InternalStaffDataController::class, 'getDesignation'])->name('internal-staff-data.designation.get');
    Route::get('internal-staff-data/department/{department}', [InternalStaffDataController::class, 'getDepartment'])->name('internal-staff-data.department.get');
});

// URL corta para acceso rápido por intranet
Route::redirect('cdi', 'admin/internal-staff-data')->middleware(['installed']);

Route::group(['prefix' => 'admin', 'middleware' => ['auth', 'installed', 'backend_permission'], 'as' => 'admin.'], function () {

    Route::get('dashboard', [DashboardController::class, 'index'])->name('dashboard.index');
    Route::get('profile', [ProfileController::class, 'index'])->name('profile');
    Route::put('profile/update/{profile}', [ProfileController::class, 'update'])->name('profile.update');
    Route::put('profile/change', [ProfileController::class, 'change'])->name('profile.change');
    Route::resource('adminusers', AdminUserController::class);
    Route::get('get-adminusers', [AdminUserController::class, 'getAdminUsers'])->name('adminusers.get-adminusers');
    Route::resource('role', RoleController::class);
    Route::post('role/save-permission/{id}', [RoleController::class, 'savePermission'])->name('role.save-permission');

    //designations - Apply supervisor headquarters scope
    Route::group(['middleware' => ['supervisor.scope:designations']], function () {
        Route::resource('designations', DesignationsController::class);
        Route::get('get-designations', [DesignationsController::class, 'getDesignations'])->name('designations.get-designations');
    });

    //departments - Apply supervisor headquarters scope
    Route::group(['middleware' => ['supervisor.scope:departments']], function () {
        Route::resource('departments', DepartmentsController::class);
        Route::get('get-departments', [DepartmentsController::class, 'getDepartments'])->name('departments.get-departments');
    });



    //headquarters (sedes)
    Route::resource('headquarters', \App\Http\Controllers\Admin\HeadquartersController::class);
    Route::get('get-headquarters', [\App\Http\Controllers\Admin\HeadquartersController::class, 'getHeadquarters'])->name('headquarters.get-headquarters');
    Route::get('get-headquarters-by-region', [\App\Http\Controllers\Admin\HeadquartersController::class, 'getHeadquartersByRegion'])->name('headquarters.get-by-region');
    Route::get('get-dependencies-by-region', [\App\Http\Controllers\Admin\HeadquartersController::class, 'getDependenciesByRegion'])->name('headquarters.get-dependencies-by-region');

    //web-token
    Route::post('store-token', [WebNotificationController::class, 'store'])->name('store.token');

    //employee route - Apply supervisor headquarters scope
    Route::group(['middleware' => ['supervisor.scope:employees']], function () {
        Route::resource('employees', EmployeeController::class);
        Route::get('get-employees', [EmployeeController::class, 'getEmployees'])->name('employees.get-employees');
        Route::get('employees/get-pre-registers/{id}', [EmployeeController::class, 'getPreRegister'])->name('employees.get-pre-registers');
        Route::get('employees/get-visitors/{id}', [EmployeeController::class, 'getVisitor'])->name('employees.get-visitors');
        Route::put('employees/check/{id}', [EmployeeController::class, 'checkEmployee'])->name('employees.check');
    });

    //pre-registers - Apply supervisor headquarters scope
    Route::group(['middleware' => ['supervisor.scope:pre-registers']], function () {
        Route::resource('pre-registers', PreRegisterController::class);
        Route::get('get-pre-registers', [PreRegisterController::class, 'getPreRegister'])->name('pre-registers.get-pre-registers');
    });

    //visitors - Apply supervisor headquarters scope
    Route::group(['middleware' => ['supervisor.scope:visitors']], function () {
        Route::resource('visitors', VisitorController::class);
        Route::post('visitor/search', [VisitorController::class, 'search'])->name('visitor.search');
        Route::get('visitor/check-out/{visitingDetail}', [VisitorController::class, 'checkout'])->name('visitors.checkout');
        Route::get('visitor/change-status/{id}/{status}/{dashboard}',  [VisitorController::class, 'changeStatus'])->name('visitor.change-status');
        Route::get('get-visitors', [VisitorController::class, 'getVisitor'])->name('visitors.get-visitors');
        Route::get('visitor/disable/{id}',  [VisitorController::class, 'visitorDisable'])->name('visitors.disable');
        Route::get('visitor/get-employee-region-headquarters', [VisitorController::class, 'getEmployeeRegionHeadquarters'])->name('visitor.get-employee-region-headquarters');
    });

    Route::get('visit-destination-queue', [VisitDestinationQueueController::class, 'index'])->name('visit-destination-queue.index');
    Route::get('visit-destination-queue/get-visits', [VisitDestinationQueueController::class, 'getVisits'])->name('visit-destination-queue.get-visits');

    Route::resource('visit-destinations', VisitDestinationController::class)->except(['show']);
    Route::get('get-visit-destinations', [VisitDestinationController::class, 'getVisitDestinations'])->name('visit-destinations.get-visit-destinations');
    Route::post('visit-destinations/{visit_destination}/rules', [VisitDestinationController::class, 'storeRule'])->name('visit-destinations.rules.store');
    Route::delete('visit-destinations/{visit_destination}/rules/{rule}', [VisitDestinationController::class, 'destroyRule'])->name('visit-destinations.rules.destroy');

    //report - Apply supervisor headquarters scope
    Route::group(['middleware' => ['supervisor.scope:reports']], function () {
        Route::get('admin-visitor-report', [VisitorReportController::class, 'index'])->name('admin-visitor-report.index');
        Route::post('admin-visitor-report', [VisitorReportController::class, 'index'])->name('admin-visitor-report.post');

        Route::get('admin-pre-registers-report', [PreRegistersReportController::class, 'index'])->name('admin-pre-registers-report.index');
        Route::post('admin-pre-registers-report', [PreRegistersReportController::class, 'index'])->name('admin-pre-registers-report.post');

        Route::get('attendance-report', [AttendanceReportController::class, 'index'])->name('attendance-report.index');
        Route::post('attendance-report', [AttendanceReportController::class, 'index'])->name('attendance-report.post');
    });

    Route::post('admin-attendance/clockin', [AttendanceController::class, 'clockIn'])->name('attendance.clockin');
    Route::post('admin-attendance/clockout', [AttendanceController::class, 'clockOut'])->name('attendance.clockout');

    Route::resource('attendance', AttendanceController::class);
    Route::get('get-attendance', [AttendanceController::class, 'getAttendance'])->name('attendance.get-attendance');
    //language
    Route::resource('language', LanguageController::class);
    Route::get('get-language', [LanguageController::class, 'getLanguage'])->name('language.get-language');
    Route::get('language/change-status/{id}/{status}', [LanguageController::class, 'changeStatus'])->name('language.change-status');

    //Addons
    Route::resource('addons', AddonController::class);

    Route::group(['prefix' => 'setting', 'as' => 'setting.'], function () {
        Route::get('/', [SettingController::class, 'index'])->name('index');
        Route::post('/', [SettingController::class, 'siteSettingUpdate'])->name('site-update');
        Route::get('sms', [SettingController::class, 'smsSetting'])->name('sms');
        Route::post('sms', [SettingController::class, 'smsSettingUpdate'])->name('sms-update');
        Route::get('fcm-notification', [SettingController::class, 'fcmSetting'])->name('fcm');
        Route::post('fcm-notification', [SettingController::class, 'fcmSettingUpdate'])->name('fcm-update');
        Route::get('email', [SettingController::class, 'emailSetting'])->name('email');
        Route::post('email', [SettingController::class, 'emailSettingUpdate'])->name('email-update');
        Route::get('notification', [SettingController::class, 'notificationSetting'])->name('notification');
        Route::post('notification', [SettingController::class, 'notificationSettingUpdate'])->name('notification-update');
        Route::get('emailtemplate', [SettingController::class, 'emailTemplateSetting'])->name('email-template');
        Route::post('emailtemplate', [SettingController::class, 'mailTemplateSettingUpdate'])->name('email-template-update');
        Route::get('homepage', [SettingController::class, 'homepageSetting'])->name('homepage');
        Route::post('homepage', [SettingController::class, 'homepageSettingUpdate'])->name('homepage-update');
        Route::get('whatsapp', [SettingController::class, 'whatsappSetting'])->name('whatsapp-message');
        Route::post('whatsapp', [SettingController::class, 'whatsappSettingupdate'])->name('whatsapp-message-update');
    });
});



/*Multi step form*/

Route::group(['middleware' => ['installed']], function () {
    Route::group(['middleware' => ['frontend']], function () {
        Route::get('/home', [CheckInController::class, 'index'])->name('home');
        Route::get('/', [CheckInController::class, 'index'])->name('/');
        Route::get('/scanqr', [CheckInController::class, 'scanQr'])->name('check-in.scan-qr');

        Route::get('/checkout', [CheckoutController::class, 'index'])->name('checkout.index');

        Route::post('/checkout', [CheckoutController::class, 'getVisitor'])->name('checkout.post');

        Route::get('/checkout/update/{visitingDetails}', [CheckoutController::class, 'update'])->name('checkout.update');

        Route::get('/check-in', [CheckInController::class, 'index'])->name('check-in');
        Route::get('/check-in/create-step-one', [CheckInController::class, 'createStepOne'])->name('check-in.step-one');
        Route::post('/check-in/create-step-one', [CheckInController::class, 'postCreateStepOne'])->name('check-in.step-one.next');
        Route::get('/check-in/create-step-two', [CheckInController::class, 'createStepTwo'])->name('check-in.step-two');
        Route::post('/check-in/create-step-two', [CheckInController::class, 'store'])->name('check-in.step-two.next');

        Route::get('/check-in/show/{id}', [CheckInController::class, 'show'])->name('check-in.show');
        Route::get('/check-in/return', [CheckInController::class, 'visitor_return'])->name('check-in.return');
        Route::post('/check-in/return', [CheckInController::class, 'find_visitor'])->name('check-in.find.visitor');

        Route::get('/check-in/pre-registered', [CheckInController::class, 'pre_registered'])->name('check-in.pre.registered');
        Route::post('/check-in/pre-registered', [CheckInController::class, 'find_pre_visitor'])->name('check-in.find.pre.visitor');

        /**
         * Scan Qr Code
         */
        Route::get('check-in/visitor-details/{visitorPhone}', [CheckInController::class, 'visitorDetails'])->name('checkin.visitor-details');
        Route::get('check-in/pre-registered/visitor-details/{visitorPhone}', [CheckInController::class, 'preVisitorDetails'])->name('checkin.pre-visitor-details');
    });
});

Route::get('visitor/change-status/{status}/{token}',  [FrontendController::class, 'changeStatus']);

Route::get('qrcode/{number}',  [FrontendController::class, 'qrcode'])->name('qrcode');
Route::get('terms_and_conditions',  [FrontendController::class, 'termsConditions'])->name('terms_and_conditions.view');
