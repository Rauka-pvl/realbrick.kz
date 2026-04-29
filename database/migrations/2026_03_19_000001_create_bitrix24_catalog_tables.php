<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('bitrix24_catalog_sections', function (Blueprint $table) {
            $table->id();
            $table->unsignedInteger('bitrix_id')->unique();
            $table->string('name');
            $table->unsignedInteger('parent_bitrix_id')->default(0);
            $table->json('path_parts')->nullable(); // ['Каталог', 'Раздел', 'Подраздел']
            $table->boolean('excluded')->default(false); // ветка исключена (Модная одежда и т.п.)
            $table->timestamp('synced_at')->nullable();
        });

        Schema::create('bitrix24_catalog_products', function (Blueprint $table) {
            $table->id();
            $table->unsignedInteger('bitrix_id')->unique();
            $table->string('name');
            $table->unsignedInteger('section_bitrix_id')->nullable();
            $table->json('path_parts')->nullable(); // ['Каталог', 'Раздел', 'Товар']
            $table->boolean('active')->default(true);
            $table->timestamp('synced_at')->nullable();

            $table->index('section_bitrix_id');
            $table->index('name');
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('bitrix24_catalog_products');
        Schema::dropIfExists('bitrix24_catalog_sections');
    }
};
