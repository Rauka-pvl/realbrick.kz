<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        if (! Schema::hasTable('bitrix24_catalog_products')) {
            return;
        }

        if (Schema::hasColumn('bitrix24_catalog_products', 'property_186') && ! Schema::hasColumn('bitrix24_catalog_products', 'units_per_sq_or_lm')) {
            DB::statement('ALTER TABLE bitrix24_catalog_products CHANGE property_186 units_per_sq_or_lm VARCHAR(255) NULL');
        }
        if (Schema::hasColumn('bitrix24_catalog_products', 'property_164') && ! Schema::hasColumn('bitrix24_catalog_products', 'article')) {
            DB::statement('ALTER TABLE bitrix24_catalog_products CHANGE property_164 article VARCHAR(255) NULL');
        }
        if (Schema::hasColumn('bitrix24_catalog_products', 'property_172') && ! Schema::hasColumn('bitrix24_catalog_products', 'photo_property_raw')) {
            DB::statement('ALTER TABLE bitrix24_catalog_products CHANGE property_172 photo_property_raw TEXT NULL');
        }
        if (Schema::hasColumn('bitrix24_catalog_products', 'property_50') && ! Schema::hasColumn('bitrix24_catalog_products', 'size')) {
            DB::statement('ALTER TABLE bitrix24_catalog_products CHANGE property_50 size VARCHAR(255) NULL');
        }
        if (Schema::hasColumn('bitrix24_catalog_products', 'property_130') && ! Schema::hasColumn('bitrix24_catalog_products', 'pieces_per_pack')) {
            DB::statement('ALTER TABLE bitrix24_catalog_products CHANGE property_130 pieces_per_pack VARCHAR(255) NULL');
        }
    }

    public function down(): void
    {
        if (! Schema::hasTable('bitrix24_catalog_products')) {
            return;
        }

        if (Schema::hasColumn('bitrix24_catalog_products', 'units_per_sq_or_lm') && ! Schema::hasColumn('bitrix24_catalog_products', 'property_186')) {
            DB::statement('ALTER TABLE bitrix24_catalog_products CHANGE units_per_sq_or_lm property_186 VARCHAR(255) NULL');
        }
        if (Schema::hasColumn('bitrix24_catalog_products', 'article') && ! Schema::hasColumn('bitrix24_catalog_products', 'property_164')) {
            DB::statement('ALTER TABLE bitrix24_catalog_products CHANGE article property_164 VARCHAR(255) NULL');
        }
        if (Schema::hasColumn('bitrix24_catalog_products', 'photo_property_raw') && ! Schema::hasColumn('bitrix24_catalog_products', 'property_172')) {
            DB::statement('ALTER TABLE bitrix24_catalog_products CHANGE photo_property_raw property_172 TEXT NULL');
        }
        if (Schema::hasColumn('bitrix24_catalog_products', 'size') && ! Schema::hasColumn('bitrix24_catalog_products', 'property_50')) {
            DB::statement('ALTER TABLE bitrix24_catalog_products CHANGE size property_50 VARCHAR(255) NULL');
        }
        if (Schema::hasColumn('bitrix24_catalog_products', 'pieces_per_pack') && ! Schema::hasColumn('bitrix24_catalog_products', 'property_130')) {
            DB::statement('ALTER TABLE bitrix24_catalog_products CHANGE pieces_per_pack property_130 VARCHAR(255) NULL');
        }
    }
};
