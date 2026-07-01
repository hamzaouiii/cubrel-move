<?php

use Illuminate\Support\Facades\Route;

use App\Http\Controllers\AuthController;
use App\Http\Controllers\DashboardController;
use App\Http\Controllers\DropdownListController;
use App\Http\Controllers\FieldsManagerController;
use App\Http\Controllers\InviteController;
use App\Http\Controllers\LayoutManagerController;
use App\Http\Controllers\ListController;
use App\Http\Controllers\ListFilterController;
use App\Http\Controllers\ModuleBuilderController;
use App\Http\Controllers\ModuleDeploymentController;
use App\Http\Controllers\ModuleManagerController;
use App\Http\Controllers\PdfController;
use App\Http\Controllers\RecordController;
use App\Http\Controllers\RelatedFieldController;
use App\Http\Controllers\RelationshipLinkController;
use App\Http\Controllers\RelationshipManagerController;
use App\Http\Controllers\SearchController;
use App\Http\Controllers\SettingsController;
use App\Http\Controllers\SetupController;
use App\Http\Controllers\UserController;
use App\Http\Controllers\UserProfileController;
use App\Http\Controllers\LineItemController;
use App\Http\Controllers\PdfTemplatesController;
use App\Http\Controllers\ExportController;
use App\Http\Controllers\ImageUploadController;


Route::middleware(['guest'])->group(function () {
    Route::get('/login', [AuthController::class, 'index'])->name('login');
    Route::post('/login', [AuthController::class, 'login']);
    Route::post('/forgot-password', [AuthController::class, 'forgot'])->name('password.email');
    Route::get('/reset-password/{token}', [AuthController::class, 'resetForm'])->name('password.reset');
    Route::post('/reset-password', [AuthController::class, 'reset'])->name('password.update');
    Route::get('/invites/{token}', [InviteController::class, 'show'])->name('invites.show');
    Route::post('/invites/{token}/accept', [InviteController::class, 'accept'])->name('invites.accept');
    Route::get('/setup/{token}', [SetupController::class, 'show'])->name('setup.show');
    Route::post('/setup/{token}', [SetupController::class, 'store'])->name('setup.store');
});

