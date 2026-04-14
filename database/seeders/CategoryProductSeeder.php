<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use App\Models\categories;
use App\Models\products;

class CategoryProductSeeder extends Seeder
{
    public function run(): void
    {
        // 1. Buat Data Kategori
        $kategoriMinuman = categories::create(['nama_kategori' => 'Minuman']);
        $kategoriMakanan = categories::create(['nama_kategori' => 'Makanan']);
        $kategoriSnack = categories::create(['nama_kategori' => 'Snack & Cemilan']);

        // 2. Buat Data Produk untuk Kategori Minuman
        products::create([
            'id_kategori' => $kategoriMinuman->id, // Mengambil ID dari Kategori Minuman di atas
            'nama_produk' => 'Es Kopi Gula Aren',
            'harga_produk' => 18000,
            'deskripsi' => 'Perpaduan espresso, susu segar, dan gula aren asli yang legit.',
            'foto' => null // Biarkan kosong dulu, nanti kita tes upload fotonya lewat Postman/Flutter
        ]);

        products::create([
            'id_kategori' => $kategoriMinuman->id,
            'nama_produk' => 'Matcha Latte',
            'harga_produk' => 22000,
            'deskripsi' => 'Minuman green tea khas Jepang yang *creamy* dan menyegarkan.',
            'foto' => null
        ]);

        // 3. Buat Data Produk untuk Kategori Makanan
        products::create([
            'id_kategori' => $kategoriMakanan->id,
            'nama_produk' => 'Nasi Goreng Spesial',
            'harga_produk' => 25000,
            'deskripsi' => 'Nasi goreng bumbu rempah dengan tambahan telur mata sapi dan ayam suwir.',
            'foto' => null
        ]);

        // 4. Buat Data Produk untuk Kategori Snack
        products::create([
            'id_kategori' => $kategoriSnack->id,
            'nama_produk' => 'Kentang Goreng',
            'harga_produk' => 15000,
            'deskripsi' => 'Kentang goreng renyah dengan taburan bumbu rahasia.',
            'foto' => null
        ]);

        $this->command->info('Data Kategori dan Produk berhasil ditambahkan!');
    }
}