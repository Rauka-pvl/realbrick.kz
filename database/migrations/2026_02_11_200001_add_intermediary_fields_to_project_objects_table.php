<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('project_objects', function (Blueprint $table) {
            $table->string('intermediary_type', 50)->nullable()->after('investor_phone');
            $table->string('intermediary_name')->nullable()->after('intermediary_type');
            $table->string('intermediary_contact')->nullable()->after('intermediary_name');
            $table->string('intermediary_position')->nullable()->after('intermediary_contact');
            $table->decimal('intermediary_percent', 5, 2)->nullable()->after('intermediary_position');
        });
    }

    public function down(): void
    {
        Schema::table('project_objects', function (Blueprint $table) {
            $table->dropColumn([
                'intermediary_type',
                'intermediary_name',
                'intermediary_contact',
                'intermediary_position',
                'intermediary_percent',
            ]);
        });
    }
};
