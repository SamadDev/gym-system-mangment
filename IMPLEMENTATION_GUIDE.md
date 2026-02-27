# Kurdistan Gym System - Implementation Guide

## Quick Reference: What's Done & What's Next

### ✅ COMPLETED
1. **Database Structure**: All 22 tables created and migrated
2. **Project Organization**: Enums, Services, Helpers, Traits folders ready
3. **Enums**: Gender, MemberStatus, MembershipStatus, PaymentMethod, PaymentType, BloodType
4. **Core Models**: Member (complete example), MembershipPlan, Membership, Attendance, Trainer, Payment, Invoice, InventoryItem, Equipment, Expense
5. **Configuration**: Timezone (Asia/Baghdad), App name, Database ready

### 📝 SAMPLE CODE EXAMPLES

---

## 1. Complete Model Example (MembershipPlan)

```php
<?php
// app/Models/MembershipPlan.php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\HasMany;

class MembershipPlan extends Model
{
    protected $fillable = [
        'name',
        'description',
        'duration_days',
        'price_male',
        'price_female',
        'class_limit',
        'personal_training_included',
        'status',
    ];

    protected $casts = [
        'personal_training_included' => 'boolean',
        'price_male' => 'decimal:2',
        'price_female' => 'decimal:2',
    ];

    // Relationships
    public function memberships(): HasMany
    {
        return $this->hasMany(Membership::class);
    }

    // Scopes
    public function scopeActive($query)
    {
        return $query->where('status', 'active');
    }

    // Methods
    public function getPriceForGender(string $gender): float
    {
        return $gender === 'male' ? $this->price_male : $this->price_female;
    }

    public function getDurationInMonths(): int
    {
        return round($this->duration_days / 30);
    }
}
```

---

## 2. Membership Model with Relationships

```php
<?php
// app/Models/Membership.php

namespace App\Models;

use Carbon\Carbon;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class Membership extends Model
{
    protected $fillable = [
        'member_id',
        'membership_plan_id',
        'start_date',
        'end_date',
        'amount_paid',
        'status',
    ];

    protected $casts = [
        'start_date' => 'date',
        'end_date' => 'date',
        'amount_paid' => 'decimal:2',
    ];

    // Relationships
    public function member(): BelongsTo
    {
        return $this->belongsTo(Member::class);
    }

    public function membershipPlan(): BelongsTo
    {
        return $this->belongsTo(MembershipPlan::class);
    }

    public function payment()
    {
        return $this->hasOne(Payment::class);
    }

    // Scopes
    public function scopeActive($query)
    {
        return $query->where('status', 'active');
    }

    public function scopeExpired($query)
    {
        return $query->where('status', 'expired');
    }

    public function scopeExpiringSoon($query, $days = 30)
    {
        return $query->where('status', 'active')
            ->whereBetween('end_date', [now(), now()->addDays($days)]);
    }

    // Methods
    public function getDaysRemainingAttribute(): int
    {
        if ($this->status === 'expired' || $this->end_date->isPast()) {
            return 0;
        }
        return now()->diffInDays($this->end_date);
    }

    public function isExpired(): bool
    {
        return $this->end_date->isPast();
    }
}
```

---

## 3. Complete Role Seeder

