<?php

namespace App\Http\Controllers;

use App\Models\ActivityLog;
use App\Models\categories;
use Illuminate\Http\Request;
// Hapus baris 'use function Pest\Laravel\delete;' karena bikin konflik

class CategoryController extends Controller
{
    /**
     * Display a listing of the resource.
     */
    public function index()
    {
        // Secara default, ini cuma nampilin kategori yang BELUM di-soft delete
        $category = categories::with('products')->latest()->get();

        return response()->json([
            'success' => true,
            'message' => 'Daftar kategori',
            'data' => $category
        ], 200);
    }

    /**
     * Store a newly created resource in storage.
     */
    public function store(Request $request)
    {

        if ($request->has('nama_kategori')) {
            $request->merge([
                'nama_kategori' => strtolower($request->nama_kategori)
            ]);
        }

        $request->validate([
            'nama_kategori' => 'required|string|max:255|unique:categories,nama_kategori',
        ], [
            // Tambahkan pesan error kustom biar kasir paham
            'nama_kategori.unique' => 'Nama kategori ini sudah ada, silakan gunakan nama lain.'
        ]);

        $category = categories::create($request->all());

        ActivityLog::insertLog(
            'Buat Kategori',
            'Membuat kategori: ' . $category->nama_kategori
        );

        return response()->json([
            'success' => true,
            'message' => 'Kategori berhasil dibuat.',
            'data'  => $category
        ], 201);
    }

    /**
     * Display the specified resource.
     */
    public function show($id)
    {
        $category = categories::with('products')->find($id);

        if (!$category) {
            return response()->json(['success' => false, 'message' => 'Kategori tidak ditemukan'],  404);
        }

        return response()->json([
            'success' => true,
            'message' => 'Detail kategori',
            'data' => $category
        ], 200);
    }

    /**
     * Update the specified resource in storage.
     */
    public function update(Request $request, $id)
    {
        $category = categories::find($id);
        if (!$category) {
            return response()->json(['success' => false, 'message' => 'Kategori tidak ditemukan'], 404);
        }

        if ($request->has('nama_kategori')) {
            $request->merge([
                'nama_kategori' => strtolower($request->nama_kategori)
            ]);
        }

        // 👇 UBAH VALIDASINYA JADI SEPERTI INI
        $request->validate([
            'nama_kategori' => 'required|string|max:255|unique:categories,nama_kategori,' . $id,
        ], [
            'nama_kategori.unique' => 'Nama kategori ini sudah dipakai, silakan gunakan nama lain.'
        ]);

        $category->update($request->all());

        ActivityLog::insertLog(
            'Edit Kategori',
            'Mengedit kategori: ' . $category->nama_kategori
        );

        return response()->json([
            'success' => true,
            'message' => 'Kategori berhasil diubah',
            'data' => $category
        ], 200);
    }

    /**
     * Remove the specified resource from storage.
     */
    public function destroy(string $id)
    {
        $category = categories::find($id);

        if (!$category) {
            return response()->json([
                'success' => false,
                'message' => 'Kategori tidak ditemukan'
            ], 404);
        }

        // Karena model sudah pakai SoftDeletes, perintah ini HANYA menyembunyikan data
        $category->delete();

        ActivityLog::insertLog(
            'Hapus Kategori',
            'Menghapus kategori: ' . $category->nama_kategori
        );

        return response()->json([
            'success' => true,
            'message' => 'Kategori berhasil dihapus.', // (soft delete)
        ], 200);
    }
}
