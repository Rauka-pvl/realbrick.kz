<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('dealers', function (Blueprint $table) {
            $table->foreignId('user_id')->nullable()->after('id')->constrained()->nullOnDelete();
            $table->string('bin', 50)->nullable()->after('company');
            $table->string('contact_person_name')->nullable()->after('bin');
            $table->string('contact_person_phone', 50)->nullable()->after('contact_person_name');
            $table->text('legal_address')->nullable()->after('city');
            $table->text('requisites')->nullable()->after('legal_address');
            $table->string('instagram')->nullable()->after('email');
        });
        if (Schema::hasColumn('dealers', 'address')) {
            DB::table('dealers')->whereNotNull('address')->update(['legal_address' => DB::raw('address')]);
            Schema::table('dealers', fn (Blueprint $table) => $table->dropColumn('address'));
        }
        if (Schema::hasColumn('dealers', 'phone')) {
            Schema::table('dealers', fn (Blueprint $table) => $table->dropColumn('phone'));
        }
    }

    public function down(): void
    {
        Schema::table('dealers', function (Blueprint $table) {
            $table->string('phone', 50)->nullable()->after('company');
            $table->string('address')->nullable()->after('city');
        });
        DB::table('dealers')->whereNotNull('legal_address')->update(['address' => DB::raw('legal_address')]);
        Schema::table('dealers', function (Blueprint $table) {
            $table->dropForeign(['user_id']);
            $table->dropColumn(['bin', 'contact_person_name', 'contact_person_phone', 'legal_address', 'requisites', 'instagram']);
        });
    }
};