```php
<?php
// database/seeders/RoleSeeder.php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\DB;

class RoleSeeder extends Seeder
{
    public function run(): void
    {
        $roles = [
            [
                'name' => 'Admin',
                'description' => 'Full system access',
                'permissions' => json_encode([
                    'members.*', 'memberships.*', 'attendance.*',
                    'payments_subscription.*', 'payments_market.*',
                    'trainers.*', 'classes.*', 'equipment.*',
                    'expenses.*', 'inventory.*', 'market_sales.*',
                    'reports.*', 'settings.*', 'users.*', 'roles.*'
                ]),
                'status' => 'active',
                'created_at' => now(),
                'updated_at' => now(),
            ],
            [
                'name' => 'Manager',
                'description' => 'Full access except user/role management',
                'permissions' => json_encode([
                    'members.*', 'memberships.*', 'attendance.*',
                    'payments_subscription.*', 'payments_market.*',
                    'trainers.*', 'classes.*', 'equipment.*',
                    'expenses.view', 'expenses.approve', 'inventory.*',
                    'market_sales.*', 'reports.*', 'settings.manage'
                ]),
                'status' => 'active',
                'created_at' => now(),
                'updated_at' => now(),
            ],
            [
                'name' => 'Receptionist',
                'description' => 'Member check-in and basic operations',
                'permissions' => json_encode([
                    'members.view', 'members.create', 'members.edit',
                    'memberships.view', 'memberships.create',
                    'attendance.view', 'attendance.checkin', 'attendance.checkout',
                    'payments_subscription.view', 'payments_subscription.create',
                    'reports.view_subscription'
                ]),
                'status' => 'active',
                'created_at' => now(),
                'updated_at' => now(),
            ],
            [
                'name' => 'Market Staff',
                'description' => 'Inventory and market sales only',
                'permissions' => json_encode([
                    'inventory.view', 'inventory.create', 'inventory.edit',
                    'market_sales.sell', 'payments_market.view',
                    'reports.view_market'
                ]),
                'status' => 'active',
                'created_at' => now(),
                'updated_at' => now(),
            ],
            [
                'name' => 'Trainer',
                'description' => 'View classes and training sessions',
                'permissions' => json_encode([
                    'classes.view', 'personal_training.view',
                    'members.view'
                ]),
                'status' => 'active',
                'created_at' => now(),
                'updated_at' => now(),
            ],
        ];

        DB::table('roles')->insert($roles);
    }
}
```

---

## 4. Permission Seeder

