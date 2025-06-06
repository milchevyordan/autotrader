<?php

declare(strict_types=1);

use App\Http\Controllers\Auth\ImageController;
use App\Http\Controllers\CompanyController;
use App\Http\Controllers\CompanyGroupController;
use App\Http\Controllers\ConfigurationController;
use App\Http\Controllers\Crm\CompanyController as CrmCompanyController;
use App\Http\Controllers\Crm\UserController as CrmUserController;
use App\Http\Controllers\DashboardBoxesController;
use App\Http\Controllers\DashboardController;
use App\Http\Controllers\DocumentController;
use App\Http\Controllers\EmailTemplateController;
use App\Http\Controllers\EngineController;
use App\Http\Controllers\FileController;
use App\Http\Controllers\InternalRemarkReplyController;
use App\Http\Controllers\ItemController;
use App\Http\Controllers\MailController;
use App\Http\Controllers\MakeController;
use App\Http\Controllers\NotificationController;
use App\Http\Controllers\OwnershipController;
use App\Http\Controllers\PdfController;
use App\Http\Controllers\PreOrderController;
use App\Http\Controllers\PreOrderVehicleController;
use App\Http\Controllers\ProfileController;
use App\Http\Controllers\PurchaseOrderController;
use App\Http\Controllers\QuoteController;
use App\Http\Controllers\QuoteInvitationController;
use App\Http\Controllers\RoleController;
use App\Http\Controllers\SalesOrderController;
use App\Http\Controllers\ServiceLevelController;
use App\Http\Controllers\ServiceOrderController;
use App\Http\Controllers\ServiceVehicleController;
use App\Http\Controllers\SettingController;
use App\Http\Controllers\TransportOrderController;
use App\Http\Controllers\UserController;
use App\Http\Controllers\UserGroupController;
use App\Http\Controllers\VariantController;
use App\Http\Controllers\VehicleController;
use App\Http\Controllers\VehicleGroupController;
use App\Http\Controllers\VehicleModelController;
use App\Http\Controllers\WorkflowController;
use App\Http\Controllers\WorkflowFinishedStepController;
use App\Http\Controllers\WorkOrderController;
use App\Http\Controllers\WorkOrderTaskController;
use Illuminate\Foundation\Application;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Redirect;
use Illuminate\Support\Facades\Route;
use Inertia\Inertia;

/*
|--------------------------------------------------------------------------
| Web Routes
|--------------------------------------------------------------------------
|
| Here is where you can register web routes for your application. These
| routes are loaded by the RouteServiceProvider within a group which
| contains the "web" middleware group. Now create something great!
|
*/

Route::get('/', function () {
    if (! Auth::check()) {
        return Inertia::render('auth/Login', [
            'laravelVersion' => Application::VERSION,
            'phpVersion'     => PHP_VERSION,
        ]);
    }

    return Redirect::route('dashboard');
});

Route::get('/dashboard', [DashboardController::class, 'index'])->middleware(['auth', 'verified'])->name('dashboard');
Route::post('/dashboard/recache', [DashboardController::class, 'refresh'])->name('dashboard.refresh');

// changing language
Route::get('language/{locale}', function ($locale) {
    app()->setLocale($locale);
    session()->put('locale', $locale);

    return redirect()->back();
})->name('language');

