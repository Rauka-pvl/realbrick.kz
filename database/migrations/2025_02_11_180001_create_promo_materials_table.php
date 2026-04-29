<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('promo_materials', function (Blueprint $table) {
            $table->id();
            $table->string('name'); // отображаемое название
            $table->string('file_path'); // путь в storage
            $table->string('file_name'); // оригинальное имя файла
            $table->unsignedBigInteger('file_size')->default(0);
            $table->string('mime_type', 100)->nullable();
            $table->timestamps();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('promo_materials');
    }
};
