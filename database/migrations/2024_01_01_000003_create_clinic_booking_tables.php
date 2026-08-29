<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        // 2. TENANTS
        Schema::create('tenants', function (Blueprint $table) {
            $table->uuid('id')->primary();
            $table->string('name', 150);
            $table->string('slug', 150)->unique();
            $table->string('logo', 255)->nullable();
            $table->string('email', 150)->nullable();
            $table->string('phone', 30)->nullable();
            $table->string('whatsapp', 30)->nullable();
            $table->text('address')->nullable();
            $table->string('city', 100)->nullable();
            $table->string('province', 100)->nullable();
            $table->string('postal_code', 20)->nullable();
            $table->decimal('latitude', 10, 7)->nullable();
            $table->decimal('longitude', 10, 7)->nullable();
            $table->text('description')->nullable();
            $table->string('timezone', 100)->default('Asia/Jakarta');
            $table->enum('status', ['trial', 'active', 'suspended', 'expired'])->default('trial');
            $table->timestamp('trial_ends_at')->nullable();
            $table->timestamps();
        });

        // 3. TENANT USERS
        Schema::create('tenant_users', function (Blueprint $table) {
            $table->uuid('id')->primary();
            $table->foreignUuid('tenant_id')->constrained()->cascadeOnDelete();
            $table->foreignUuid('user_id')->constrained()->cascadeOnDelete();
            $table->enum('role', ['owner', 'admin', 'receptionist', 'staff'])->default('admin');
            $table->boolean('is_active')->default(true);
            $table->timestamps();

            $table->unique(['tenant_id', 'user_id']);
        });

        // 4. TREATMENT CATEGORIES
        Schema::create('treatment_categories', function (Blueprint $table) {
            $table->uuid('id')->primary();
            $table->foreignUuid('tenant_id')->constrained()->cascadeOnDelete();
            $table->string('name', 150);
            $table->text('description')->nullable();
            $table->string('image', 255)->nullable();
            $table->integer('sort_order')->default(0);
            $table->boolean('is_active')->default(true);
            $table->timestamps();
        });

        // 5. TREATMENTS
        Schema::create('treatments', function (Blueprint $table) {
            $table->uuid('id')->primary();
            $table->foreignUuid('tenant_id')->constrained()->cascadeOnDelete();
            $table->foreignUuid('category_id')->nullable()->constrained('treatment_categories')->nullOnDelete();
            $table->string('name', 150);
            $table->string('slug', 180)->nullable();
            $table->text('description')->nullable();
            $table->decimal('price', 15, 2)->default(0);
            $table->integer('duration_minutes')->default(60);
            $table->string('image', 255)->nullable();
            $table->integer('sort_order')->default(0);
            $table->boolean('is_active')->default(true);
            $table->timestamps();
        });

        // 6. STAFF
        Schema::create('staff', function (Blueprint $table) {
            $table->uuid('id')->primary();
            $table->foreignUuid('tenant_id')->constrained()->cascadeOnDelete();
            $table->foreignUuid('user_id')->nullable()->constrained('users')->nullOnDelete();
            $table->string('name', 150);
            $table->string('phone', 30)->nullable();
            $table->string('email', 150)->nullable();
            $table->string('photo', 255)->nullable();
            $table->string('role', 50)->nullable();
            $table->string('specialization', 150)->nullable();
            $table->text('bio')->nullable();
            $table->boolean('is_active')->default(true);
            $table->timestamps();
        });

        // 7. STAFF TREATMENTS
        Schema::create('staff_treatments', function (Blueprint $table) {
            $table->uuid('id')->primary();
            $table->foreignUuid('tenant_id')->constrained()->cascadeOnDelete();
            $table->foreignUuid('staff_id')->constrained('staff')->cascadeOnDelete();
            $table->foreignUuid('treatment_id')->constrained('treatments')->cascadeOnDelete();
            $table->timestamp('created_at')->useCurrent();

            $table->unique(['staff_id', 'treatment_id']);
        });

        // 8. OPERATING HOURS
        Schema::create('operating_hours', function (Blueprint $table) {
            $table->uuid('id')->primary();
            $table->foreignUuid('tenant_id')->constrained()->cascadeOnDelete();
            $table->smallInteger('day_of_week'); // 0-6
            $table->boolean('is_open')->default(true);
            $table->time('open_time')->nullable();
            $table->time('close_time')->nullable();
            $table->timestamps();

            $table->unique(['tenant_id', 'day_of_week']);
        });

        // 9. STAFF SCHEDULE
        Schema::create('staff_schedules', function (Blueprint $table) {
            $table->uuid('id')->primary();
            $table->foreignUuid('tenant_id')->constrained()->cascadeOnDelete();
            $table->foreignUuid('staff_id')->constrained('staff')->cascadeOnDelete();
            $table->smallInteger('day_of_week'); // 0-6
            $table->boolean('is_working')->default(true);
            $table->time('start_time')->nullable();
            $table->time('end_time')->nullable();
            $table->timestamps();

            $table->unique(['staff_id', 'day_of_week']);
        });

        // 10. STAFF LEAVE / TIME OFF
        Schema::create('staff_time_off', function (Blueprint $table) {
            $table->uuid('id')->primary();
            $table->foreignUuid('tenant_id')->constrained()->cascadeOnDelete();
            $table->foreignUuid('staff_id')->constrained('staff')->cascadeOnDelete();
            $table->timestamp('start_datetime');
            $table->timestamp('end_datetime');
            $table->string('reason', 255)->nullable();
            $table->timestamps();
        });

        // 11. CUSTOMERS
        Schema::create('customers', function (Blueprint $table) {
            $table->uuid('id')->primary();
            $table->foreignUuid('tenant_id')->constrained()->cascadeOnDelete();
            $table->string('name', 150);
            $table->string('phone', 30);
            $table->string('email', 150)->nullable();
            $table->string('gender', 20)->nullable();
            $table->date('birth_date')->nullable();
            $table->text('address')->nullable();
            $table->text('notes')->nullable();
            $table->integer('total_visits')->default(0);
            $table->decimal('total_spent', 15, 2)->default(0);
            $table->timestamp('last_visit_at')->nullable();
            $table->timestamps();
        });

        // 12. BOOKINGS
        Schema::create('bookings', function (Blueprint $table) {
            $table->uuid('id')->primary();
            $table->foreignUuid('tenant_id')->constrained()->cascadeOnDelete();
            $table->string('booking_number', 50);
            $table->foreignUuid('customer_id')->constrained()->restrictOnDelete();
            $table->foreignUuid('treatment_id')->constrained()->restrictOnDelete();
            $table->foreignUuid('staff_id')->nullable()->constrained('staff')->nullOnDelete();
            $table->date('booking_date');
            $table->time('start_time');
            $table->time('end_time');
            $table->decimal('price', 15, 2)->default(0);
            $table->decimal('discount', 15, 2)->default(0);
            $table->decimal('total_amount', 15, 2)->default(0);
            $table->enum('status', ['pending', 'confirmed', 'checked_in', 'in_progress', 'completed', 'cancelled', 'no_show'])->default('pending');
            $table->enum('source', ['website', 'admin', 'whatsapp', 'manual'])->default('website');
            $table->text('notes')->nullable();
            $table->timestamp('cancelled_at')->nullable();
            $table->timestamp('completed_at')->nullable();
            $table->timestamps();

            $table->unique(['tenant_id', 'booking_number']);
        });

        // 13. BOOKING STATUS HISTORY
        Schema::create('booking_status_histories', function (Blueprint $table) {
            $table->uuid('id')->primary();
            $table->foreignUuid('tenant_id')->constrained()->cascadeOnDelete();
            $table->foreignUuid('booking_id')->constrained()->cascadeOnDelete();
            $table->string('old_status', 30)->nullable();
            $table->string('new_status', 30);
            $table->text('notes')->nullable();
            $table->foreignUuid('changed_by')->nullable()->constrained('users')->nullOnDelete();
            $table->timestamp('created_at')->useCurrent();
        });

        // 14. TRANSACTIONS
        Schema::create('transactions', function (Blueprint $table) {
            $table->uuid('id')->primary();
            $table->foreignUuid('tenant_id')->constrained()->cascadeOnDelete();
            $table->string('transaction_number', 50);
            $table->foreignUuid('booking_id')->nullable()->constrained()->nullOnDelete();
            $table->foreignUuid('customer_id')->constrained()->restrictOnDelete();
            $table->decimal('subtotal', 15, 2)->default(0);
            $table->decimal('discount', 15, 2)->default(0);
            $table->decimal('tax', 15, 2)->default(0);
            $table->decimal('total_amount', 15, 2)->default(0);
            $table->decimal('paid_amount', 15, 2)->default(0);
            $table->string('payment_method', 30)->nullable();
            $table->enum('payment_status', ['unpaid', 'partial', 'paid', 'refunded'])->default('unpaid');
            $table->timestamp('paid_at')->nullable();
            $table->text('notes')->nullable();
            $table->timestamps();

            $table->unique(['tenant_id', 'transaction_number']);
        });

        // 15. TRANSACTION ITEMS
        Schema::create('transaction_items', function (Blueprint $table) {
            $table->uuid('id')->primary();
            $table->foreignUuid('tenant_id')->constrained()->cascadeOnDelete();
            $table->foreignUuid('transaction_id')->constrained()->cascadeOnDelete();
            $table->foreignUuid('treatment_id')->nullable()->constrained()->nullOnDelete();
            $table->string('name', 150);
            $table->integer('quantity')->default(1);
            $table->decimal('price', 15, 2)->default(0);
            $table->decimal('discount', 15, 2)->default(0);
            $table->decimal('subtotal', 15, 2)->default(0);
            $table->timestamp('created_at')->useCurrent();
        });

        // 16. PROMOS
        Schema::create('promos', function (Blueprint $table) {
            $table->uuid('id')->primary();
            $table->foreignUuid('tenant_id')->constrained()->cascadeOnDelete();
            $table->string('name', 150);
            $table->string('code', 50)->nullable();
            $table->text('description')->nullable();
            $table->enum('discount_type', ['percentage', 'fixed']);
            $table->decimal('discount_value', 15, 2)->default(0);
            $table->decimal('min_transaction', 15, 2)->default(0);
            $table->decimal('max_discount', 15, 2)->nullable();
            $table->date('start_date');
            $table->date('end_date');
            $table->integer('usage_limit')->nullable();
            $table->integer('usage_count')->default(0);
            $table->boolean('is_active')->default(true);
            $table->timestamps();
        });

        // 17. PROMO TREATMENTS
        Schema::create('promo_treatments', function (Blueprint $table) {
            $table->uuid('id')->primary();
            $table->foreignUuid('promo_id')->constrained()->cascadeOnDelete();
            $table->foreignUuid('treatment_id')->constrained()->cascadeOnDelete();
            $table->timestamp('created_at')->useCurrent();

            $table->unique(['promo_id', 'treatment_id']);
        });

        // 18. MEMBERSHIP PLANS
        Schema::create('membership_plans', function (Blueprint $table) {
            $table->uuid('id')->primary();
            $table->foreignUuid('tenant_id')->constrained()->cascadeOnDelete();
            $table->string('name', 100);
            $table->text('description')->nullable();
            $table->decimal('price', 15, 2)->default(0);
            $table->integer('duration_days')->default(30);
            $table->decimal('discount_percentage', 5, 2)->default(0);
            $table->decimal('point_multiplier', 5, 2)->default(1);
            $table->boolean('is_active')->default(true);
            $table->timestamps();
        });

        // 19. CUSTOMER MEMBERSHIPS
        Schema::create('customer_memberships', function (Blueprint $table) {
            $table->uuid('id')->primary();
            $table->foreignUuid('tenant_id')->constrained()->cascadeOnDelete();
            $table->foreignUuid('customer_id')->constrained()->cascadeOnDelete();
            $table->foreignUuid('membership_plan_id')->constrained()->restrictOnDelete();
            $table->date('start_date');
            $table->date('end_date');
            $table->enum('status', ['active', 'expired', 'cancelled'])->default('active');
            $table->integer('points')->default(0);
            $table->timestamps();
        });

        // 20. WEBSITE SETTINGS
        Schema::create('website_settings', function (Blueprint $table) {
            $table->uuid('id')->primary();
            $table->foreignUuid('tenant_id')->constrained()->cascadeOnDelete();
            $table->string('site_title', 150)->nullable();
            $table->string('meta_title', 255)->nullable();
            $table->text('meta_description')->nullable();
            $table->string('hero_title', 255)->nullable();
            $table->text('hero_description')->nullable();
            $table->string('hero_image', 255)->nullable();
            $table->string('primary_color', 20)->nullable();
            $table->string('favicon', 255)->nullable();
            $table->string('facebook_url', 255)->nullable();
            $table->string('instagram_url', 255)->nullable();
            $table->string('tiktok_url', 255)->nullable();
            $table->text('google_maps_url')->nullable();
            $table->string('custom_domain', 255)->nullable();
            $table->boolean('custom_domain_verified')->default(false);
            $table->boolean('is_published')->default(true);
            $table->timestamps();

            $table->unique('tenant_id');
        });

        // 21. WEBSITE BANNERS
        Schema::create('website_banners', function (Blueprint $table) {
            $table->uuid('id')->primary();
            $table->foreignUuid('tenant_id')->constrained()->cascadeOnDelete();
            $table->string('title', 255)->nullable();
            $table->text('description')->nullable();
            $table->string('image', 255)->nullable();
            $table->string('button_text', 100)->nullable();
            $table->string('button_url', 255)->nullable();
            $table->integer('sort_order')->default(0);
            $table->boolean('is_active')->default(true);
            $table->timestamps();
        });

        // 22. WHATSAPP SETTINGS
        Schema::create('whatsapp_settings', function (Blueprint $table) {
            $table->uuid('id')->primary();
            $table->foreignUuid('tenant_id')->constrained()->cascadeOnDelete();
            $table->string('provider', 50)->nullable();
            $table->string('phone_number', 30)->nullable();
            $table->string('session_name', 150)->nullable();
            $table->enum('status', ['disconnected', 'connecting', 'connected'])->default('disconnected');
            $table->text('api_url')->nullable();
            $table->text('api_token')->nullable();
            $table->timestamp('connected_at')->nullable();
            $table->timestamps();

            $table->unique('tenant_id');
        });

        // 23. NOTIFICATION TEMPLATES
        Schema::create('notification_templates', function (Blueprint $table) {
            $table->uuid('id')->primary();
            $table->foreignUuid('tenant_id')->constrained()->cascadeOnDelete();
            $table->string('type', 50);
            $table->string('channel', 30)->default('whatsapp');
            $table->string('name', 150);
            $table->text('content');
            $table->boolean('is_active')->default(true);
            $table->timestamps();

            $table->unique(['tenant_id', 'type', 'channel']);
        });

        // 24. NOTIFICATION LOGS
        Schema::create('notification_logs', function (Blueprint $table) {
            $table->uuid('id')->primary();
            $table->foreignUuid('tenant_id')->constrained()->cascadeOnDelete();
            $table->foreignUuid('customer_id')->nullable()->constrained()->nullOnDelete();
            $table->foreignUuid('booking_id')->nullable()->constrained()->nullOnDelete();
            $table->string('channel', 30);
            $table->string('type', 50);
            $table->string('recipient', 150);
            $table->text('message')->nullable();
            $table->enum('status', ['pending', 'sent', 'failed'])->default('pending');
            $table->string('provider_message_id', 255)->nullable();
            $table->text('error_message')->nullable();
            $table->timestamp('sent_at')->nullable();
            $table->timestamp('created_at')->useCurrent();
        });

        // 25. SUBSCRIPTION PLANS
        Schema::create('subscription_plans', function (Blueprint $table) {
            $table->uuid('id')->primary();
            $table->string('name', 100);
            $table->string('slug', 100)->unique();
            $table->text('description')->nullable();
            $table->decimal('price_monthly', 15, 2)->default(0);
            $table->decimal('price_yearly', 15, 2)->nullable();
            $table->integer('max_staff')->nullable();
            $table->integer('max_booking')->nullable();
            $table->integer('max_customer')->nullable();
            $table->integer('max_outlet')->default(1);
            $table->boolean('whatsapp_enabled')->default(false);
            $table->boolean('membership_enabled')->default(false);
            $table->boolean('custom_domain_enabled')->default(false);
            $table->boolean('reports_enabled')->default(true);
            $table->boolean('is_active')->default(true);
            $table->timestamps();
        });

        // 26. TENANT SUBSCRIPTIONS
        Schema::create('tenant_subscriptions', function (Blueprint $table) {
            $table->uuid('id')->primary();
            $table->foreignUuid('tenant_id')->constrained()->cascadeOnDelete();
            $table->foreignUuid('plan_id')->constrained('subscription_plans')->restrictOnDelete();
            $table->enum('billing_cycle', ['monthly', 'yearly']);
            $table->date('start_date');
            $table->date('end_date');
            $table->enum('status', ['trial', 'active', 'expired', 'cancelled'])->default('active');
            $table->boolean('auto_renew')->default(true);
            $table->timestamps();
        });

        // 27. SUBSCRIPTION PAYMENTS
        Schema::create('subscription_payments', function (Blueprint $table) {
            $table->uuid('id')->primary();
            $table->foreignUuid('tenant_id')->constrained()->cascadeOnDelete();
            $table->foreignUuid('subscription_id')->constrained('tenant_subscriptions')->cascadeOnDelete();
            $table->string('invoice_number', 100)->unique();
            $table->decimal('amount', 15, 2);
            $table->string('payment_method', 50)->nullable();
            $table->string('payment_gateway', 50)->nullable();
            $table->string('gateway_transaction_id', 255)->nullable();
            $table->enum('status', ['pending', 'paid', 'failed', 'expired', 'refunded'])->default('pending');
            $table->timestamp('paid_at')->nullable();
            $table->timestamp('expired_at')->nullable();
            $table->timestamps();
        });

        // 28. ACTIVITY LOGS
        Schema::create('activity_logs', function (Blueprint $table) {
            $table->uuid('id')->primary();
            $table->foreignUuid('tenant_id')->nullable()->constrained()->cascadeOnDelete();
            $table->foreignUuid('user_id')->nullable()->constrained()->nullOnDelete();
            $table->string('action', 100);
            $table->string('module', 100)->nullable();
            $table->text('description')->nullable();
            $table->ipAddress('ip_address')->nullable();
            $table->text('user_agent')->nullable();
            $table->timestamp('created_at')->useCurrent();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('activity_logs');
        Schema::dropIfExists('subscription_payments');
        Schema::dropIfExists('tenant_subscriptions');
        Schema::dropIfExists('subscription_plans');
        Schema::dropIfExists('notification_logs');
        Schema::dropIfExists('notification_templates');
        Schema::dropIfExists('whatsapp_settings');
        Schema::dropIfExists('website_banners');
        Schema::dropIfExists('website_settings');
        Schema::dropIfExists('customer_memberships');
        Schema::dropIfExists('membership_plans');
        Schema::dropIfExists('promo_treatments');
        Schema::dropIfExists('promos');
        Schema::dropIfExists('transaction_items');
        Schema::dropIfExists('transactions');
        Schema::dropIfExists('booking_status_histories');
        Schema::dropIfExists('bookings');
        Schema::dropIfExists('customers');
        Schema::dropIfExists('staff_time_off');
        Schema::dropIfExists('staff_schedules');
        Schema::dropIfExists('operating_hours');
        Schema::dropIfExists('staff_treatments');
        Schema::dropIfExists('staff');
        Schema::dropIfExists('treatments');
        Schema::dropIfExists('treatment_categories');
        Schema::dropIfExists('tenant_users');
        Schema::dropIfExists('tenants');
    }
};
