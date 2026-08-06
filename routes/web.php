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
use App\Http\Controllers\OnboardingController;
use App\Http\Controllers\PdfController;
use App\Http\Controllers\PreferencesController;
use App\Http\Controllers\RecordController;
use App\Http\Controllers\TransformationRunController;
use App\Http\Controllers\TransformationsManagerController;
use App\Http\Controllers\RelatedFieldController;
use App\Http\Controllers\RelationshipLinkController;
use App\Http\Controllers\RelationshipManagerController;
use App\Http\Controllers\SearchController;
use App\Http\Controllers\SettingsController;
use App\Http\Controllers\SetupController;
use App\Http\Controllers\UserController;
use App\Http\Controllers\UserProfileController;
use App\Http\Controllers\LineItemController;
use App\Http\Controllers\MeetingAttendeeController;
use App\Http\Controllers\NotificationController;
use App\Http\Controllers\PdfTemplatesController;
use App\Http\Controllers\ExportController;
use App\Http\Controllers\ImportController;
use App\Http\Controllers\ImageUploadController;
use App\Http\Controllers\SessionController;
use App\Http\Controllers\AuditLogController;
use App\Http\Controllers\ImpersonationSessionController;
use App\Http\Controllers\RecordHistoryController;
use App\Http\Controllers\RecordTimelineController;
use App\Http\Controllers\EmailCaptureAddressController;
use App\Http\Controllers\ApiTokenController;


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
    Route::get('/onboarding', [OnboardingController::class, 'show'])->name('onboarding.show');
    Route::post('/onboarding/demo-data', [OnboardingController::class, 'seedDemoData'])->name('onboarding.demo-data');
    Route::post('/onboarding/finish', [OnboardingController::class, 'finish'])->name('onboarding.finish');
    Route::post('/uploads/image', [ImageUploadController::class, 'store'])->name('uploads.image');
    Route::get('/keep-alive', [SessionController::class, 'keepAlive'])->name('keep-alive');
});

