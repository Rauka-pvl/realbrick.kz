<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        if (Schema::hasTable('leads')) {
            if (! Schema::hasColumn('leads', 'bitrix_lead_id')) {
                Schema::table('leads', function (Blueprint $table) {
                    $table->unsignedBigInteger('bitrix_lead_id')->nullable()->after('comment');
                });
            }

            return;
        }

        Schema::create('leads', function (Blueprint $table) {
            $table->id();
            $table->string('name');
            $table->string('phone', 50);
            $table->text('comment')->nullable();
            $table->unsignedBigInteger('bitrix_lead_id')->nullable();
            $table->timestamps();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('leads');
    }
};
