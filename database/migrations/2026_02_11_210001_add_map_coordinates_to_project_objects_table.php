<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('project_objects', function (Blueprint $table) {
            $table->decimal('map_lat', 10, 8)->nullable()->after('address_cadastral');
            $table->decimal('map_lng', 11, 8)->nullable()->after('map_lat');
        });
    }

    public function down(): void
    {
        Schema::table('project_objects', function (Blueprint $table) {
            $table->dropColumn(['map_lat', 'map_lng']);
        });
    }
};
