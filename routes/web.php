<?php

use Illuminate\Support\Facades\Route;
use App\Http\Controllers\DashboardController;
use App\Http\Controllers\MemberController;
use App\Http\Controllers\MembershipController;
use App\Http\Controllers\MembershipPlanController;
use App\Http\Controllers\AttendanceController;
use App\Http\Controllers\PaymentController;
use App\Http\Controllers\InvoiceController;
use App\Http\Controllers\InventoryItemController;
use App\Http\Controllers\ExpenseController;
use App\Http\Controllers\TrainerController;
use App\Http\Controllers\ClassController;
use App\Http\Controllers\EquipmentController;
use App\Http\Controllers\ReportController;
use App\Http\Controllers\RoleController;
use App\Http\Controllers\UserController;

// Guest routes
Route::get('/', function () {
    if (auth()->check()) {
        return redirect('/admin/dashboard');
    }
    return redirect()->route('login');
});

// Authentication routes
Route::middleware('guest')->group(function () {
    Route::get('/login', function () {
        return view('auth.login');
    })->name('login');
    
    Route::post('/login', [\App\Http\Controllers\Auth\AuthController::class, 'login']);
});

Route::post('/logout', [\App\Http\Controllers\Auth\AuthController::class, 'logout'])->name('logout');

// Authenticated routes
Route::middleware(['auth'])->prefix('admin')->group(function () {
    
    // Stub routes for frontend API calls
    Route::get('/translations', function () { return response()->json([]); });
    Route::put('/translations/switch-language', function () { 
        return response()->json(['success' => true, 'data' => []]); 
    });
    Route::get('/currency-types/search', function () { return response()->json([]); });
    
    // Dashboard
    Route::get('/dashboard', [DashboardController::class, 'index'])->name('dashboard');
    Route::get('/dashboard/data', [DashboardController::class, 'data']);

    // Members
    Route::prefix('members')->name('members.')->group(function () {
        Route::get('/', [MemberController::class, 'index'])->name('index');
        Route::get('/data', [MemberController::class, 'data'])->name('data');
        Route::get('/create', [MemberController::class, 'create'])->name('create');
        Route::post('/', [MemberController::class, 'store'])->name('store');
        Route::get('/{id}', [MemberController::class, 'show'])->name('show');
        Route::get('/{id}/edit', [MemberController::class, 'edit'])->name('edit');
        Route::put('/{id}', [MemberController::class, 'update'])->name('update');
        Route::delete('/{id}', [MemberController::class, 'destroy'])->name('destroy');
        Route::get('/stats/overview', [MemberController::class, 'stats'])->name('stats');
    });

    // Memberships
    Route::prefix('memberships')->name('memberships.')->group(function () {
        Route::get('/', [MembershipController::class, 'index'])->name('index');
        Route::get('/data', [MembershipController::class, 'data'])->name('data');
        Route::get('/create', [MembershipController::class, 'create'])->name('create');
        Route::post('/', [MembershipController::class, 'store'])->name('store');
        Route::get('/{id}', [MembershipController::class, 'show'])->name('show');
        Route::put('/{id}', [MembershipController::class, 'update'])->name('update');
        Route::post('/{id}/cancel', [MembershipController::class, 'cancel'])->name('cancel');
        Route::post('/{id}/renew', [MembershipController::class, 'renew'])->name('renew');
    });

    // Membership Plans
    Route::prefix('membership-plans')->name('membership-plans.')->group(function () {
        Route::get('/', [MembershipPlanController::class, 'index'])->name('index');
        Route::get('/data', [MembershipPlanController::class, 'data'])->name('data');
        Route::get('/active', [MembershipPlanController::class, 'active'])->name('active');
        Route::post('/', [MembershipPlanController::class, 'store'])->name('store');
        Route::get('/{id}', [MembershipPlanController::class, 'show'])->name('show');
        Route::put('/{id}', [MembershipPlanController::class, 'update'])->name('update');
        Route::delete('/{id}', [MembershipPlanController::class, 'destroy'])->name('destroy');
    });

    // Attendance
    Route::prefix('attendance')->name('attendance.')->group(function () {
        Route::get('/', [AttendanceController::class, 'index'])->name('index');
        Route::get('/data', [AttendanceController::class, 'data'])->name('data');
        Route::post('/check-in', [AttendanceController::class, 'checkIn'])->name('checkin');
        Route::post('/check-out', [AttendanceController::class, 'checkOut'])->name('checkout');
        Route::get('/member/{memberId}', [AttendanceController::class, 'memberHistory'])->name('member.history');
        Route::get('/stats/today', [AttendanceController::class, 'todayStats'])->name('stats.today');
    });

    // Payments
    Route::prefix('payments')->name('payments.')->group(function () {
        Route::get('/', [PaymentController::class, 'index'])->name('index');
        Route::get('/data', [PaymentController::class, 'data'])->name('data');
        Route::post('/', [PaymentController::class, 'store'])->name('store');
        Route::get('/{id}', [PaymentController::class, 'show'])->name('show');
    });

    // Invoices
    Route::prefix('invoices')->name('invoices.')->group(function () {
        Route::get('/', [InvoiceController::class, 'index'])->name('index');
        Route::get('/data', [InvoiceController::class, 'data'])->name('data');
        Route::get('/{id}', [InvoiceController::class, 'show'])->name('show');
        Route::get('/{id}/print', [InvoiceController::class, 'print'])->name('print');
    });

    // Inventory (Market)
    Route::prefix('inventory')->name('inventory.')->group(function () {
        Route::get('/', [InventoryItemController::class, 'index'])->name('index');
        Route::get('/data', [InventoryItemController::class, 'data'])->name('data');
        Route::post('/', [InventoryItemController::class, 'store'])->name('store');
        Route::put('/{id}', [InventoryItemController::class, 'update'])->name('update');
        Route::delete('/{id}', [InventoryItemController::class, 'destroy'])->name('destroy');
    });

    // Expenses
    Route::prefix('expenses')->name('expenses.')->group(function () {
        Route::get('/', [ExpenseController::class, 'index'])->name('index');
        Route::get('/data', [ExpenseController::class, 'data'])->name('data');
        Route::post('/', [ExpenseController::class, 'store'])->name('store');
        Route::get('/{id}', [ExpenseController::class, 'show'])->name('show');
        Route::put('/{id}', [ExpenseController::class, 'update'])->name('update');
        Route::delete('/{id}', [ExpenseController::class, 'destroy'])->name('destroy');
    });

    // Trainers
    Route::prefix('trainers')->name('trainers.')->group(function () {
        Route::get('/', [TrainerController::class, 'index'])->name('index');
        Route::get('/data', [TrainerController::class, 'data'])->name('data');
        Route::post('/', [TrainerController::class, 'store'])->name('store');
        Route::get('/{id}', [TrainerController::class, 'show'])->name('show');
        Route::put('/{id}', [TrainerController::class, 'update'])->name('update');
        Route::delete('/{id}', [TrainerController::class, 'destroy'])->name('destroy');
    });

    // Classes
    Route::prefix('classes')->name('classes.')->group(function () {
        Route::get('/', [ClassController::class, 'index'])->name('index');
        Route::get('/data', [ClassController::class, 'data'])->name('data');
        Route::post('/', [ClassController::class, 'store'])->name('store');
        Route::put('/{id}', [ClassController::class, 'update'])->name('update');
        Route::delete('/{id}', [ClassController::class, 'destroy'])->name('destroy');
    });

    // Equipment
    Route::prefix('equipment')->name('equipment.')->group(function () {
        Route::get('/', [EquipmentController::class, 'index'])->name('index');
        Route::get('/data', [EquipmentController::class, 'data'])->name('data');
        Route::post('/', [EquipmentController::class, 'store'])->name('store');
        Route::put('/{id}', [EquipmentController::class, 'update'])->name('update');
        Route::delete('/{id}', [EquipmentController::class, 'destroy'])->name('destroy');
    });

    // Reports
    Route::prefix('reports')->name('reports.')->group(function () {
        Route::get('/revenue', [ReportController::class, 'revenue'])->name('revenue');
        Route::get('/members', [ReportController::class, 'members'])->name('members');
        Route::get('/attendance', [ReportController::class, 'attendance'])->name('attendance');
        Route::get('/expenses', [ReportController::class, 'expenses'])->name('expenses');
    });

    // Roles & Permissions
    Route::prefix('roles')->name('roles.')->group(function () {
        Route::get('/', [RoleController::class, 'index'])->name('index');
        Route::get('/data', [RoleController::class, 'data'])->name('data');
        Route::post('/', [RoleController::class, 'store'])->name('store');
        Route::put('/{id}', [RoleController::class, 'update'])->name('update');
        Route::delete('/{id}', [RoleController::class, 'destroy'])->name('destroy');
    });

    // Users
    Route::prefix('users')->name('users.')->group(function () {
        Route::get('/', [UserController::class, 'index'])->name('index');
        Route::get('/data', [UserController::class, 'data'])->name('data');
        Route::post('/', [UserController::class, 'store'])->name('store');
        Route::put('/{id}', [UserController::class, 'update'])->name('update');
        Route::delete('/{id}', [UserController::class, 'destroy'])->name('destroy');
    });
});

