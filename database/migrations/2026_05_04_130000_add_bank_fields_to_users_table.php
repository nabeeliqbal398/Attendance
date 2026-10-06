<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('users', function (Blueprint $table) {
            $table->string('bank_name', 100)->nullable()->after('cnic');
            $table->string('bank_account_title', 150)->nullable()->after('bank_name');
            $table->string('bank_account_number', 50)->nullable()->after('bank_account_title');
            $table->string('iban', 34)->nullable()->after('bank_account_number');
        });
    }

    public function down(): void
    {
        Schema::table('users', function (Blueprint $table) {
            $table->dropColumn(['bank_name', 'bank_account_title', 'bank_account_number', 'iban']);
        });
    }
};
