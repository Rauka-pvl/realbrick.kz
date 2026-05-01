<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('bitrix24_catalog_products', function (Blueprint $table) {
            if (! Schema::hasColumn('bitrix24_catalog_products', 'property_130')) {
                $table->string('property_130')->nullable()->after('property_50');
            }
        });
    }

    public function down(): void
    {
        Schema::table('bitrix24_catalog_products', function (Blueprint $table) {
            if (Schema::hasColumn('bitrix24_catalog_products', 'property_130')) {
                $table->dropColumn('property_130');
            }
        });
    }
};