Route::middleware(['auth', 'onboarded'])->group(function () {

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
    Route::get('/preferences', [PreferencesController::class, 'index'])->name('preferences.index');
    Route::put('/preferences', [PreferencesController::class, 'update'])->name('preferences.update');
    Route::get('/notifications', [NotificationController::class, 'index'])->name('notifications.index');
    Route::get('/notifications/unread-count', [NotificationController::class, 'unreadCount'])->name('notifications.unread-count');
    Route::post('/notifications/{id}/read', [NotificationController::class, 'markRead'])->name('notifications.read');
    Route::post('/notifications/read-all', [NotificationController::class, 'markAllRead'])->name('notifications.read-all');
    Route::get('/search', SearchController::class)->name('search');
    Route::get('/line-items', [LineItemController::class, 'index'])->name('line-items.index');
    Route::post('/line-items', [LineItemController::class, 'store'])->name('line-items.store');
    Route::post('/line-items/reorder', [LineItemController::class, 'reorder'])->name('line-items.reorder');
    Route::put('/line-items/{lineItem}', [LineItemController::class, 'update'])->name('line-items.update');
    Route::delete('/line-items/{lineItem}', [LineItemController::class, 'destroy'])->name('line-items.destroy');

    Route::get('/meeting-attendees', [MeetingAttendeeController::class, 'index'])->name('meeting-attendees.index');
    Route::post('/meeting-attendees', [MeetingAttendeeController::class, 'store'])->name('meeting-attendees.store');
    Route::post('/meeting-attendees/mark-all-attended', [MeetingAttendeeController::class, 'markAllAttended'])->name('meeting-attendees.mark-all-attended');
    Route::put('/meeting-attendees/{meetingAttendee}', [MeetingAttendeeController::class, 'update'])->name('meeting-attendees.update');
    Route::delete('/meeting-attendees/{meetingAttendee}', [MeetingAttendeeController::class, 'destroy'])->name('meeting-attendees.destroy');




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
        Route::post('/users/{user}/reset-password', [UserController::class, 'sendPasswordResetEmail'])->name('users.reset-password');
        Route::post('/users/{user}/send-set-password', [UserController::class, 'sendSetPasswordEmail'])->name('users.send-set-password');
        // invites
        Route::post('/invites', [InviteController::class, 'store']);
        Route::post('/invites/bulk', [InviteController::class, 'bulkStore'])->name('invites.bulk');
        Route::post('/invites/{invite}/resend', [InviteController::class, 'resend'])->name('invites.resend');
        Route::patch('/invites/{invite}/revoke', [InviteController::class, 'revoke'])->name('invites.revoke');
        Route::delete('/invites/{invite}', [InviteController::class, 'destroy'])->name('invites.destroy');

        Route::prefix('settings')->name('settings.')->group(function () {

            // Module manager
            Route::resource('modules', ModuleManagerController::class)
                ->except(['show', 'edit'])
                ->names('modules');

            Route::get('modules/{module}', [ModuleManagerController::class, 'redirectToSettings'])
                ->name('modules.show');

            // Module scoped resources
            Route::prefix('modules/{module}')
                ->group(function () {

                    Route::get('module-settings', [ModuleManagerController::class, 'show'])
                        ->name('modules.module-settings');

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

                    Route::delete('fields/{field}', [FieldsManagerController::class, 'destroy'])
                        ->name('modules.fields.destroy');

                    // Layouts
                    Route::get('layouts', [LayoutManagerController::class, 'show'])
                        ->name('modules.layouts.show');

                    Route::get('layouts/{layoutType}', [LayoutManagerController::class, 'edit'])
                        ->name('modules.layouts.edit');

                    Route::post('layouts/{layoutType}', [LayoutManagerController::class, 'store'])
                        ->name('modules.layouts.store');

                    // Relationships
                    Route::resource('relationships', RelationshipManagerController::class)
                        ->only(['index', 'create', 'store', 'destroy'])
                        ->names('relationships');
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

            Route::delete('modulebuilder/{module}/discard', [ModuleBuilderController::class, 'discard'])
                ->name('modules.builder.discard');
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

            // Transformations (Studio > Automation > Transformations)
            Route::prefix('transformations')->name('transformations.')->group(function () {
                Route::get('/', [TransformationsManagerController::class, 'index'])->name('index');
                Route::get('/create', [TransformationsManagerController::class, 'create'])->name('create');
                Route::post('/', [TransformationsManagerController::class, 'store'])->name('store');
                Route::get('/{transformation}', [TransformationsManagerController::class, 'edit'])->name('edit');
                Route::put('/{transformation}', [TransformationsManagerController::class, 'update'])->name('update');
                Route::patch('/{transformation}/toggle', [TransformationsManagerController::class, 'toggle'])->name('toggle');
                Route::delete('/{transformation}', [TransformationsManagerController::class, 'destroy'])->name('destroy');
                Route::post('/expressions/validate', [TransformationsManagerController::class, 'validateExpression'])->name('validate-expression');
            });

            // Email capture addresses (admin-created inboxes for the
            // Emails module, e.g. leads@{tenant} — see EmailInboundWebhookController)
            Route::prefix('email-capture-addresses')->name('email-capture-addresses.')->group(function () {
                Route::get('/', [EmailCaptureAddressController::class, 'index'])->name('index');
                Route::post('/', [EmailCaptureAddressController::class, 'store'])->name('store');
                Route::delete('/{emailCaptureAddress}', [EmailCaptureAddressController::class, 'destroy'])->name('destroy');
            });

            // Audit Trail
            Route::prefix('audit-trail')->name('audit-trail.')->group(function () {
                Route::get('/', [AuditLogController::class, 'index'])->name('index');
                Route::get('/{auditLog}/affected-records', [AuditLogController::class, 'affectedRecords'])->name('affected-records');
            });

            // Impersonation Sessions
            Route::prefix('impersonation-sessions')->name('impersonation-sessions.')->group(function () {
                Route::get('/', [ImpersonationSessionController::class, 'index'])->name('index');
            });

            // REST API tokens
            Route::prefix('api-tokens')->name('api-tokens.')->group(function () {
                Route::get('/', [ApiTokenController::class, 'index'])->name('index');
                Route::get('/create', [ApiTokenController::class, 'create'])->name('create');
                Route::post('/', [ApiTokenController::class, 'store'])->name('store');
                Route::get('/{token}', [ApiTokenController::class, 'show'])->name('show');
                Route::delete('/{token}', [ApiTokenController::class, 'destroy'])->name('destroy');
            });
        });

        // System Settings
        Route::get('/settings', [SettingsController::class, 'index'])->name('settings.index');
        Route::put('/settings/{item}', [SettingsController::class, 'update'])->name('settings.update');
        Route::get('/settings/system/notifications', [SettingsController::class, 'notifications'])->name('settings.notifications');
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
    Route::get('/modules/{module}/{recordId}/history', [RecordHistoryController::class, 'index'])->name('modules.record.history');
    Route::get('/modules/{module}/{recordId}/timeline', [RecordTimelineController::class, 'index'])->name('modules.record.timeline');
    Route::get('/modules/{module}/{record_id}/relationships/{relationship}/single-link', [RelationshipLinkController::class, 'getRecordsForUpdateSingleLinking'])->name('relationships.single-link');
    Route::post('/modules/{module}/{record_id}/relationships/{relationship}', [RelationshipLinkController::class, 'linkRecords'])->name('relationships.link');
    Route::delete('/modules/{module}/{record_id}/relationships/{relationship}/{relatedId}', [RelationshipLinkController::class, 'unlink'])->name('relationships.unlink');
    Route::get('/modules/{module}/{recordId}/transformations', [TransformationRunController::class, 'available'])->name('transformations.available');
    Route::get('/transformations/{transformation}/{recordId}/preview', [TransformationRunController::class, 'preview'])->name('transformations.preview');
    Route::post('/transformations/{transformation}/{recordId}/run', [TransformationRunController::class, 'run'])->name('transformations.run');
    Route::get('/{module}/{recordId}/pdf', [PdfController::class, 'generate'])->name('modules.record.pdf');
    Route::get('/{module}/{recordId}/export', [ExportController::class, 'export'])->name('modules.record.export');
    Route::post('/{module}/export', [ExportController::class, 'exportMany'])->name('modules.records.exportMany');
    Route::post('/{module}/import/preview', [ImportController::class, 'preview'])->name('modules.import.preview');
    Route::post('/{module}/import/{import}/start', [ImportController::class, 'start'])->name('modules.import.start');
    Route::get('/{module}/import/{import}/status', [ImportController::class, 'status'])->name('modules.import.status');
    Route::get('/{module}/{recordId}', RecordController::class)->name('modules.record.show');
    Route::put('/{module}/{record}', [RecordController::class, 'update'])->name('modules.records.update');
    Route::delete('/{module}', [RecordController::class, 'destroyMany'])->name('modules.records.destroyMany');
    Route::put('/{module}', [RecordController::class, 'updateMany'])->name('modules.records.updateMany');
    Route::delete('/{module}/{record}', [RecordController::class, 'destroy'])->name('modules.records.destroy');

    Route::get('/{module}', ListController::class)->where('module', '^(?!login$|logout$|profile$|preferences$).+')->name('modules.index');
});