Route::middleware('auth')->group(function () {
    Route::get('/profile', [ProfileController::class, 'edit'])->name('profile.edit');
    Route::patch('/profile', [ProfileController::class, 'update'])->name('profile.update');
    Route::post('/profile/update-profile-image', [ProfileController::class, 'updateProfileImage'])->name('profile.update-profile-image');
    Route::delete('/profile', [ProfileController::class, 'destroy'])->name('profile.destroy');

    Route::get('/notifications', [NotificationController::class, 'index'])->name('notifications.index');
    Route::put('/notifications/read/{id}', [NotificationController::class, 'read'])->name('notifications.read');
    Route::post('/notifications/read-all', [NotificationController::class, 'readAll'])->name('notifications.read-all');

    Route::middleware('roles:Super Manager,Management,Company Purchaser,Manager SalesPurchasing,Back Office Employee,Back Office Manager,Logistics Employee,Finance Employee')->group(function () {
        Route::get('/vehicles/overview', [VehicleController::class, 'overview'])->name('vehicles.overview');
        Route::get('/vehicles/following', [VehicleController::class, 'following'])->name('vehicles.following');
        Route::get('/vehicles/management/{slug}', [VehicleController::class, 'management'])->name('vehicles.management');
        Route::resource('vehicles', VehicleController::class)->except(['show']);
        Route::post('/vehicles/duplicate', [VehicleController::class, 'duplicate'])->name('vehicles.duplicate');
        Route::post('/vehicles/{vehicle}/sales-order', [VehicleController::class, 'storeSalesOrder'])->name('vehicles.sales-order');
        Route::post('/vehicles/{vehicle}/generate-quote', [VehicleController::class, 'generateQuote'])->name('vehicles.generate-quote');
        Route::put('/vehicles/{vehicle}/follow', [VehicleController::class, 'follow'])->name('vehicles.follow');
        Route::get('/vehicles/{token}/transfer', [VehicleController::class, 'transfer'])->name('vehicles.transfer');

        Route::resource('pre-order-vehicles', PreOrderVehicleController::class)->except('show');

        Route::resource('service-vehicles', ServiceVehicleController::class)->except('show');

        Route::resource('engines', EngineController::class)->only(['index', 'store', 'update', 'destroy']);

        Route::resource('variants', VariantController::class)->only(['index', 'store', 'update', 'destroy']);

        Route::resource('makes', MakeController::class)->only(['index', 'store', 'update', 'destroy']);

        Route::resource('vehicle-models', VehicleModelController::class)->only(['index', 'store', 'update', 'destroy']);

        Route::resource('vehicle-groups', VehicleGroupController::class)->only(['index', 'store', 'update', 'destroy']);

        Route::get('/ownerships', [OwnershipController::class, 'index'])->name('ownerships.index');
        Route::get('/ownerships/{ownership}', [OwnershipController::class, 'show'])->name('ownerships.show');
        Route::patch('/ownerships/{ownership}/accept', [OwnershipController::class, 'accept'])->name('ownerships.accept');
        Route::patch('/ownerships/{ownership}/reject', [OwnershipController::class, 'reject'])->name('ownerships.reject');

        Route::resource('items', ItemController::class)->only(['index', 'store', 'update', 'destroy']);

        Route::name('crm.')->prefix('crm')->group(function () {
            Route::resource('companies', CrmCompanyController::class)->except('show');
            Route::post('companies/users', [CrmCompanyController::class, 'storeUser'])->name('companies.store-user');

            Route::resource('company-groups', CompanyGroupController::class)->only(['index', 'store', 'update', 'destroy']);

            Route::resource('users', CrmUserController::class)->except('destroy');

            Route::resource('user-groups', UserGroupController::class)->except('show');
        });

        Route::patch('/purchase-orders/update-status', [PurchaseOrderController::class, 'updateStatus'])->name('purchase-orders.update-status');
        Route::resource('purchase-orders', PurchaseOrderController::class)->except('show');

        Route::patch('/pre-orders/update-status', [PreOrderController::class, 'updateStatus'])->name('pre-orders.update-status');
        Route::post('/pre-orders/vehicles', [PreOrderController::class, 'storeVehicles'])->name('pre-orders.vehicles');
        Route::resource('pre-orders', PreOrderController::class)->except('show');

        Route::patch('/sales-orders/update-status', [SalesOrderController::class, 'updateStatus'])->name('sales-orders.update-status');
        Route::resource('sales-orders', SalesOrderController::class)->except('show');
        Route::post('/sales-orders/{vehicles}/work-order', [SalesOrderController::class, 'storeWorkOrder'])->name('sales-orders.work-order');

        Route::patch('/service-orders/update-status', [ServiceOrderController::class, 'updateStatus'])->name('service-orders.update-status');
        Route::resource('service-orders', ServiceOrderController::class)->except('show');

        Route::resource('workflows', WorkflowController::class)->only(['store', 'show']);

        Route::resource('workflow-finished-steps', WorkflowFinishedStepController::class)->only(['store', 'update']);
        Route::post('/workflow-finished-steps', [WorkflowFinishedStepController::class, 'upsert'])->name('workflow-finished-steps.upsert');
        Route::delete('/workflow-finished-steps/delete', [WorkflowFinishedStepController::class, 'destroy'])->name('workflow-finished-steps.destroy');

        Route::get('/dashboard-boxes/{slug}', [DashboardBoxesController::class, 'index'])->name('dashboard-boxes.index');
        Route::get('/dashboard-boxes/{slug}/flags', [DashboardBoxesController::class, 'index'])->name('dashboard-boxes.flags');

        Route::patch('/work-orders/update-status', [WorkOrderController::class, 'updateStatus'])->name('work-orders.update-status');
        Route::resource('work-orders', WorkOrderController::class)->except('show');
        Route::post('/work-orders/create-from-vehicle', [WorkOrderController::class, 'createFromVehicle'])->name('work-orders.create-from-vehicle');

        Route::resource('work-order-tasks', WorkOrderTaskController::class)->only(['store', 'update', 'destroy']);

        Route::patch('/transport-orders/update-status', [TransportOrderController::class, 'updateStatus'])->name('transport-orders.update-status');
        Route::patch('/transport-orders/generate-file', [TransportOrderController::class, 'generateFile'])->name('transport-orders.generate-file');
        Route::resource('transport-orders', TransportOrderController::class)->except('show');

        Route::resource('service-levels', ServiceLevelController::class);

        Route::patch('/documents/update-status', [DocumentController::class, 'updateStatus'])->name('documents.update-status');
        Route::resource('documents', DocumentController::class)->except('show');

        Route::patch('/quotes/update-status', [QuoteController::class, 'updateStatus'])->name('quotes.update-status');
        Route::resource('quotes', QuoteController::class)->except('show');
        Route::patch('/quotes/{quote}/reserve', [QuoteController::class, 'reserve'])->name('quotes.reserve');
        Route::patch('/quotes/{quote}/cancel-reservation', [QuoteController::class, 'cancelReservation'])->name('quotes.cancel-reservation');
        Route::patch('/quotes/{quote}/accept-customer', [QuoteController::class, 'acceptCustomer'])->name('quotes.accept-customer');
        Route::post('/quotes/{quote}/sales-order', [QuoteController::class, 'storeSalesOrder'])->name('quotes.sales-order');
        Route::post('/quotes/{quote}/duplicate', [QuoteController::class, 'duplicate'])->name('quotes.duplicate');

        Route::resource('quote-invitations', QuoteInvitationController::class)->except(['index', 'show']);

        Route::get('mails', [MailController::class, 'index'])->name('mails.index');
    });

    Route::middleware('roles:Super Manager,Management,Company Purchaser,Manager SalesPurchasing,Back Office Employee,Back Office Manager,Logistics Employee,Finance Employee,Customer')->group(function () {
        Route::resource('quote-invitations', QuoteInvitationController::class)->only(['index', 'show', 'store']);
        Route::patch('/quotes-invitations/{id}/accept', [QuoteInvitationController::class, 'accept'])->name('quote-invitations.accept');
        Route::patch('/quotes-invitations/{id}/reject', [QuoteInvitationController::class, 'reject'])->name('quote-invitations.reject');
        Route::get('/quotes-invitations/{quoteId}/invitation', [QuoteInvitationController::class, 'invitation'])->name('quote-invitations.invitation');
    });

    Route::resource('setting', SettingController::class)->only(['index', 'store', 'update']);
    Route::post('setting/logo', [SettingController::class, 'updateLogo'])->name('settings.logo.update');

    Route::resource('email-templates', EmailTemplateController::class)->only(['edit', 'update']);

    Route::resource('internal-remark-reply-controller', InternalRemarkReplyController::class)->only('store');

    Route::resource('companies', CompanyController::class)->middleware('roles:Root')->except(['show', 'destroy']);

    Route::resource('users', UserController::class)->middleware('roles:Root,Company Administrator')->except('destroy');

    Route::resource('roles', RoleController::class)->middleware('roles:Root,Company Administrator');

    Route::resource('configurations', ConfigurationController::class)->middleware('roles:Root')->only('index');

    Route::name('images.')->group(function () {
        Route::put('/images/order', [ImageController::class, 'order'])->name('order');
        Route::delete('/images/{path}', [ImageController::class, 'destroy'])->name('destroy');
    });

    Route::name('files.')->group(function () {
        Route::put('/files/order', [FileController::class, 'fileOrder'])->name('order');
        Route::get('/download/files/{path}', [FileController::class, 'download'])->name('download');
        Route::post('/files/download-archived', [FileController::class, 'downloadArchived'])->name('downloadArchived');
        Route::post('/files/download-and-delete', [FileController::class, 'downloadAndDelete'])->name('downloadAndDelete');
        Route::delete('/files/{path}', [FileController::class, 'destroy'])->name('destroy');
    });

    Route::post('/pdf/send-mail/', [PdfController::class, 'sendMail'])->name('pdf.send_mail');
});

require __DIR__.'/auth.php';
