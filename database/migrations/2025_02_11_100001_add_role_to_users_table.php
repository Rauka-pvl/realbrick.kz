<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('users', function (Blueprint $table) {
            $table->string('role', 20)->default('dealer')->after('email_verified_at');
        });
        if (Schema::hasColumn('users', 'is_admin')) {
            DB::table('users')->where('is_admin', true)->update(['role' => 'admin']);
            Schema::table('users', fn (Blueprint $table) => $table->dropColumn('is_admin'));
        }
    }

    public function down(): void
    {
        Schema::table('users', function (Blueprint $table) {
            $table->boolean('is_admin')->default(false)->after('email_verified_at');
        });
        DB::table('users')->where('role', 'admin')->update(['is_admin' => true]);
        Schema::table('users', fn (Blueprint $table) => $table->dropColumn('role'));
    }
};
