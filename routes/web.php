<?php


use Illuminate\Support\Facades\Route;
use App\Http\Controllers\AuthController;
use App\Http\Controllers\HomeController;
use App\Http\Controllers\ContentController;
use App\Http\Controllers\UserController;
use App\Http\Controllers\DepartmentController;
use App\Http\Controllers\WelcomeController;

/*
|--------------------------------------------------------------------------
| Web Routes
|--------------------------------------------------------------------------
|
| Here is where you can register web routes for your application. These
| routes are loaded by the RouteServiceProvider and all of them will
| be assigned to the "web" middleware group. Make something great!
|
*/

// Halaman landing atau sambutan
Route::get('/', [WelcomeController::class, 'index']);



// ===== Autentikasi ===== //
// Form login
Route::get('login', [AuthController::class, 'showLoginForm'])
    ->name('login');
// Proses login
Route::post('login', [AuthController::class, 'login']);
// Logout user (dengan method POST sesuai best practice)
Route::post('logout', [AuthController::class, 'logout'])
    ->name('logout');



// ===== Dashboard Umum ===== //
Route::get('/home', [HomeController::class, 'index'])
    ->name('home');
Route::get('welcome', [WelcomeController::class, 'index'])
    ->name('index');



// ===== Resource Routes CRUD dengan middleware auth ===== //
// Semua route ini hanya bisa diakses oleh user yang sudah login
Route::resource('content', ContentController::class)->middleware('auth');
Route::resource('user', UserController::class)->middleware('auth');
Route::resource('department', DepartmentController::class)->middleware('auth');



// ===== Dashboard Khusus Role ===== //
// Dashboard Superadmin
Route::get('superadmin/dashboard', function () {
    return view('superadmin.dashboard');
})->name('superadmin.dashboard');
// Dashboard Admin
Route::get('admin/dashboard', function () {
    return view('admin.dashboard');
})->name('admin.dashboard');


// Dashboard konten — hanya auth
Route::get('/content', [ContentController::class, 'index'])->name('content.index')->middleware('auth');
Route::get('/content/create', [ContentController::class, 'create'])->name('content.create')->middleware('auth');
Route::post('/content', [ContentController::class, 'store'])->name('content.store')->middleware('auth');



// Show, Edit, Update, Delete konten — hanya auth
Route::get('/content/{uuid}', action: [ContentController::class, 'show'])
    ->name('content.show')
    ->middleware('auth');



Route::get('/content/file/{uuid}', [ContentController::class, 'serveFile'])
    ->name('content.serve.file')
    ->middleware('auth');



Route::get('/content/{uuid}/edit', [ContentController::class, 'edit'])
    ->name('content.edit')
    ->middleware('auth');
Route::put('/content/{uuid}', [ContentController::class, 'update'])
    ->name('content.update')
    ->middleware('auth');
Route::delete('/content/{id}', [ContentController::class, 'destroy'])
    ->name('content.destroy')
    ->middleware('auth');
Route::post('/content/{content}/request-tayang', [ContentController::class, 'requestTayang'])
    ->name('content.requestTayang');
Route::post('/content/{content}/cancel-tayang', [ContentController::class, 'cancelTayang'])
    ->name('content.cancelTayang');


// =====URL untuk User (Show dan Edit) ===== //
Route::get('/user', [UserController::class, 'index'])
    ->name('user.index');
Route::get('/user/show/{uuid}', [UserController::class, 'show'])
    ->name('user.show');
Route::get('/user/edit/{uuid}', [UserController::class, 'edit'])
    ->name('user.edit');
Route::delete('/user/{uuid}', [UserController::class, 'destroy'])
    ->name('user.destroy');

Route::resource('department', DepartmentController::class)
    ->parameters([
        'department' => 'uuid',
    ]);



Route::get('/content-list-only', [ContentController::class, 'listOnly']);





