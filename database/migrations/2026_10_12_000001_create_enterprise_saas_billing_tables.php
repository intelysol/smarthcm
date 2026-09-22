<?php

declare(strict_types=1);

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        // 1. Commercial Products
        Schema::create('billing_products', function (Blueprint $table) {
            $table->char('id', 36)->primary();
            $table->string('name', 150);
            $table->string('code', 80)->unique();
            $table->text('description')->nullable();
            $table->boolean('is_active')->default(true);
            $table->json('metadata')->nullable();
            $table->timestamps();
        });

        // 2. Product Versions
        Schema::create('billing_product_versions', function (Blueprint $table) {
            $table->char('id', 36)->primary();
            $table->char('product_id', 36);
            $table->string('version', 50);
            $table->boolean('is_active')->default(true);
            $table->text('release_notes')->nullable();
            $table->timestamps();

            $table->foreign('product_id')->references('id')->on('billing_products')->cascadeOnDelete();
            $table->unique(['product_id', 'version'], 'bpv_product_version_unique');
        });

        // 3. Subscription Plans
        Schema::create('billing_plans', function (Blueprint $table) {
            $table->char('id', 36)->primary();
            $table->char('product_id', 36);
            $table->char('version_id', 36)->nullable();
            $table->string('name', 150);
            $table->string('code', 80)->unique();
            $table->text('description')->nullable();
            $table->string('billing_model', 40)->default('flat'); // flat, per_seat, tiered, volume, hybrid
            $table->string('billing_interval', 20)->default('monthly'); // monthly, quarterly, semiannual, annual
            $table->string('currency', 3)->default('USD');
            $table->decimal('base_price', 12, 2)->default(0.00);
            $table->decimal('setup_fee', 12, 2)->default(0.00);
            $table->unsignedInteger('trial_period_days')->default(0);
            $table->unsignedInteger('min_commitment_months')->default(0);
            $table->unsignedInteger('max_quantity')->nullable();
            $table->string('status', 20)->default('active'); // draft, active, archived
            $table->unsignedInteger('version')->default(1);
            $table->json('metadata')->nullable();
            $table->timestamps();

            $table->foreign('product_id')->references('id')->on('billing_products')->cascadeOnDelete();
            $table->foreign('version_id')->references('id')->on('billing_product_versions')->nullOnDelete();
            $table->index(['status', 'currency'], 'bp_status_currency_idx');
        });

        // 4. Commercial Price Components
        Schema::create('billing_prices', function (Blueprint $table) {
            $table->char('id', 36)->primary();
            $table->char('plan_id', 36);
            $table->string('component_key', 80); // base_platform, employee_seat, payroll_module, ai_usage, storage_gb, api_requests
            $table->string('pricing_model', 40)->default('flat'); // flat, per_unit, tiered, volume, graduated, overage
            $table->string('unit_name', 50)->default('seat');
            $table->decimal('unit_price', 12, 4)->default(0.0000);
            $table->unsignedInteger('min_quantity')->default(0);
            $table->unsignedInteger('max_quantity')->nullable();
            $table->unsignedInteger('included_quantity')->default(0);
            $table->decimal('overage_price', 12, 4)->default(0.0000);
            $table->string('currency', 3)->default('USD');
            $table->json('tiers_config')->nullable(); // For graduated/volume tiered pricing brackets
            $table->timestamps();

            $table->foreign('plan_id')->references('id')->on('billing_plans')->cascadeOnDelete();
            $table->unique(['plan_id', 'component_key'], 'bp_plan_component_unique');
        });

        // 5. Commercial Plan Entitlements
        Schema::create('billing_plan_entitlements', function (Blueprint $table) {
            $table->char('id', 36)->primary();
            $table->char('plan_id', 36);
            $table->string('entitlement_key', 80); // employee_limit, storage_limit_gb, api_limit_monthly, ai_token_limit_monthly, payroll_enabled, etc.
            $table->string('entitlement_type', 30)->default('limit'); // limit, boolean, tier, feature
            $table->bigInteger('limit_value')->nullable();
            $table->boolean('is_enabled')->default(true);
            $table->json('metadata')->nullable();
            $table->timestamps();

            $table->foreign('plan_id')->references('id')->on('billing_plans')->cascadeOnDelete();
            $table->unique(['plan_id', 'entitlement_key'], 'bpe_plan_entitlement_unique');
        });

        // 6. Tenant Subscriptions
        Schema::create('billing_subscriptions', function (Blueprint $table) {
            $table->char('id', 36)->primary();
            $table->char('tenant_id', 36);
            $table->char('plan_id', 36);
            $table->string('status', 30)->default('active'); // trialing, active, past_due, paused, suspended, cancelled, expired
            $table->unsignedInteger('quantity')->default(1); // seat or license quantity
            $table->string('currency', 3)->default('USD');
            $table->timestamp('starts_at');
            $table->timestamp('ends_at')->nullable();
            $table->timestamp('trial_ends_at')->nullable();
            $table->timestamp('grace_ends_at')->nullable();
            $table->unsignedSmallInteger('billing_anchor_day')->default(1);
            $table->timestamp('current_cycle_start')->nullable();
            $table->timestamp('current_cycle_end')->nullable();
            $table->timestamp('paused_at')->nullable();
            $table->timestamp('suspended_at')->nullable();
            $table->timestamp('cancelled_at')->nullable();
            $table->string('cancellation_reason')->nullable();
            $table->boolean('auto_renew')->default(true);
            $table->json('metadata')->nullable();
            $table->timestamps();

            $table->foreign('tenant_id')->references('id')->on('tenants')->cascadeOnDelete();
            $table->foreign('plan_id')->references('id')->on('billing_plans')->cascadeOnDelete();
            $table->index(['tenant_id', 'status'], 'bs_tenant_status_idx');
        });

        // 7. Subscription Items
        Schema::create('billing_subscription_items', function (Blueprint $table) {
            $table->char('id', 36)->primary();
            $table->char('subscription_id', 36);
            $table->char('price_id', 36);
            $table->unsignedInteger('quantity')->default(1);
            $table->decimal('unit_price', 12, 4)->default(0.0000);
            $table->string('status', 20)->default('active');
            $table->json('metadata')->nullable();
            $table->timestamps();

            $table->foreign('subscription_id')->references('id')->on('billing_subscriptions')->cascadeOnDelete();
            $table->foreign('price_id')->references('id')->on('billing_prices')->cascadeOnDelete();
        });

        // 8. Usage Meters
        Schema::create('billing_usage_meters', function (Blueprint $table) {
            $table->char('id', 36)->primary();
            $table->string('meter_key', 80)->unique(); // active_employees, user_seats, api_calls, ai_tokens, storage_mb
            $table->string('name', 120);
            $table->string('aggregation_type', 30)->default('count'); // count, sum, max, latest
            $table->string('reset_frequency', 30)->default('monthly'); // daily, monthly, never
            $table->text('description')->nullable();
            $table->timestamps();
        });

        // 9. Raw Usage Events (Idempotent)
        Schema::create('billing_usage_events', function (Blueprint $table) {
            $table->char('id', 36)->primary();
            $table->char('tenant_id', 36);
            $table->string('meter_key', 80);
            $table->decimal('quantity', 14, 4)->default(1.0000);
            $table->timestamp('recorded_at');
            $table->string('idempotency_key', 128)->unique();
            $table->string('source', 80)->default('system');
            $table->json('metadata')->nullable();
            $table->timestamp('created_at')->useCurrent();

            $table->foreign('tenant_id')->references('id')->on('tenants')->cascadeOnDelete();
            $table->index(['tenant_id', 'meter_key', 'recorded_at'], 'bue_tenant_meter_date_idx');
        });

        // 10. Usage Snapshots (Pre-aggregated)
        Schema::create('billing_usage_snapshots', function (Blueprint $table) {
            $table->char('id', 36)->primary();
            $table->char('tenant_id', 36);
            $table->string('meter_key', 80);
            $table->timestamp('period_start');
            $table->timestamp('period_end');
            $table->decimal('total_quantity', 14, 4)->default(0.0000);
            $table->decimal('billable_quantity', 14, 4)->default(0.0000);
            $table->timestamp('snapshotted_at');
            $table->timestamps();

            $table->foreign('tenant_id')->references('id')->on('tenants')->cascadeOnDelete();
            $table->unique(['tenant_id', 'meter_key', 'period_start', 'period_end'], 'bus_tenant_meter_period_unique');
        });

        // 11. Commercial Invoices
        Schema::create('billing_invoices', function (Blueprint $table) {
            $table->char('id', 36)->primary();
            $table->string('invoice_number', 50)->unique();
            $table->char('tenant_id', 36);
            $table->char('subscription_id', 36)->nullable();
            $table->timestamp('billing_period_start')->nullable();
            $table->timestamp('billing_period_end')->nullable();
            $table->date('issue_date');
            $table->date('due_date');
            $table->string('currency', 3)->default('USD');
            $table->decimal('subtotal', 12, 2)->default(0.00);
            $table->decimal('discount_amount', 12, 2)->default(0.00);
            $table->decimal('tax_amount', 12, 2)->default(0.00);
            $table->decimal('credit_amount', 12, 2)->default(0.00);
            $table->decimal('total_amount', 12, 2)->default(0.00);
            $table->decimal('amount_paid', 12, 2)->default(0.00);
            $table->decimal('balance_due', 12, 2)->default(0.00);
            $table->string('status', 30)->default('draft'); // draft, issued, partially_paid, paid, past_due, void, uncollectible
            $table->text('notes')->nullable();
            $table->json('metadata')->nullable();
            $table->timestamps();

            $table->foreign('tenant_id')->references('id')->on('tenants')->cascadeOnDelete();
            $table->foreign('subscription_id')->references('id')->on('billing_subscriptions')->nullOnDelete();
            $table->index(['tenant_id', 'status', 'due_date'], 'bi_tenant_status_due_idx');
        });

        // 12. Invoice Items (Preserves historical pricing)
        Schema::create('billing_invoice_items', function (Blueprint $table) {
            $table->char('id', 36)->primary();
            $table->char('invoice_id', 36);
            $table->string('description', 255);
            $table->decimal('quantity', 12, 4)->default(1.0000);
            $table->string('unit_name', 50)->default('unit');
            $table->decimal('unit_price', 12, 4)->default(0.0000);
            $table->decimal('amount', 12, 2)->default(0.00);
            $table->string('pricing_source', 50)->nullable();
            $table->json('metadata')->nullable();
            $table->timestamps();

            $table->foreign('invoice_id')->references('id')->on('billing_invoices')->cascadeOnDelete();
        });

        // 13. Payment Transactions
        Schema::create('billing_payments', function (Blueprint $table) {
            $table->char('id', 36)->primary();
            $table->string('payment_number', 50)->unique();
            $table->char('tenant_id', 36);
            $table->char('invoice_id', 36);
            $table->string('provider', 50)->default('mock'); // mock, stripe, paypal, bank_transfer, manual
            $table->string('provider_transaction_id', 150)->nullable()->index();
            $table->decimal('amount', 12, 2);
            $table->string('currency', 3)->default('USD');
            $table->string('status', 30)->default('pending'); // pending, authorized, succeeded, failed, cancelled, refunded, partially_refunded
            $table->string('payment_method', 50)->default('card'); // card, bank_transfer, credit_balance, wallet
            $table->string('failure_reason')->nullable();
            $table->timestamp('initiated_at')->nullable();
            $table->timestamp('completed_at')->nullable();
            $table->json('metadata')->nullable();
            $table->timestamps();

            $table->foreign('tenant_id')->references('id')->on('tenants')->cascadeOnDelete();
            $table->foreign('invoice_id')->references('id')->on('billing_invoices')->cascadeOnDelete();
            $table->index(['tenant_id', 'status'], 'bp_tenant_status_idx');
        });

        // 14. Payment Refunds
        Schema::create('billing_refunds', function (Blueprint $table) {
            $table->char('id', 36)->primary();
            $table->string('refund_number', 50)->unique();
            $table->char('payment_id', 36);
            $table->char('invoice_id', 36);
            $table->char('tenant_id', 36);
            $table->decimal('amount', 12, 2);
            $table->string('currency', 3)->default('USD');
            $table->string('reason', 255)->nullable();
            $table->string('status', 30)->default('completed'); // pending, completed, failed
            $table->string('provider_refund_id', 150)->nullable();
            $table->json('metadata')->nullable();
            $table->timestamps();

            $table->foreign('payment_id')->references('id')->on('billing_payments')->cascadeOnDelete();
            $table->foreign('invoice_id')->references('id')->on('billing_invoices')->cascadeOnDelete();
            $table->foreign('tenant_id')->references('id')->on('tenants')->cascadeOnDelete();
        });

        // 15. Tenant Credits (Wallet Balance)
        Schema::create('billing_credits', function (Blueprint $table) {
            $table->char('id', 36)->primary();
            $table->char('tenant_id', 36);
            $table->decimal('amount', 12, 2);
            $table->decimal('balance', 12, 2);
            $table->string('currency', 3)->default('USD');
            $table->string('reason', 150);
            $table->string('status', 20)->default('active'); // active, depleted, expired, cancelled
            $table->timestamp('expires_at')->nullable();
            $table->timestamps();

            $table->foreign('tenant_id')->references('id')->on('tenants')->cascadeOnDelete();
            $table->index(['tenant_id', 'status'], 'bc_tenant_status_idx');
        });

        // 16. Credit Transactions (Immutable Ledger)
        Schema::create('billing_credit_transactions', function (Blueprint $table) {
            $table->char('id', 36)->primary();
            $table->char('credit_id', 36);
            $table->char('invoice_id', 36)->nullable();
            $table->decimal('amount', 12, 2);
            $table->string('type', 20); // issued, applied, refund_credit, expired, cancelled
            $table->string('description', 255)->nullable();
            $table->timestamps();

            $table->foreign('credit_id')->references('id')->on('billing_credits')->cascadeOnDelete();
            $table->foreign('invoice_id')->references('id')->on('billing_invoices')->nullOnDelete();
        });

        // 17. Discounts and Coupons
        Schema::create('billing_discounts', function (Blueprint $table) {
            $table->char('id', 36)->primary();
            $table->string('code', 50)->unique();
            $table->string('name', 120);
            $table->string('discount_type', 30)->default('percentage'); // percentage, fixed_amount
            $table->decimal('value', 12, 2);
            $table->unsignedInteger('max_redemptions')->nullable();
            $table->unsignedInteger('redeemed_count')->default(0);
            $table->timestamp('starts_at')->nullable();
            $table->timestamp('expires_at')->nullable();
            $table->boolean('is_active')->default(true);
            $table->json('metadata')->nullable(); // plan restrictions, min spend, etc.
            $table->timestamps();
        });

        // 18. Discount Redemptions
        Schema::create('billing_discount_redemptions', function (Blueprint $table) {
            $table->char('id', 36)->primary();
            $table->char('discount_id', 36);
            $table->char('tenant_id', 36);
            $table->char('invoice_id', 36)->nullable();
            $table->decimal('amount_discounted', 12, 2);
            $table->timestamp('redeemed_at');
            $table->timestamps();

            $table->foreign('discount_id')->references('id')->on('billing_discounts')->cascadeOnDelete();
            $table->foreign('tenant_id')->references('id')->on('tenants')->cascadeOnDelete();
            $table->foreign('invoice_id')->references('id')->on('billing_invoices')->nullOnDelete();
        });

        // 19. Tax Records & Jurisdictions
        Schema::create('billing_tax_records', function (Blueprint $table) {
            $table->char('id', 36)->primary();
            $table->string('country_code', 2);
            $table->string('region_code', 20)->nullable();
            $table->string('tax_name', 80); // VAT, GST, Sales Tax, Provincial Sales Tax
            $table->decimal('rate_percent', 6, 3)->default(0.000);
            $table->boolean('is_compound')->default(false);
            $table->boolean('is_active')->default(true);
            $table->timestamps();

            $table->index(['country_code', 'region_code', 'is_active'], 'btr_country_region_idx');
        });

        // 20. Provider Accounts
        Schema::create('billing_provider_accounts', function (Blueprint $table) {
            $table->char('id', 36)->primary();
            $table->string('provider_code', 50)->unique(); // mock, stripe_sandbox, bank_transfer
            $table->string('name', 120);
            $table->boolean('is_enabled')->default(true);
            $table->boolean('is_sandbox')->default(true);
            $table->json('config')->nullable(); // API public/secret keys or webhook secrets (encrypted)
            $table->timestamps();
        });

        // 21. Reconciliation Records
        Schema::create('billing_reconciliation_records', function (Blueprint $table) {
            $table->char('id', 36)->primary();
            $table->date('reconciliation_date');
            $table->char('invoice_id', 36)->nullable();
            $table->char('payment_id', 36)->nullable();
            $table->string('status', 30)->default('discrepancy'); // matched, discrepancy, resolved
            $table->string('discrepancy_type', 50)->nullable(); // amount_mismatch, missing_payment, duplicate_payment, orphan_transaction
            $table->decimal('discrepancy_amount', 12, 2)->default(0.00);
            $table->text('notes')->nullable();
            $table->timestamp('resolved_at')->nullable();
            $table->unsignedBigInteger('resolved_by')->nullable();
            $table->timestamps();

            $table->foreign('invoice_id')->references('id')->on('billing_invoices')->nullOnDelete();
            $table->foreign('payment_id')->references('id')->on('billing_payments')->nullOnDelete();
            $table->foreign('resolved_by')->references('id')->on('users')->nullOnDelete();
            $table->index(['reconciliation_date', 'status'], 'brr_date_status_idx');
        });

        // 22. Commercial Audit Events
        Schema::create('billing_events', function (Blueprint $table) {
            $table->char('id', 36)->primary();
            $table->char('tenant_id', 36)->nullable();
            $table->string('event_type', 100);
            $table->json('payload')->nullable();
            $table->unsignedBigInteger('actor_id')->nullable();
            $table->string('ip_address', 45)->nullable();
            $table->timestamp('created_at')->useCurrent();

            $table->foreign('tenant_id')->references('id')->on('tenants')->cascadeOnDelete();
            $table->foreign('actor_id')->references('id')->on('users')->nullOnDelete();
            $table->index(['tenant_id', 'event_type', 'created_at'], 'be_tenant_event_idx');
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('billing_events');
        Schema::dropIfExists('billing_reconciliation_records');
        Schema::dropIfExists('billing_provider_accounts');
        Schema::dropIfExists('billing_tax_records');
        Schema::dropIfExists('billing_discount_redemptions');
        Schema::dropIfExists('billing_discounts');
        Schema::dropIfExists('billing_credit_transactions');
        Schema::dropIfExists('billing_credits');
        Schema::dropIfExists('billing_refunds');
        Schema::dropIfExists('billing_payments');
        Schema::dropIfExists('billing_invoice_items');
        Schema::dropIfExists('billing_invoices');
        Schema::dropIfExists('billing_usage_snapshots');
        Schema::dropIfExists('billing_usage_events');
        Schema::dropIfExists('billing_usage_meters');
        Schema::dropIfExists('billing_subscription_items');
        Schema::dropIfExists('billing_subscriptions');
        Schema::dropIfExists('billing_plan_entitlements');
        Schema::dropIfExists('billing_prices');
        Schema::dropIfExists('billing_plans');
        Schema::dropIfExists('billing_product_versions');
        Schema::dropIfExists('billing_products');
    }
};