```php
<?php
// database/seeders/PermissionSeeder.php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\DB;

class PermissionSeeder extends Seeder
{
    public function run(): void
    {
        $permissions = [
            // Members
            ['name' => 'members.view', 'module' => 'members', 'description' => 'View members'],
            ['name' => 'members.create', 'module' => 'members', 'description' => 'Create new members'],
            ['name' => 'members.edit', 'module' => 'members', 'description' => 'Edit members'],
            ['name' => 'members.delete', 'module' => 'members', 'description' => 'Delete members'],

            // Memberships
            ['name' => 'memberships.view', 'module' => 'memberships', 'description' => 'View memberships'],
            ['name' => 'memberships.create', 'module' => 'memberships', 'description' => 'Create memberships'],
            ['name' => 'memberships.edit', 'module' => 'memberships', 'description' => 'Edit memberships'],
            ['name' => 'memberships.delete', 'module' => 'memberships', 'description' => 'Delete memberships'],
            ['name' => 'memberships.override_price', 'module' => 'memberships', 'description' => 'Override membership price'],

            // Attendance
            ['name' => 'attendance.view', 'module' => 'attendance', 'description' => 'View attendance'],
            ['name' => 'attendance.checkin', 'module' => 'attendance', 'description' => 'Check-in members'],
            ['name' => 'attendance.checkout', 'module' => 'attendance', 'description' => 'Check-out members'],

            // Payments - Subscription
            ['name' => 'payments_subscription.view', 'module' => 'payments', 'description' => 'View subscription payments'],
            ['name' => 'payments_subscription.create', 'module' => 'payments', 'description' => 'Create subscription payments'],
            ['name' => 'payments_subscription.refund', 'module' => 'payments', 'description' => 'Refund subscription payments'],

            // Payments - Market
            ['name' => 'payments_market.view', 'module' => 'payments', 'description' => 'View market payments'],
            ['name' => 'payments_market.create', 'module' => 'payments', 'description' => 'Create market payments'],
            ['name' => 'payments_market.refund', 'module' => 'payments', 'description' => 'Refund market payments'],

            // Trainers
            ['name' => 'trainers.view', 'module' => 'trainers', 'description' => 'View trainers'],
            ['name' => 'trainers.create', 'module' => 'trainers', 'description' => 'Create trainers'],
            ['name' => 'trainers.edit', 'module' => 'trainers', 'description' => 'Edit trainers'],
            ['name' => 'trainers.delete', 'module' => 'trainers', 'description' => 'Delete trainers'],

            // Classes
            ['name' => 'classes.view', 'module' => 'classes', 'description' => 'View classes'],
            ['name' => 'classes.create', 'module' => 'classes', 'description' => 'Create classes'],
            ['name' => 'classes.edit', 'module' => 'classes', 'description' => 'Edit classes'],
            ['name' => 'classes.delete', 'module' => 'classes', 'description' => 'Delete classes'],

            // Equipment
            ['name' => 'equipment.view', 'module' => 'equipment', 'description' => 'View equipment'],
            ['name' => 'equipment.create', 'module' => 'equipment', 'description' => 'Create equipment'],
            ['name' => 'equipment.edit', 'module' => 'equipment', 'description' => 'Edit equipment'],
            ['name' => 'equipment.delete', 'module' => 'equipment', 'description' => 'Delete equipment'],
            ['name' => 'equipment.maintenance', 'module' => 'equipment', 'description' => 'Log maintenance'],

            // Expenses
            ['name' => 'expenses.view', 'module' => 'expenses', 'description' => 'View expenses'],
            ['name' => 'expenses.create', 'module' => 'expenses', 'description' => 'Create expenses'],
            ['name' => 'expenses.edit', 'module' => 'expenses', 'description' => 'Edit expenses'],
            ['name' => 'expenses.delete', 'module' => 'expenses', 'description' => 'Delete expenses'],
            ['name' => 'expenses.approve', 'module' => 'expenses', 'description' => 'Approve expenses'],

            // Inventory
            ['name' => 'inventory.view', 'module' => 'inventory', 'description' => 'View inventory'],
            ['name' => 'inventory.create', 'module' => 'inventory', 'description' => 'Create inventory items'],
            ['name' => 'inventory.edit', 'module' => 'inventory', 'description' => 'Edit inventory items'],
            ['name' => 'inventory.delete', 'module' => 'inventory', 'description' => 'Delete inventory items'],
            ['name' => 'inventory.adjust', 'module' => 'inventory', 'description' => 'Adjust stock levels'],

            // Market Sales
            ['name' => 'market_sales.view', 'module' => 'market', 'description' => 'View market sales'],
            ['name' => 'market_sales.sell', 'module' => 'market', 'description' => 'Process market sales'],

            // Reports
            ['name' => 'reports.view_subscription', 'module' => 'reports', 'description' => 'View subscription reports'],
            ['name' => 'reports.view_market', 'module' => 'reports', 'description' => 'View market reports'],
            ['name' => 'reports.view_financial', 'module' => 'reports', 'description' => 'View financial reports'],
            ['name' => 'reports.view_all', 'module' => 'reports', 'description' => 'View all reports'],

            // Settings
            ['name' => 'settings.manage', 'module' => 'settings', 'description' => 'Manage settings'],
            ['name' => 'settings.change_language', 'module' => 'settings', 'description' => 'Change language'],

            // Users
            ['name' => 'users.view', 'module' => 'users', 'description' => 'View users'],
            ['name' => 'users.create', 'module' => 'users', 'description' => 'Create users'],
            ['name' => 'users.edit', 'module' => 'users', 'description' => 'Edit users'],
            ['name' => 'users.delete', 'module' => 'users', 'description' => 'Delete users'],

            // Roles
            ['name' => 'roles.view', 'module' => 'roles', 'description' => 'View roles'],
            ['name' => 'roles.create', 'module' => 'roles', 'description' => 'Create roles'],
            ['name' => 'roles.edit', 'module' => 'roles', 'description' => 'Edit roles'],
            ['name' => 'roles.delete', 'module' => 'roles', 'description' => 'Delete roles'],
        ];

        foreach ($permissions as $permission) {
            $permission['created_at'] = now();
            $permission['updated_at'] = now();
        }

        DB::table('permissions')->insert($permissions);
    }
}
```

---

## 5. Membership Plan Seeder

