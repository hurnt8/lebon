<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('seller_bank_accounts', function (Blueprint $table) {
            $table->string('transfer_reference', 50)->nullable()->after('account_holder_name');
        });
    }

    public function down(): void
    {
        Schema::table('seller_bank_accounts', function (Blueprint $table) {
            $table->dropColumn('transfer_reference');
        });
    }
};
