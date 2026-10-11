<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('estimate_number_sequences', function (Blueprint $table): void {
            $table->unsignedSmallInteger('year')->primary();
            $table->unsignedInteger('last_number')->default(0);
            $table->timestamps();
        });

        Schema::create('estimate_catalogue_items', function (Blueprint $table): void {
            $table->id();
            $table->string('name');
            $table->string('name_normalized')->unique();
            $table->text('description')->nullable();
            $table->decimal('default_rate', 12, 2)->default(0);
            $table->string('rate_basis', 20);
            $table->decimal('actual_cost_rate', 12, 2)->default(0);
            $table->boolean('is_active')->default(true);
            $table->timestamps();
            $table->softDeletes();
        });

        Schema::create('estimates', function (Blueprint $table): void {
            $table->id();
            $table->string('number', 20)->unique();
            $table->string('status', 20);
            $table->date('estimate_date');
            $table->date('valid_until');
            $table->foreignId('customer_id')->nullable()->constrained('customers')->nullOnDelete();
            $table->string('customer_name');
            $table->string('customer_mobile', 30);
            $table->foreignId('event_type_id')->nullable()->constrained('event_types')->nullOnDelete();
            $table->string('event_name')->nullable();
            $table->string('event_dates')->nullable();
            $table->string('venue')->nullable();
            $table->string('city')->nullable();
            $table->text('payment_terms')->nullable();
            $table->text('payment_schedule')->nullable();
            $table->text('notes')->nullable();
            $table->text('special_notes')->nullable();
            $table->decimal('discount_amount', 12, 2)->default(0);
            $table->decimal('additional_charges', 12, 2)->default(0);
            $table->decimal('tax_rate', 8, 2)->default(0);
            $table->string('tax_label')->nullable();
            $table->decimal('tax_amount', 12, 2)->default(0);
            $table->decimal('subtotal', 12, 2)->default(0);
            $table->decimal('grand_total', 12, 2)->default(0);
            $table->decimal('advance_amount', 12, 2)->default(0);
            $table->decimal('balance_due', 12, 2)->default(0);
            $table->string('currency', 10)->default('INR');
            $table->string('issued_company_name')->nullable();
            $table->string('issued_company_tagline')->nullable();
            $table->string('issued_phone')->nullable();
            $table->string('issued_email')->nullable();
            $table->text('issued_address')->nullable();
            $table->string('issued_website')->nullable();
            $table->string('issued_logo_path')->nullable();
            $table->foreignId('created_by')->nullable()->constrained('users')->nullOnDelete();
            $table->foreignId('updated_by')->nullable()->constrained('users')->nullOnDelete();
            $table->timestamps();
            $table->softDeletes();

            $table->index(['status', 'valid_until']);
            $table->index('customer_name');
            $table->index('estimate_date');
        });

        Schema::create('estimate_lines', function (Blueprint $table): void {
            $table->id();
            $table->foreignId('estimate_id')->constrained('estimates')->cascadeOnDelete();
            $table->foreignId('catalogue_item_id')->nullable()->constrained('estimate_catalogue_items')->nullOnDelete();
            $table->unsignedInteger('sort_order')->default(0);
            $table->string('name');
            $table->text('description')->nullable();
            $table->decimal('qty', 12, 2);
            $table->decimal('rate', 12, 2);
            $table->decimal('days', 8, 2)->nullable();
            $table->string('rate_basis', 20);
            $table->decimal('amount', 12, 2);
            $table->decimal('actual_cost_rate', 12, 2)->default(0);
            $table->decimal('actual_cost', 12, 2)->default(0);
            $table->timestamps();

            $table->index(['estimate_id', 'sort_order']);
        });

        Schema::create('estimate_direct_expenses', function (Blueprint $table): void {
            $table->id();
            $table->foreignId('estimate_id')->constrained('estimates')->cascadeOnDelete();
            $table->string('type', 30);
            $table->string('description');
            $table->decimal('amount', 12, 2);
            $table->unsignedInteger('sort_order')->default(0);
            $table->timestamps();
        });

        Schema::create('estimate_audits', function (Blueprint $table): void {
            $table->id();
            $table->foreignId('estimate_id')->constrained('estimates')->cascadeOnDelete();
            $table->foreignId('user_id')->nullable()->constrained('users')->nullOnDelete();
            $table->string('action', 40);
            $table->json('changes')->nullable();
            $table->timestamps();

            $table->index(['estimate_id', 'created_at']);
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('estimate_audits');
        Schema::dropIfExists('estimate_direct_expenses');
        Schema::dropIfExists('estimate_lines');
        Schema::dropIfExists('estimates');
        Schema::dropIfExists('estimate_catalogue_items');
        Schema::dropIfExists('estimate_number_sequences');
    }
};