```php
<?php
// database/seeders/MembershipPlanSeeder.php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\DB;

class MembershipPlanSeeder extends Seeder
{
    public function run(): void
    {
        $plans = [
            [
                'name' => '1 Month',
                'description' => 'One month membership',
                'duration_days' => 30,
                'price_male' => 50000.00, // 50,000 IQD
                'price_female' => 40000.00, // 40,000 IQD
                'class_limit' => null,
                'personal_training_included' => false,
                'status' => 'active',
            ],
            [
                'name' => '3 Months',
                'description' => 'Three months membership - 10% discount',
                'duration_days' => 90,
                'price_male' => 135000.00, // 135,000 IQD
                'price_female' => 108000.00, // 108,000 IQD
                'class_limit' => null,
                'personal_training_included' => false,
                'status' => 'active',
            ],
            [
                'name' => '6 Months',
                'description' => 'Six months membership - 15% discount + 2 PT sessions',
                'duration_days' => 180,
                'price_male' => 255000.00, // 255,000 IQD
                'price_female' => 204000.00, // 204,000 IQD
                'class_limit' => null,
                'personal_training_included' => true,
                'status' => 'active',
            ],
            [
                'name' => '1 Year',
                'description' => 'One year membership - 25% discount + 5 PT sessions',
                'duration_days' => 365,
                'price_male' => 450000.00, // 450,000 IQD
                'price_female' => 360000.00, // 360,000 IQD
                'class_limit' => null,
                'personal_training_included' => true,
                'status' => 'active',
            ],
        ];

        foreach ($plans as $plan) {
            $plan['created_at'] = now();
            $plan['updated_at'] = now();
        }

        DB::table('membership_plans')->insert($plans);
    }
}
```

---

## 6. User Model with Permission Trait

```php
<?php
// app/Models/User.php

namespace App\Models;

use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Foundation\Auth\User as Authenticatable;
use Illuminate\Notifications\Notifiable;

class User extends Authenticatable
{
    use Notifiable;

    protected $fillable = [
        'name',
        'email',
        'password',
        'role_id',
        'phone',
        'photo',
        'language',
        'status',
        'last_login',
    ];

    protected $hidden = [
        'password',
        'remember_token',
    ];

    protected $casts = [
        'email_verified_at' => 'datetime',
        'last_login' => 'datetime',
        'password' => 'hashed',
    ];

    // Relationship
    public function role(): BelongsTo
    {
        return $this->belongsTo(Role::class);
    }

    // Permission checking
    public function hasPermission(string $permission): bool
    {
        if (!$this->role) {
            return false;
        }

        $permissions = json_decode($this->role->permissions, true) ?? [];

        // Check for wildcard permission (e.g., members.*)
        $module = explode('.', $permission)[0];
        if (in_array($module . '.*', $permissions)) {
            return true;
        }

        // Check for exact permission
        return in_array($permission, $permissions);
    }

    public function can($permission, $arguments = [])
    {
        return $this->hasPermission($permission);
    }

    public function isAdmin(): bool
    {
        return $this->role && $this->role->name === 'Admin';
    }
}
```

---

## 7. Permission Middleware

```php
<?php
// app/Http/Middleware/CheckPermission.php

namespace App\Http\Middleware;

use Closure;
use Illuminate\Http\Request;

class CheckPermission
{
    public function handle(Request $request, Closure $next, string $permission)
    {
        if (!auth()->check()) {
            return redirect()->route('login');
        }

        if (!auth()->user()->hasPermission($permission)) {
            abort(403, 'You do not have permission to access this resource.');
        }

        return $next($request);
    }
}

// Register in bootstrap/app.php:
// ->withMiddleware(function (Middleware $middleware) {
//     $middleware->alias([
//         'permission' => \App\Http\Middleware\CheckPermission::class,
//     ]);
// })
```

---

## 8. Sample Controller (MemberController)

