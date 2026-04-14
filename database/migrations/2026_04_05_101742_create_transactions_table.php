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
        Schema::create('transactions', function (Blueprint $table) {
            $table->id();
            $table->string('nomor_unik')->unique();
            $table->foreignId('id_users')->constrained('users'); // ID Kasir
            $table->foreignId('id_produk')->constrained('products'); // ID Paket Foto
            $table->string('nama_pelanggan');
            $table->string('email_pelanggan')->nullable();
            $table->date('jadwal');
            $table->integer('uang_bayar');
            $table->integer('uang_kembali');
            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('transactions');
    }
};
