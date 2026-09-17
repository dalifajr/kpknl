<?php

use Illuminate\Support\Facades\Route;
use App\Http\Controllers\Auth\AuthController;

Route::get('/', function () {
    if (auth()->check()) {
        return redirect()->route('dashboard');
    }
    return view('welcome');
})->name('login');

Route::get('/auth/redirect', [AuthController::class, 'redirect'])->name('auth.redirect');
Route::get('/auth/callback', [AuthController::class, 'callback'])->name('auth.callback');
Route::post('/logout', [AuthController::class, 'logout'])->name('logout');

use App\Http\Controllers\TaskController;
use App\Http\Controllers\UserTaskController;

Route::middleware(['auth'])->group(function () {
    Route::get('/dashboard', [\App\Http\Controllers\DashboardController::class, 'index'])->name('dashboard');

    Route::get('/calendar', [\App\Http\Controllers\CalendarController::class, 'index'])->name('calendar.index');
    Route::get('/calendar/events', [\App\Http\Controllers\CalendarController::class, 'getEvents'])->name('calendar.events');

    Route::get('/user-tasks', [UserTaskController::class, 'index'])->name('user-tasks.index');
    Route::get('/user-tasks/{assignment}', [UserTaskController::class, 'show'])->name('user-tasks.show');
    Route::post('/user-tasks/{assignment}/submit', [UserTaskController::class, 'submit'])->name('user-tasks.submit');
    Route::delete('/user-tasks/{assignment}/draft', [UserTaskController::class, 'deleteDraft'])->name('user-tasks.delete-draft');
    Route::delete('/user-tasks/{assignment}', [UserTaskController::class, 'destroy'])->name('user-tasks.destroy');
    Route::post('/user-tasks/{assignment}/comment', [UserTaskController::class, 'addComment'])->name('user-tasks.comment');

    Route::get('/notifications', [\App\Http\Controllers\MonlapNotificationController::class, 'index'])->name('notifications.index');
    Route::get('/notifications/{notification}/read', [\App\Http\Controllers\MonlapNotificationController::class, 'read'])->name('notifications.read');
    Route::post('/notifications/mark-all-read', [\App\Http\Controllers\MonlapNotificationController::class, 'markAllRead'])->name('notifications.mark-all-read');

    Route::middleware(['role:superadmin,admin'])->group(function () {
        Route::post('tasks/preview-deadline', [TaskController::class, 'previewDeadline'])->name('tasks.preview-deadline');
        Route::post('tasks/bulk-assign', [TaskController::class, 'bulkAssign'])->name('tasks.bulk-assign');
        Route::post('tasks/bulk-action', [TaskController::class, 'bulkAction'])->name('tasks.bulk-action');
        Route::resource('tasks', TaskController::class);
        Route::post('tasks/{task}/toggle', [TaskController::class, 'toggleActive'])->name('tasks.toggle-active');

        Route::get('/reviews', [\App\Http\Controllers\ReviewController::class, 'index'])->name('reviews.index');
        Route::get('/reviews/{assignment}', [\App\Http\Controllers\ReviewController::class, 'show'])->name('reviews.show');
        Route::post('/reviews/{assignment}/process', [\App\Http\Controllers\ReviewController::class, 'process'])->name('reviews.process');

        Route::post('/agendas', [\App\Http\Controllers\AgendaController::class, 'store'])->name('agendas.store');
        Route::put('/agendas/{agenda}', [\App\Http\Controllers\AgendaController::class, 'update'])->name('agendas.update');
        Route::delete('/agendas/{agenda}', [\App\Http\Controllers\AgendaController::class, 'destroy'])->name('agendas.destroy');
    });
});
