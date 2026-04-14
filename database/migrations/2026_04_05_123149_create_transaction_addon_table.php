<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * Run the migrations.
     */
    public function up(): void
    {
        Schema::create('transaction_addon', function (Blueprint $table) {
            $table->id();
            // Sambungin ke tabel transactions
            $table->foreignId('transaction_id')->constrained('transactions')->onDelete('cascade');
            // Sambungin ke tabel addons (sesuaikan nama tabel add-on lu, misal 'addons')
            $table->foreignId('addon_id')->constrained('add_ons')->onDelete('cascade');
            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('transaction_addon');
    }
};
