<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        if (! Schema::hasTable('bitrix24_catalog_sections')) {
            return;
        }

        Schema::table('bitrix24_catalog_sections', function (Blueprint $table) {
            if (! Schema::hasColumn('bitrix24_catalog_sections', 'image_url')) {
                $table->string('image_url', 2048)->nullable()->after('path_parts');
            }
        });
    }

    public function down(): void
    {
        if (! Schema::hasTable('bitrix24_catalog_sections')) {
            return;
        }

        Schema::table('bitrix24_catalog_sections', function (Blueprint $table) {
            if (Schema::hasColumn('bitrix24_catalog_sections', 'image_url')) {
                $table->dropColumn('image_url');
            }
        });
    }
};
