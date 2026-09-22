<?php

declare(strict_types=1);

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
        Schema::create('public_leads', function (Blueprint $table) {
            $table->uuid('id')->primary();
            $table->string('type', 32)->default('demo'); // demo, contact, pricing
            $table->string('name', 255);
            $table->string('company', 255);
            $table->string('work_email', 255)->index();
            $table->string('phone', 64)->nullable();
            $table->string('country', 100)->nullable();
            $table->string('organization_size', 64)->nullable();
            $table->string('hcm_requirements', 255)->nullable();
            $table->text('message')->nullable();
            $table->string('ip_address', 45)->nullable();
            $table->string('user_agent', 500)->nullable();
            $table->string('status', 32)->default('new'); // new, contacted, qualified, converted
            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('public_leads');
    }
};
