<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('project_objects', function (Blueprint $table) {
            $table->string('moderation_status', 32)->nullable()->after('stage');
            $table->foreignId('duplicate_of_project_object_id')
                ->nullable()
                ->after('moderation_status')
                ->constrained('project_objects')
                ->nullOnDelete();
        });
    }

    public function down(): void
    {
        Schema::table('project_objects', function (Blueprint $table) {
            $table->dropConstrainedForeignId('duplicate_of_project_object_id');
            $table->dropColumn('moderation_status');
        });
    }
};