```php
<?php
// app/Http/Controllers/MemberController.php

namespace App\Http\Controllers;

use App\Models\Member;
use Illuminate\Http\Request;

class MemberController extends Controller
{
    public function index(Request $request)
    {
        // Check permission
        if (!auth()->user()->hasPermission('members.view')) {
            abort(403);
        }

        $query = Member::query()->with('currentMembership.membershipPlan');

        // Search
        if ($request->filled('search')) {
            $query->search($request->search);
        }

        // Filter by status
        if ($request->filled('status')) {
            $query->where('status', $request->status);
        }

        // Filter by gender
        if ($request->filled('gender')) {
            $query->byGender($request->gender);
        }

        $members = $query->latest()->paginate(25);

        return view('members.index', compact('members'));
    }

    public function create()
    {
        if (!auth()->user()->hasPermission('members.create')) {
            abort(403);
        }

        return view('members.create');
    }

    public function store(Request $request)
    {
        if (!auth()->user()->hasPermission('members.create')) {
            abort(403);
        }

        $validated = $request->validate([
            'first_name' => 'required|string|max:255',
            'last_name' => 'required|string|max:255',
            'phone' => 'required|string|unique:members,phone',
            'email' => 'nullable|email|unique:members,email',
            'date_of_birth' => 'required|date|before:today',
            'gender' => 'required|in:male,female',
            'emergency_contact' => 'required|string',
            'emergency_phone' => 'required|string',
            'blood_type' => 'nullable|in:A+,A-,B+,B-,AB+,AB-,O+,O-',
            'photo' => 'nullable|image|max:2048',
            'address' => 'nullable|string',
            'status' => 'required|in:active,inactive',
        ]);

        // Handle photo upload
        if ($request->hasFile('photo')) {
            $path = $request->file('photo')->store('members', 'public');
            $validated['photo'] = basename($path);
        }

        $member = Member::create($validated);

        return redirect()->route('members.show', $member)
            ->with('success', 'Member created successfully with code: ' . $member->member_code);
    }

    public function show(Member $member)
    {
        if (!auth()->user()->hasPermission('members.view')) {
            abort(403);
        }

        $member->load([
            'memberships.membershipPlan',
            'payments',
            'attendance' => fn($q) => $q->latest()->limit(10)
        ]);

        return view('members.show', compact('member'));
    }

    // Add edit, update, destroy methods...
}
```

---

## 9. Sample Routes

```php
<?php
// routes/web.php

use App\Http\Controllers\MemberController;
use App\Http\Controllers\MembershipController;
use App\Http\Controllers\AttendanceController;
use Illuminate\Support\Facades\Route;

Route::middleware(['auth'])->group(function () {
    
    // Dashboard
    Route::get('/', function () {
        return view('dashboard');
    })->name('dashboard');
    
    // Members
    Route::middleware(['permission:members.view'])->group(function () {
        Route::resource('members', MemberController::class);
    });
    
    // Memberships
    Route::middleware(['permission:memberships.view'])->group(function () {
        Route::resource('memberships', MembershipController::class);
        Route::get('/memberships/expiring', [MembershipController::class, 'expiring'])
            ->name('memberships.expiring');
    });
    
    // Attendance
    Route::middleware(['permission:attendance.checkin'])->group(function () {
        Route::get('/attendance/checkin', [AttendanceController::class, 'checkin'])
            ->name('attendance.checkin');
        Route::post('/attendance/process', [AttendanceController::class, 'process'])
            ->name('attendance.process');
    });
});
```

---

## 10. How to Run Seeders

```bash
# Run specific seeder
php artisan db:seed --class=RoleSeeder
php artisan db:seed --class=PermissionSeeder
php artisan db:seed --class=MembershipPlanSeeder

# Or update DatabaseSeeder.php to run all:
public function run(): void
{
    $this->call([
        RoleSeeder::class,
        PermissionSeeder::class,
        MembershipPlanSeeder::class,
    ]);
}

# Then run:
php artisan db:seed
```

---

## Summary

You now have:
1. ✅ Complete database with 22 tables
2. ✅ Full Member model as example
3. ✅ Role & Permission seeders
4. ✅ Membership Plan seeder
5. ✅ User model with permission checking
6. ✅ Sample controller pattern
7. ✅ Middleware for permissions
8. ✅ Route structure examples

**Next steps**: 
- Complete remaining models
- Create all controllers
- Build views (you need Vristo template)
- Create service layer
- Add multi-language files
- Test workflows

Good luck! 🚀
