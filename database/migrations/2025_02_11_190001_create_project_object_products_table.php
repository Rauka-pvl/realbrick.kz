<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('project_object_products', function (Blueprint $table) {
            $table->id();
            $table->foreignId('project_object_id')->constrained('project_objects')->cascadeOnDelete();
            $table->string('bitrix_product_id', 50); // id из каталога Bitrix24
            $table->string('product_name'); // название на момент выбора
            $table->decimal('quantity', 12, 2)->default(1);
            $table->timestamps();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('project_object_products');
    }
};
