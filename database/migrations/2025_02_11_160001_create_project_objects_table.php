<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('project_objects', function (Blueprint $table) {
            $table->id();
            $table->foreignId('dealer_id')->constrained()->cascadeOnDelete();
            $table->foreignId('client_id')->nullable()->constrained()->nullOnDelete();

            $table->string('manager_name')->nullable();
            $table->string('manager_position')->nullable();
            $table->string('manager_phone', 50)->nullable();
            $table->string('manager_email')->nullable();

            $table->string('contact_name')->nullable();
            $table->string('contact_phone', 50)->nullable();
            $table->string('contact_email')->nullable();

            $table->string('address_country')->nullable();
            $table->string('address_locality')->nullable();
            $table->string('address_street')->nullable();
            $table->string('address_house', 100)->nullable();
            $table->string('address_cadastral', 100)->nullable();

            $table->string('name')->nullable();

            $table->string('architect_org')->nullable();
            $table->string('architect_phone', 50)->nullable();
            $table->string('architect_contact')->nullable();
            $table->string('architect_email')->nullable();

            $table->string('investor_contact')->nullable();
            $table->string('investor_phone', 50)->nullable();

            $table->text('competing_materials')->nullable();

            $table->string('stage', 50)->default('negotiations');

            $table->date('planned_delivery_date')->nullable();

            $table->string('title_page_path')->nullable();
            $table->string('visualization_path')->nullable();

            $table->timestamps();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('project_objects');
    }
};
