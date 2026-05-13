<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration {
    public function up(): void
    {
        Schema::table('verification_results', function (Blueprint $table): void {
            $table->timestamp('synced_to_sheet_at')->nullable()->after('submitted_at')->index();
        });
    }

    public function down(): void
    {
        Schema::table('verification_results', function (Blueprint $table): void {
            $table->dropColumn('synced_to_sheet_at');
        });
    }
};
