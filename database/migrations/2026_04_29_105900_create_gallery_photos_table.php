<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('gallery_photos', function (Blueprint $table) {
            $table->id();
            $table->string('title')->nullable();
            $table->string('subtitle')->nullable();
            $table->string('image_path');
            $table->string('collection_type', 64)->index();
            $table->string('color', 64)->index();
            $table->boolean('is_featured')->default(false)->index();
            $table->boolean('is_active')->default(true)->index();
            $table->unsignedInteger('sort_order')->default(0)->index();
            $table->timestamps();
        });

        DB::table('gallery_photos')->insert([
            [
                'title' => 'Коллекция Europe',
                'subtitle' => 'Алматы',
                'image_path' => 'gallery/gallery-full.png',
                'collection_type' => 'Europe',
                'color' => 'Кора дуба',
                'is_featured' => true,
                'is_active' => true,
                'sort_order' => 10,
                'created_at' => now(),
                'updated_at' => now(),
            ],
            [
                'title' => null,
                'subtitle' => null,
                'image_path' => 'gallery/gallery-01.png',
                'collection_type' => 'Ultima',
                'color' => 'Графит',
                'is_featured' => false,
                'is_active' => true,
                'sort_order' => 20,
                'created_at' => now(),
                'updated_at' => now(),
            ],
            [
                'title' => null,
                'subtitle' => null,
                'image_path' => 'gallery/gallery-02.png',
                'collection_type' => 'Europe',
                'color' => 'Кирпичный',
                'is_featured' => false,
                'is_active' => true,
                'sort_order' => 30,
                'created_at' => now(),
                'updated_at' => now(),
            ],
            [
                'title' => null,
                'subtitle' => null,
                'image_path' => 'gallery/gallery-03.png',
                'collection_type' => 'DEPO',
                'color' => 'Графит',
                'is_featured' => false,
                'is_active' => true,
                'sort_order' => 40,
                'created_at' => now(),
                'updated_at' => now(),
            ],
            [
                'title' => null,
                'subtitle' => null,
                'image_path' => 'gallery/gallery-04.png',
                'collection_type' => 'Europe',
                'color' => 'Кора дуба',
                'is_featured' => false,
                'is_active' => true,
                'sort_order' => 50,
                'created_at' => now(),
                'updated_at' => now(),
            ],
            [
                'title' => null,
                'subtitle' => null,
                'image_path' => 'gallery/gallery-05.png',
                'collection_type' => 'Ultima',
                'color' => 'Слоновая кость',
                'is_featured' => false,
                'is_active' => true,
                'sort_order' => 60,
                'created_at' => now(),
                'updated_at' => now(),
            ],
            [
                'title' => null,
                'subtitle' => null,
                'image_path' => 'gallery/gallery-06.png',
                'collection_type' => 'DEPO',
                'color' => 'Кирпичный',
                'is_featured' => false,
                'is_active' => true,
                'sort_order' => 70,
                'created_at' => now(),
                'updated_at' => now(),
            ],
            [
                'title' => null,
                'subtitle' => null,
                'image_path' => 'gallery/gallery-07.png',
                'collection_type' => 'Europe',
                'color' => 'Слоновая кость',
                'is_featured' => false,
                'is_active' => true,
                'sort_order' => 80,
                'created_at' => now(),
                'updated_at' => now(),
            ],
        ]);
    }

    public function down(): void
    {
        Schema::dropIfExists('gallery_photos');
    }
};