Route::middleware(['auth'])->group(function () {

    // Independent routes
    Route::get('/', [DashboardController::class, 'index'])->name('dashboard');
    Route::post('/dashboard/layout', [DashboardController::class, 'saveLayout'])->name('dashboard.layout.save');
    Route::post('/dashboard/widget-data', [DashboardController::class, 'widgetData'])->name('dashboard.widget-data');
    Route::get('/dashboard/module-fields/{slug}', [DashboardController::class, 'moduleFields'])->name('dashboard.module-fields');
    Route::get('/dashboard/module-relationships/{slug}', [DashboardController::class, 'moduleRelationships'])->name('dashboard.module-relationships');
    Route::get('/dashboard/filterable-fields/{slug}', [DashboardController::class, 'filterableFields'])->name('dashboard.filterable-fields');
    Route::post('/logout', [AuthController::class, 'logout'])->name('logout');
    Route::get('/profile', [UserProfileController::class, 'index'])->name('profile.index');
    Route::put('/profile', [UserProfileController::class, 'update'])->name('profile.update');
    Route::post('/uploads/image', [ImageUploadController::class, 'store'])->name('uploads.image');
    Route::get('/search', SearchController::class)->name('search');
    Route::get('/line-items', [LineItemController::class, 'index'])->name('line-items.index');
    Route::post('/line-items', [LineItemController::class, 'store'])->name('line-items.store');
    Route::post('/line-items/reorder', [LineItemController::class, 'reorder'])->name('line-items.reorder');
    Route::put('/line-items/{lineItem}', [LineItemController::class, 'update'])->name('line-items.update');
    Route::delete('/line-items/{lineItem}', [LineItemController::class, 'destroy'])->name('line-items.destroy');




    // Only For admins
    Route::middleware(['admin'])->group(function () {
        // user routes
        Route::get('/users', [UserController::class, 'index'])->name('users.index');
        Route::get('/users-linking-list', [UserController::class, 'getUsersForLinking'])->name('users.linking');
        Route::get('/users/create', [UserController::class, 'create'])->name('users.create');
        Route::get('/users/invites', [InviteController::class, 'list'])->name('invites.list');
        Route::get('/users/{user_id}', [UserController::class, 'show'])->name('users.show');
        Route::put('/users/{user_id}', [UserController::class, 'update'])->name('users.update');
        Route::post('/users', [UserController::class, 'store'])->name('users.store');
        Route::post('/users/{user}/impersonate', [UserController::class, 'impersonate'])->name('impersonate');
        // invites
        Route::post('/invites', [InviteController::class, 'store']);
        Route::post('/invites/bulk', [InviteController::class, 'bulkStore'])->name('invites.bulk');
        Route::post('/invites/{invite}/resend', [InviteController::class, 'resend'])->name('invites.resend');
        Route::patch('/invites/{invite}/revoke', [InviteController::class, 'revoke'])->name('invites.revoke');
        Route::delete('/invites/{invite}', [InviteController::class, 'destroy'])->name('invites.destroy');

        Route::prefix('settings')->name('settings.')->group(function () {

            // Module manager
            Route::resource('modules', ModuleManagerController::class)
                ->names('modules');

            // Module scoped resources
            Route::prefix('modules/{module}')
                ->group(function () {

                    // Fields
                    Route::get('fields', [FieldsManagerController::class, 'show'])
                        ->name('modules.fields.index');

                    Route::get('fields/create', [FieldsManagerController::class, 'create'])
                        ->name('modules.fields.create');

                    Route::get('fields/{field}', [FieldsManagerController::class, 'edit'])
                        ->name('modules.fields.edit');

                    Route::post('fields/create', [FieldsManagerController::class, 'store'])
                        ->name('modules.fields.store');

                    Route::put('fields/{field}', [FieldsManagerController::class, 'update'])
                        ->name('modules.fields.update');

                    // Route::delete('fields/{field}', [FieldsManagerController::class, 'destroy'])
                    //   ->name('modules.fields.destroy');

                    // Layouts
                    Route::get('layouts', [LayoutManagerController::class, 'show'])
                        ->name('modules.layouts.show');

                    Route::get('layouts/{layoutType}', [LayoutManagerController::class, 'edit'])
                        ->name('modules.layouts.edit');

                    Route::post('layouts/{layoutType}', [LayoutManagerController::class, 'store'])
                        ->name('modules.layouts.store');

                    // Relationships
                    Route::resource('relationships', RelationshipManagerController::class)->names('relationships');
                });

            // moduleBuilder
            Route::get('modulebuilder', [ModuleBuilderController::class, 'create'])
                ->name('modules.builder.create');

            Route::put('modulebuilder/{module}', [ModuleBuilderController::class, 'update'])
                ->name('modules.builder.update');

            Route::post('modulebuilder/{module}/field', [ModuleBuilderController::class, 'saveDraftField'])
                ->name('modules.builder.saveDraftField');

            Route::delete('modulebuilder/{module}/field/{field}', [ModuleBuilderController::class, 'deleteDraftField'])
                ->name('modules.builder.deleteDraftField');
            Route::prefix('modulebuilder/{module}/deploy')->controller(ModuleDeploymentController::class)->group(function () {
                Route::post('/initialize', 'initialize');
                Route::post('/generate-files', 'generateFiles');
                Route::post('/create-labels', 'createLabels');
                Route::post('/activate-fields', 'activateFields');
                Route::post('/create-table', 'createTable');
                Route::post('/rollback', 'rollback');
            });

            // dropdowns
            Route::get('dropdowns', [DropdownListController::class, 'index']);
            Route::get('dropdowns/create', [DropdownListController::class, 'create']);
            Route::post('dropdowns', [DropdownListController::class, 'store']);
            Route::put('dropdowns/{dropdown_id}', [DropdownListController::class, 'update']);
            Route::post('dropdowns_in_fields', [DropdownListController::class, 'storeAndAttach']);
            Route::get('dropdowns/{dropdown_id}', [DropdownListController::class, 'show'])
                ->name('dropdowns.show');

            // PDF Templates
            Route::prefix('pdf-templates')->name('pdf-templates.')->group(function () {
                Route::get('/', [PdfTemplatesController::class, 'index'])->name('index');
                Route::get('/create', [PdfTemplatesController::class, 'create'])->name('create');
                Route::post('/', [PdfTemplatesController::class, 'store'])->name('store');
                Route::post('/preview', [PdfTemplatesController::class, 'preview'])->name('preview');
                Route::get('/{pdfTemplate}', [PdfTemplatesController::class, 'edit'])->name('edit');
                Route::put('/{pdfTemplate}', [PdfTemplatesController::class, 'update'])->name('update');
                Route::delete('/{pdfTemplate}', [PdfTemplatesController::class, 'destroy'])->name('destroy');
                Route::post('/{pdfTemplate}/default', [PdfTemplatesController::class, 'setDefault'])->name('default');
            });
        });

        // System Settings
        Route::get('/settings', [SettingsController::class, 'index'])->name('settings.index');
        Route::put('/settings/{item}', [SettingsController::class, 'update'])->name('settings.update');
        Route::get('/settings/{category}/{item}', [SettingsController::class, 'show'])->name('settings.show');
    });
    Route::post('/leaveimpersonate', [UserController::class, 'leaveImpersonation'])->name('leave-impersonate');
    Route::get('/relatedfield/search/{related_module}', RelatedFieldController::class)->name('records.search');

    // Modules routes
    Route::prefix('{module}/filters')->name('list-filters.')->group(function () {
      Route::post('/', [ListFilterController::class, 'store'])->name('store');
      Route::put('/{filter}', [ListFilterController::class, 'update'])->name('update');
      Route::delete('/{filter}', [ListFilterController::class, 'destroy'])->name('destroy');
    });
    Route::get('{module}/create', [RecordController::class, 'create'])->name('record.create');
    Route::post('{module}', [RecordController::class, 'store'])->name('record.store');
    Route::get('/modules/{module}/{record_id}/relationships/{relationship}/available', [RelationshipLinkController::class, 'getRecordsForLinking'])->name('relationships.available');
    Route::get('/modules/{module}/{record_id}/relationships/{relationship}/single-link', [RelationshipLinkController::class, 'getRecordsForUpdateSingleLinking'])->name('relationships.single-link');
    Route::post('/modules/{module}/{record_id}/relationships/{relationship}', [RelationshipLinkController::class, 'linkRecords'])->name('relationships.link');
    Route::delete('/modules/{module}/{record_id}/relationships/{relationship}/{relatedId}', [RelationshipLinkController::class, 'unlink'])->name('relationships.unlink');
    Route::get('/{module}/{recordId}/pdf', [PdfController::class, 'generate'])->name('modules.record.pdf');
    Route::get('/{module}/{recordId}/export', [ExportController::class, 'export'])->name('modules.record.export');
    Route::post('/{module}/export', [ExportController::class, 'exportMany'])->name('modules.records.exportMany');
    Route::get('/{module}/{recordId}', RecordController::class)->name('modules.record.show');
    Route::put('/{module}/{record}', [RecordController::class, 'update'])->name('modules.records.update');
    Route::delete('/{module}', [RecordController::class, 'destroyMany'])->name('modules.records.destroyMany');
    Route::put('/{module}', [RecordController::class, 'updateMany'])->name('modules.records.updateMany');
    Route::delete('/{module}/{record}', [RecordController::class, 'destroy'])->name('modules.records.destroy');

    Route::get('/{module}', ListController::class)->where('module', '^(?!login$|logout$|profile$).+')->name('modules.index');
});

