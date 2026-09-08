<?php

use App\Http\Controllers\Api\V1\AuthController;
use App\Http\Controllers\Api\V1\ChildController;
use App\Http\Controllers\Api\V1\ConfigController;
use App\Http\Controllers\Api\V1\ContentController;
use App\Http\Controllers\Api\V1\DeviceCodeController;
use App\Http\Controllers\Api\V1\ParentController;
use App\Http\Controllers\Api\V1\SyncController;
use Illuminate\Support\Facades\Route;

/*
|--------------------------------------------------------------------------
| API v1 — the Flutter app (phone, tablet, Android TV)
|--------------------------------------------------------------------------
| Mounted at /api/v1 (see bootstrap/app.php). Stateless: no session, no CSRF.
| Guardians authenticate with a Sanctum token; the child being played is named
| per request by the X-Child-Id header and checked against ownership.
|
| Contract: KIDDOQUEST_FLUTTER_APP_PLAN.md §7.1.
*/

// ── Public ───────────────────────────────────────────────────────────────────

Route::get('/config', [ConfigController::class, 'show'])->name('api.config');

Route::middleware('throttle:20,1')->group(function () {
    Route::post('/auth/parent/register', [AuthController::class, 'register'])->name('api.auth.register');
    Route::post('/auth/parent/login', [AuthController::class, 'login'])->name('api.auth.login');

    // Television sign-in: the TV asks for a code and polls; the phone approves.
    Route::post('/auth/device/code', [DeviceCodeController::class, 'create'])->name('api.auth.device.code');
    Route::get('/auth/device/poll', [DeviceCodeController::class, 'poll'])->name('api.auth.device.poll');
});

/*
| Safaricom's callback. It lived in routes/web.php, where the CSRF middleware
| would have rejected every POST Daraja ever sent; here there is no session and
| no token to miss.
*/
Route::post('/mpesa/callback', [App\Http\Controllers\Parent\SubscriptionController::class, 'handleCallback'])
    ->name('api.mpesa.callback');

// ── Signed in as a parent ────────────────────────────────────────────────────

Route::middleware(['auth:sanctum', 'api.device'])->group(function () {

    Route::get('/auth/me', [AuthController::class, 'me'])->name('api.auth.me');
    Route::post('/auth/refresh', [AuthController::class, 'refresh'])->name('api.auth.refresh');
    Route::post('/auth/logout', [AuthController::class, 'logout'])->name('api.auth.logout');
    Route::post('/auth/logout-all', [AuthController::class, 'logoutAll'])->name('api.auth.logout_all');
    Route::post('/auth/device/approve', [DeviceCodeController::class, 'approve'])->name('api.auth.device.approve');

    // Children
    Route::get('/children', [ChildController::class, 'index'])->name('api.children.index');
    Route::post('/children', [ChildController::class, 'store'])->name('api.children.store');
    Route::get('/children/{child}', [ChildController::class, 'show'])->whereNumber('child')->name('api.children.show');
    Route::patch('/children/{child}', [ChildController::class, 'update'])->whereNumber('child')->name('api.children.update');
    Route::delete('/children/{child}', [ChildController::class, 'destroy'])->whereNumber('child')->name('api.children.destroy');
    Route::get('/children/{child}/snapshot', [ChildController::class, 'snapshot'])->whereNumber('child')->name('api.children.snapshot');
    Route::patch('/children/{child}/screen-time', [ChildController::class, 'screenTime'])->whereNumber('child')->name('api.children.screen_time');
    Route::patch('/children/{child}/focus-mission', [ChildController::class, 'focusMission'])->whereNumber('child')->name('api.children.focus_mission');

    // Content
    Route::middleware('api.child:optional')->group(function () {
        Route::get('/content/catalog', [ContentController::class, 'catalog'])->name('api.content.catalog');
        Route::get('/content/worlds', [ContentController::class, 'worlds'])->name('api.content.worlds');
    });
    Route::get('/content/packs/{packId}/manifest', [ContentController::class, 'manifest'])->name('api.content.manifest');
    Route::get('/content/packs/{packId}/v{version}/download', [ContentController::class, 'download'])
        ->whereNumber('version')
        ->name('api.content.download');

    // The hot path.
    Route::post('/sync', [SyncController::class, 'store'])
        ->middleware(['api.child', 'throttle:sync'])
        ->name('api.sync');

    // Parent zone
    Route::post('/parent/pin/verify', [ParentController::class, 'verifyPin'])
        ->middleware('throttle:12,1')
        ->name('api.parent.pin.verify');
    Route::patch('/parent/pin', [ParentController::class, 'updatePin'])->name('api.parent.pin.update');
    Route::patch('/parent/settings', [ParentController::class, 'updateSettings'])->name('api.parent.settings');
    Route::get('/parent/children/{child}/report', [ParentController::class, 'report'])
        ->whereNumber('child')
        ->name('api.parent.report');
    Route::post('/devices/push-token', [ParentController::class, 'pushToken'])->name('api.devices.push_token');
});
