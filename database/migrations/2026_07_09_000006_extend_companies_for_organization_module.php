<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('companies', function (Blueprint $table) {
            $table->string('ntn', 80)->nullable()->after('registration_number');
            $table->string('strn', 80)->nullable()->after('ntn');
            $table->string('industry')->nullable()->after('strn');
            $table->string('company_size', 80)->nullable()->after('industry');
            $table->string('logo_path')->nullable()->after('company_size');
            $table->string('mobile', 40)->nullable()->after('phone');
            $table->string('fax', 40)->nullable()->after('mobile');
            $table->string('fiscal_year_start', 5)->default('01-01')->after('currency');
            $table->text('address')->nullable()->after('fiscal_year_start');
            $table->string('country', 120)->nullable()->after('address');
            $table->string('state', 120)->nullable()->after('country');
            $table->string('city', 120)->nullable()->after('state');
            $table->string('postal_code', 40)->nullable()->after('city');
            $table->string('status', 40)->default('active')->after('postal_code')->index();
        });
    }

    public function down(): void
    {
        Schema::table('companies', function (Blueprint $table) {
            $table->dropColumn([
                'ntn',
                'strn',
                'industry',
                'company_size',
                'logo_path',
                'mobile',
                'fax',
                'fiscal_year_start',
                'address',
                'country',
                'state',
                'city',
                'postal_code',
                'status',
            ]);
        });
    }
};
