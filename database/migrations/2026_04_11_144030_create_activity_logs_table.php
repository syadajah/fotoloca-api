<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('activity_logs', function (Blueprint $table) {
            $table->id();
            // PERBAIKAN: Gunakan unsignedBigInteger dan foreign key manual
            $table->unsignedBigInteger('user_id'); // Atau id_users kalau nama kolom primary key di tabel users lu id_users
            $table->foreign('user_id')->references('id')->on('users')->onDelete('cascade');
            // Catatan: Ganti 'id' dan 'users' jika nama kolom/tabel lu beda. 
            // Misalnya: references('id_users')->on('users')

            $table->string('action');
            $table->text('description');
            $table->timestamps();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('activity_logs');
    }
};
