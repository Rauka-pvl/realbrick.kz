<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('bitrix24_catalog_products', function (Blueprint $table) {
            if (! Schema::hasColumn('bitrix24_catalog_products', 'price_value')) {
                $table->decimal('price_value', 14, 2)->nullable()->after('active');
            }
            if (! Schema::hasColumn('bitrix24_catalog_products', 'price_currency')) {
                $table->string('price_currency', 10)->nullable()->after('price_value');
            }
        });
    }

    public function down(): void
    {
        Schema::table('bitrix24_catalog_products', function (Blueprint $table) {
            if (Schema::hasColumn('bitrix24_catalog_products', 'price_currency')) {
                $table->dropColumn('price_currency');
            }
            if (Schema::hasColumn('bitrix24_catalog_products', 'price_value')) {
                $table->dropColumn('price_value');
            }
        });
    }
};

