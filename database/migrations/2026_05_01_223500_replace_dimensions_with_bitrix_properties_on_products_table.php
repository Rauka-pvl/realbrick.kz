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

        Schema::table('bitrix24_catalog_products', function (Blueprint $table) {
            if (Schema::hasColumn('bitrix24_catalog_products', 'length')) {
                $table->dropColumn('length');
            }
            if (Schema::hasColumn('bitrix24_catalog_products', 'height')) {
                $table->dropColumn('height');
            }
            if (Schema::hasColumn('bitrix24_catalog_products', 'width')) {
                $table->dropColumn('width');
            }
        });
    }

    public function down(): void
    {
        Schema::table('bitrix24_catalog_products', function (Blueprint $table) {
            if (! Schema::hasColumn('bitrix24_catalog_products', 'width')) {
                $table->decimal('width', 14, 2)->nullable()->after('price_currency');
            }
            if (! Schema::hasColumn('bitrix24_catalog_products', 'height')) {
                $table->decimal('height', 14, 2)->nullable()->after('width');
            }
            if (! Schema::hasColumn('bitrix24_catalog_products', 'length')) {
                $table->decimal('length', 14, 2)->nullable()->after('height');
            }
        });

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
