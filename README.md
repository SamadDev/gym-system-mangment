# Kurdistan Gym Management System 🏋️

> **A comprehensive gym management system built with Laravel 11, tailored for Kurdistan gyms with gender-based pricing, multi-language support (EN/AR/KU), and complete business management features.**

[![Laravel](https://img.shields.io/badge/Laravel-11.x-red.svg)](https://laravel.com)
[![PHP](https://img.shields.io/badge/PHP-8.2+-blue.svg)](https://php.net)
[![License](https://img.shields.io/badge/License-Proprietary-yellow.svg)]()

---

## 🎉 Project Status: Foundation Complete!

**✅ Database Architecture**: All 22 tables created and migrated  
**✅ Core Models**: 10 Eloquent models with example implementations  
**✅ Type-Safe Enums**: 6 enums for data integrity  
**✅ Seeders Ready**: Roles, Permissions, Membership Plans  
**✅ Documentation**: 4 comprehensive guides (1000+ lines)  
**✅ Verified & Working**: Run `./verify.sh` to see

**Current Progress**: ~15% complete (foundation stage)

---

## 📚 Documentation

**Start here:**
1. **[PROJECT_SUMMARY.md](PROJECT_SUMMARY.md)** - Quick overview of what's done and what's next
2. **[GYM_SYSTEM_README.md](GYM_SYSTEM_README.md)** - Complete system architecture and requirements
3. **[IMPLEMENTATION_GUIDE.md](IMPLEMENTATION_GUIDE.md)** - Code examples, patterns, and how-to guides  
4. **[QUICK_COMMANDS.md](QUICK_COMMANDS.md)** - All Laravel artisan and useful commands

---

## 🚀 Quick Start

### 1. Verify Installation
```bash
./verify.sh
```

### 2. Run Seeders
```bash
php artisan db:seed --class=RoleSeeder
php artisan db:seed --class=PermissionSeeder
php artisan db:seed --class=MembershipPlanSeeder
```

### 3. Start Development Server
```bash
php artisan serve
```

Visit: http://localhost:8000

---

## 🗄️ Database Structure

### 22 Custom Tables (All Migrated ✅)

**Core System:**
- `roles` - Dynamic role management
- `permissions` - Granular permissions (60+)
- `users` - Staff accounts (enhanced)
- `members` - Gym members with profiles

**Membership Management:**
- `membership_plans` - Gender-based pricing
- `memberships` - Active subscriptions
- `attendance` - Check-in/check-out logs

**Trainers & Classes:**
- `trainers` - Trainer profiles
- `trainer_specializations` - Multiple specializations
- `classes` - Class definitions
- `class_schedules` - Weekly schedules
- `personal_training_sessions` - 1-on-1 sessions

**Financial:**
- `payments` - All payment types
- `invoices` - Auto-generated invoices

**Inventory:**
- `suppliers` - Supplier management
- `inventory_items` - Products for sale
- `inventory_transactions` - Stock movements

**Equipment:**
- `equipment` - Gym equipment tracking
- `maintenance_logs` - Service records

**Expenses:**
- `expense_categories` - Dynamic categories
- `expenses` - Expense tracking with approval

**Audit:**
- `access_logs` - User activity tracking

---

## 💡 Key Features (Implemented in Schema)

✅ **Gender-Based Pricing**: Separate male/female prices in membership plans  
✅ **Auto-Generated Codes**: Member codes, payment references, invoice numbers  
✅ **Multi-Currency Ready**: IQD (Iraqi Dinar) with proper decimal handling  
✅ **Timezone Configured**: Asia/Baghdad  
✅ **Multi-Language Structure**: English, Arabic, Kurdish support  
✅ **Separate Financial Streams**: Subscription vs Market sales  
✅ **Permission-Based Access**: 60+ granular permissions  
✅ **Audit Trail**: Access logs for all user actions

---

## 📦 What's Included

### Code Structure
```
app/
├── Enums/              ✅ 6 type-safe enums (Gender, Status, etc.)
├── Models/             ✅ 10 core models (Member fully implemented)
├── Services/           📂 Ready for business logic
├── Helpers/            📂 Ready for helper functions
├── Traits/             📂 Ready for reusable traits
├── Constants/          📂 Ready for constants
└── Http/
    ├── Controllers/    📂 Sample patterns in docs
    ├── Middleware/     📂 Permission checking ready
    └── Requests/       📂 Ready for validation classes
```

### Documentation (4 Files, 2000+ Lines)
- ✅ GYM_SYSTEM_README.md - Complete architecture
- ✅ IMPLEMENTATION_GUIDE.md - Code examples & patterns  
- ✅ QUICK_COMMANDS.md - All commands
- ✅ PROJECT_SUMMARY.md - Status & next steps

### Seeders (3 Ready)
- ✅ RoleSeeder (5 default roles)
- ✅ PermissionSeeder (60+ permissions)
- ✅ MembershipPlanSeeder (4 plans with gender pricing)

---

## 🎯 Next Steps

### For Developers:
1. Read [IMPLEMENTATION_GUIDE.md](IMPLEMENTATION_GUIDE.md)
2. Complete remaining models following Member.php example
3. Create controllers using provided patterns
4. Build views with Vristo template
5. Add multi-language files

**Estimated Time**: 40-60 hours for complete implementation

### For Project Managers:
1. Review [PROJECT_SUMMARY.md](PROJECT_SUMMARY.md)
2. See foundation is complete (~15% of project)
3. Hire Laravel developer to complete (1-2 weeks)
4. Or build incrementally (2-3 weeks part-time)

---

## 🛠️ Tech Stack

- **Framework**: Laravel 11
- **PHP**: 8.2+
- **Database**: SQLite (dev) / MySQL (production ready)
- **Frontend**: Vristo Admin Template (to be integrated)
- **Styling**: Tailwind CSS
- **Languages**: English, Arabic, Kurdish (RTL support)
- **Currency**: Iraqi Dinar (IQD)
- **Timezone**: Asia/Baghdad

---

## 📊 Project Stats

| Metric | Count |
|--------|-------|
| Database Tables | 22 custom + 8 Laravel = 30 total |
| Models | 10 created (22 needed) |
| Enums | 6 type-safe enums |
| Seeders | 3 ready to run |
| Permissions | 60+ granular permissions |
| Default Roles | 5 (Admin, Manager, Receptionist, Market Staff, Trainer) |
| Documentation | 4 files, 2000+ lines |
| Lines of Code | ~2500+ (foundation) |

---

## 🔐 Default Configuration

### Database
- **Type**: SQLite (development)
- **File**: `database/database.sqlite`
- **Tables**: 30 total (all migrated ✅)

### Application
- **Name**: Kurdistan Gym Management
- **Locale**: English (en)
- **Timezone**: Asia/Baghdad (UTC+3)
- **Debug**: Enabled (development)

### Supported Languages
- **English** (en) - LTR
- **Arabic** (ar) - RTL
- **Kurdish** (ku) - RTL

---

## 📖 Learning Resources

### Laravel Documentation
- [Laravel 11 Docs](https://laravel.com/docs/11.x)
- [Eloquent ORM](https://laravel.com/docs/11.x/eloquent)
- [Migrations](https://laravel.com/docs/11.x/migrations)

### Project-Specific
- See [IMPLEMENTATION_GUIDE.md](IMPLEMENTATION_GUIDE.md) for:
  - Complete model examples
  - Controller patterns
  - Permission system usage
  - Service layer patterns
  - Blade component examples

---

## 🤝 Contributing

This is a proprietary project for Kurdistan gyms. For questions or contributions, contact the project team.

---

## 📝 License

Proprietary - All Rights Reserved

---

## 🎊 Credits

Built with ❤️ for Kurdistan Gyms  
**Laravel 11** + **Modern PHP 8.2+ Practices**  
**February 2026**

---

**Status**: ✅ Foundation Complete | 🚧 Development Ready | 📈 ~15% Complete

Run `./verify.sh` to see full verification report.
