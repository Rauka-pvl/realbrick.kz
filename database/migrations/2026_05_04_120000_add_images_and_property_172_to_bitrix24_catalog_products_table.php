<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('bitrix24_catalog_products', function (Blueprint $table) {
            if (! Schema::hasColumn('bitrix24_catalog_products', 'image_url')) {
                $table->string('image_url', 2048)->nullable()->after('price_currency');
            }
            if (! Schema::hasColumn('bitrix24_catalog_products', 'gallery_json')) {
                $table->json('gallery_json')->nullable()->after('image_url');
            }
            if (! Schema::hasColumn('bitrix24_catalog_products', 'property_172')) {
                $table->text('property_172')->nullable()->after('property_186');
            }
        });
    }

    public function down(): void
    {
        Schema::table('bitrix24_catalog_products', function (Blueprint $table) {
            if (Schema::hasColumn('bitrix24_catalog_products', 'property_172')) {
                $table->dropColumn('property_172');
            }
            if (Schema::hasColumn('bitrix24_catalog_products', 'gallery_json')) {
                $table->dropColumn('gallery_json');
            }
            if (Schema::hasColumn('bitrix24_catalog_products', 'image_url')) {
                $table->dropColumn('image_url');
            }
        });
    }
};
