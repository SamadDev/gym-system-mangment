<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * Run the migrations.
     */
    public function up(): void
    {
        // 1. Members table (independent)
        Schema::create('members', function (Blueprint $table) {
            $table->id();
            $table->string('member_code')->unique();
            $table->string('first_name');
            $table->string('last_name');
            $table->string('phone');
            $table->string('email')->nullable();
            $table->text('address')->nullable();
            $table->date('date_of_birth');
            $table->enum('gender', ['male', 'female']);
            $table->string('photo')->nullable();
            $table->string('emergency_contact');
            $table->string('emergency_phone');
            $table->enum('blood_type', ['A+', 'A-', 'B+', 'B-', 'AB+', 'AB-', 'O+', 'O-'])->nullable();
            $table->enum('status', ['active', 'inactive'])->default('active');
            $table->timestamps();
        });

        // 4. Membership Plans table (independent)
        Schema::create('membership_plans', function (Blueprint $table) {
            $table->id();
            $table->string('name');
            $table->text('description')->nullable();
            $table->integer('duration_days');
            $table->decimal('price_male', 10, 2);
            $table->decimal('price_female', 10, 2);
            $table->integer('class_limit')->nullable();
            $table->boolean('personal_training_included')->default(false);
            $table->enum('status', ['active', 'inactive'])->default('active');
            $table->timestamps();
        });

        // 5. Memberships table (depends on members, membership_plans)
        Schema::create('memberships', function (Blueprint $table) {
            $table->id();
            $table->foreignId('member_id')->constrained('members')->onDelete('cascade');
            $table->foreignId('membership_plan_id')->constrained('membership_plans')->onDelete('cascade');
            $table->date('start_date');
            $table->date('end_date');
            $table->decimal('amount_paid', 10, 2);
            $table->enum('status', ['active', 'expired'])->default('active');
            $table->timestamps();
        });

        // 6. Attendance table (depends on members)
        Schema::create('attendance', function (Blueprint $table) {
            $table->id();
            $table->foreignId('member_id')->constrained('members')->onDelete('cascade');
            $table->dateTime('check_in_time');
            $table->dateTime('check_out_time')->nullable();
            $table->enum('entry_method', ['manual', 'card', 'qr'])->default('manual');
            $table->text('notes')->nullable();
            $table->timestamp('created_at');
        });

        // 7. Trainers table (independent)
        Schema::create('trainers', function (Blueprint $table) {
            $table->id();
            $table->string('first_name');
            $table->string('last_name');
            $table->string('phone');
            $table->string('email')->nullable();
            $table->text('certifications')->nullable();
            $table->date('hire_date');
            $table->decimal('salary', 10, 2)->nullable();
            $table->decimal('commission_rate', 5, 2)->nullable();
            $table->string('photo')->nullable();
            $table->enum('status', ['active', 'on_leave', 'terminated'])->default('active');
            $table->timestamps();
        });

        // 8. Trainer Specializations table (depends on trainers)
        Schema::create('trainer_specializations', function (Blueprint $table) {
            $table->id();
            $table->foreignId('trainer_id')->constrained('trainers')->onDelete('cascade');
            $table->string('specialization');
            $table->timestamp('created_at');
        });

        // 9. Classes table (depends on trainers)
        Schema::create('classes', function (Blueprint $table) {
            $table->id();
            $table->string('name');
            $table->text('description')->nullable();
            $table->foreignId('trainer_id')->nullable()->constrained('trainers')->onDelete('set null');
            $table->integer('capacity')->nullable();
            $table->integer('duration_minutes');
            $table->enum('difficulty_level', ['beginner', 'intermediate', 'advanced'])->nullable();
            $table->enum('gender_type', ['male', 'female', 'mixed']);
            $table->enum('status', ['active', 'inactive'])->default('active');
            $table->timestamps();
        });

        // 10. Class Schedules table (depends on classes)
        Schema::create('class_schedules', function (Blueprint $table) {
            $table->id();
            $table->foreignId('class_id')->constrained('classes')->onDelete('cascade');
            $table->enum('day_of_week', ['monday', 'tuesday', 'wednesday', 'thursday', 'friday', 'saturday', 'sunday']);
            $table->time('start_time');
            $table->time('end_time');
            $table->string('room')->nullable();
            $table->timestamps();
        });

        // 11. Personal Training Sessions table (depends on members, trainers)
        Schema::create('personal_training_sessions', function (Blueprint $table) {
            $table->id();
            $table->foreignId('member_id')->constrained('members')->onDelete('cascade');
            $table->foreignId('trainer_id')->constrained('trainers')->onDelete('cascade');
            $table->dateTime('session_date');
            $table->integer('duration_minutes');
            $table->decimal('price', 10, 2);
            $table->enum('status', ['scheduled', 'completed', 'cancelled'])->default('scheduled');
            $table->text('notes')->nullable();
            $table->timestamps();
        });

        // 12. Payments table (depends on members, memberships, users)
        Schema::create('payments', function (Blueprint $table) {
            $table->id();
            $table->foreignId('member_id')->nullable()->constrained('members')->onDelete('set null');
            $table->foreignId('membership_id')->nullable()->constrained('memberships')->onDelete('set null');
            $table->enum('payment_type', ['membership', 'personal_training', 'market_sale']);
            $table->decimal('amount', 10, 2);
            $table->enum('payment_method', ['cash', 'card', 'bank_transfer']);
            $table->dateTime('payment_date');
            $table->string('reference_number')->unique();
            $table->text('notes')->nullable();
            $table->enum('status', ['completed', 'refunded'])->default('completed');
            $table->foreignId('created_by')->constrained('users')->onDelete('cascade');
            $table->timestamps();
        });

        // 13. Invoices table (depends on payments)
        Schema::create('invoices', function (Blueprint $table) {
            $table->id();
            $table->foreignId('payment_id')->constrained('payments')->onDelete('cascade');
            $table->string('invoice_number')->unique();
            $table->decimal('subtotal', 10, 2);
            $table->decimal('tax', 10, 2)->default(0);
            $table->decimal('discount', 10, 2)->default(0);
            $table->decimal('total', 10, 2);
            $table->dateTime('issue_date');
            $table->timestamps();
        });

        // 14. Suppliers table (independent)
        Schema::create('suppliers', function (Blueprint $table) {
            $table->id();
            $table->string('name');
            $table->string('contact_person')->nullable();
            $table->string('phone');
            $table->string('email')->nullable();
            $table->text('address')->nullable();
            $table->enum('status', ['active', 'inactive'])->default('active');
            $table->timestamps();
        });

        // 15. Inventory Items table (depends on suppliers)
        Schema::create('inventory_items', function (Blueprint $table) {
            $table->id();
            $table->foreignId('supplier_id')->nullable()->constrained('suppliers')->onDelete('set null');
            $table->string('name');
            $table->string('sku')->unique();
            $table->string('category');
            $table->integer('quantity')->default(0);
            $table->decimal('purchase_price', 10, 2);
            $table->decimal('selling_price', 10, 2);
            $table->integer('reorder_level');
            $table->string('image')->nullable();
            $table->enum('status', ['active', 'inactive'])->default('active');
            $table->timestamps();
        });

        // 16. Inventory Transactions table (depends on inventory_items, users)
        Schema::create('inventory_transactions', function (Blueprint $table) {
            $table->id();
            $table->foreignId('inventory_item_id')->constrained('inventory_items')->onDelete('cascade');
            $table->enum('transaction_type', ['purchase', 'sale', 'adjustment', 'return']);
            $table->integer('quantity');
            $table->decimal('unit_price', 10, 2);
            $table->string('reference')->nullable();
            $table->text('notes')->nullable();
            $table->dateTime('transaction_date');
            $table->foreignId('created_by')->constrained('users')->onDelete('cascade');
            $table->timestamp('created_at');
        });

        // 17. Equipment table (independent)
        Schema::create('equipment', function (Blueprint $table) {
            $table->id();
            $table->string('name');
            $table->string('equipment_code')->unique();
            $table->string('category');
            $table->date('purchase_date')->nullable();
            $table->decimal('purchase_price', 10, 2)->nullable();
            $table->enum('condition', ['excellent', 'good', 'fair', 'poor', 'broken'])->default('excellent');
            $table->date('last_maintenance_date')->nullable();
            $table->date('next_maintenance_date')->nullable();
            $table->enum('status', ['active', 'under_maintenance', 'retired'])->default('active');
            $table->text('notes')->nullable();
            $table->timestamps();
        });

        // 18. Maintenance Logs table (depends on equipment, users)
        Schema::create('maintenance_logs', function (Blueprint $table) {
            $table->id();
            $table->foreignId('equipment_id')->constrained('equipment')->onDelete('cascade');
            $table->date('maintenance_date');
            $table->enum('maintenance_type', ['routine', 'repair', 'inspection']);
            $table->text('description');
            $table->decimal('cost', 10, 2)->nullable();
            $table->string('technician_name')->nullable();
            $table->date('next_maintenance_date')->nullable();
            $table->foreignId('performed_by')->nullable()->constrained('users')->onDelete('set null');
            $table->timestamps();
        });

        // 19. Expense Categories table (independent)
        Schema::create('expense_categories', function (Blueprint $table) {
            $table->id();
            $table->string('name');
            $table->text('description')->nullable();
            $table->enum('status', ['active', 'inactive'])->default('active');
            $table->timestamps();
        });

        // 20. Expenses table (depends on expense_categories, users)
        Schema::create('expenses', function (Blueprint $table) {
            $table->id();
            $table->foreignId('expense_category_id')->constrained('expense_categories')->onDelete('cascade');
            $table->decimal('amount', 10, 2);
            $table->date('expense_date');
            $table->text('description');
            $table->enum('payment_method', ['cash', 'card', 'bank_transfer', 'cheque']);
            $table->string('receipt_image')->nullable();
            $table->foreignId('approved_by')->nullable()->constrained('users')->onDelete('set null');
            $table->foreignId('created_by')->constrained('users')->onDelete('cascade');
            $table->enum('status', ['pending', 'approved', 'rejected'])->default('pending');
            $table->timestamps();
        });

        // 21. Access Logs table (depends on users)
        Schema::create('access_logs', function (Blueprint $table) {
            $table->id();
            $table->foreignId('user_id')->constrained('users')->onDelete('cascade');
            $table->string('action');
            $table->string('module');
            $table->integer('record_id')->nullable();
            $table->string('ip_address');
            $table->text('user_agent');
            $table->timestamp('created_at');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('access_logs');
        Schema::dropIfExists('expenses');
        Schema::dropIfExists('expense_categories');
        Schema::dropIfExists('maintenance_logs');
        Schema::dropIfExists('equipment');
        Schema::dropIfExists('inventory_transactions');
        Schema::dropIfExists('inventory_items');
        Schema::dropIfExists('suppliers');
        Schema::dropIfExists('invoices');
        Schema::dropIfExists('payments');
        Schema::dropIfExists('personal_training_sessions');
        Schema::dropIfExists('class_schedules');
        Schema::dropIfExists('classes');
        Schema::dropIfExists('trainer_specializations');
        Schema::dropIfExists('trainers');
        Schema::dropIfExists('attendance');
        Schema::dropIfExists('memberships');
        Schema::dropIfExists('membership_plans');
        Schema::dropIfExists('members');
    }
};
