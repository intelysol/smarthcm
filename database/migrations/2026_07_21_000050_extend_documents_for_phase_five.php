<?php
use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;
return new class extends Migration {
    public function up(): void {
        Schema::table('documents', function (Blueprint $table): void { $table->string('module', 60)->nullable()->index(); $table->string('related_type', 120)->nullable(); $table->string('related_id', 80)->nullable(); $table->string('status', 30)->default('active')->index(); $table->string('classification', 40)->nullable(); $table->boolean('legal_hold')->default(false)->index(); $table->unsignedInteger('current_version')->default(1); $table->index(['tenant_id', 'module', 'status']); });
        Schema::table('document_versions', function (Blueprint $table): void { $table->longText('extracted_text')->nullable(); });
    }
    public function down(): void { Schema::table('document_versions', fn (Blueprint $table) => $table->dropColumn('extracted_text')); Schema::table('documents', fn (Blueprint $table) => $table->dropColumn(['module', 'related_type', 'related_id', 'status', 'classification', 'legal_hold', 'current_version'])); }
};
