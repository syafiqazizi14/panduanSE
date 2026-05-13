<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration {
    public function up(): void
    {
        Schema::create('verification_results', function (Blueprint $table): void {
            $table->id();
            $table->timestamp('submitted_at')->nullable();
            $table->string('id_scraping')->index();
            $table->string('source_tab', 64)->index();
            $table->string('match_idsbr')->nullable();
            $table->string('match_nama_usaha')->nullable();
            $table->text('match_alamat')->nullable();
            $table->string('verification_status', 32)->index();
            $table->string('officer_name', 120);
            $table->string('officer_id', 120)->nullable();
            $table->decimal('officer_latitude', 10, 7)->nullable();
            $table->decimal('officer_longitude', 10, 7)->nullable();
            $table->decimal('verified_latitude', 10, 7)->nullable();
            $table->decimal('verified_longitude', 10, 7)->nullable();
            $table->decimal('distance_km', 8, 3)->nullable();
            $table->text('notes')->nullable();
            $table->string('photo_url')->nullable();
            $table->string('device_id', 191)->nullable();
            $table->string('idempotency_key', 191)->unique();
            $table->timestamps();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('verification_results');
    }
};
