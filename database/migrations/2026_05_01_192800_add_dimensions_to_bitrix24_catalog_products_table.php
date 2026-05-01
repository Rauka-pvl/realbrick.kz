<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('bitrix24_catalog_products', function (Blueprint $table) {
            if (! Schema::hasColumn('bitrix24_catalog_products', 'property_50')) {
                $table->string('property_50')->nullable()->after('price_currency');
            }
            if (! Schema::hasColumn('bitrix24_catalog_products', 'property_186')) {
                $table->string('property_186')->nullable()->after('property_50');
            }
        });
    }

    public function down(): void
    {
        Schema::table('bitrix24_catalog_products', function (Blueprint $table) {
            if (Schema::hasColumn('bitrix24_catalog_products', 'property_186')) {
                $table->dropColumn('property_186');
            }
            if (Schema::hasColumn('bitrix24_catalog_products', 'property_50')) {
                $table->dropColumn('property_50');
            }
        });
    }
};
