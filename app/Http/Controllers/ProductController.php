<?php

namespace App\Http\Controllers;

use App\Models\ActivityLog;
use App\Models\categories;
use App\Models\products;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Storage;

class ProductController extends Controller
{
    /**
     * Display a listing of the resource.
     */
    public function index()
    {
        // Pastikan category yang udah terhapus tetep ketarik biar UI gak error 
        $products = products::with(['category' => function ($query) {
            $query->withTrashed();
        }])->latest()->get();

        $products->map(function ($item) {
            $item->foto_url = $item->foto ? asset('storage/' . $item->foto) :  null;
            return $item;
        });

        return response()->json([
            'success' => true,
            'message' => 'Daftar produk',
            'data' => $products
        ], 200);
    }

    /**
     * Store a newly created resource in storage.
     */
    public function store(Request $request)
    {

        if ($request->has('nama_produk')) {
            $request->merge([
                'nama_produk' => strtolower($request->nama_produk)
            ]);
        }

        $request->validate([
            'id_kategori'  => 'required|integer',
            'nama_produk' => 'required|string|max:255|unique:products,nama_produk',
            'harga_produk' => 'required|numeric',
            'deskripsi'    => 'required|string',
            'foto'     => 'required|string',
            'tier_level' => 'required|string',
        ], [
            'nama_produk.unique' => 'Nama produk ini sudah ada di database.'
        ]);

        try {
            // 2. Langsung simpan ke Database!
            // Tidak ada lagi logika ribet memindahkan file fisik
            $product = products::create([
                'id_kategori'  => $request->id_kategori,
                'nama_produk'  => $request->nama_produk,
                'harga_produk' => $request->harga_produk,
                'deskripsi'    => $request->deskripsi,
                // Sesuaikan 'foto' dengan nama kolom di tabel products milikmu
                'foto'         => $request->foto,
                'tier_level' => $request->tier_level,
            ]);

            ActivityLog::insertLog(
                'Buat Produk',
                'Membuat produk: ' . $product->nama_produk
            );


            // 3. Kembalikan response sukses ke Flutter
            return response()->json([
                'success' => true,
                'message' => 'Produk berhasil ditambahkan.',
                'data'    => $product
            ], 201);
        } catch (\Exception $e) {
            return response()->json([
                'success' => false,
                'message' => 'Gagal menyimpan produk: ' . $e->getMessage()
            ], 500);
        }
    }

    /**
     * Display the specified resource.
     */
    public function show($id)
    {
        $product = products::with('category')->find($id);

        if (!$product) {
            return response()->json([
                'success' => false,
                'message' => 'Produk tidak ditemukan',
                'data' => $product
            ], 404);
        }

        $product->foto_url = $product->foto ? asset('storage/', $product->foto) : null;

        return response()->json([
            'success' => true,
            'message' => 'Detail produk',
            'data' => $product,
        ], 200);
    }

    /**
     * Update the specified resource in storage.
     */
    public function update(Request $request, $id)
    {
        $product = products::find($id);

        if (!$product) {
            return response()->json([
                'success' => false,
                'message' => 'Produk tidak ditemukan.',
                'data' => $product
            ], 404);
        }

        if ($request->has('nama_produk')) {
            $request->merge([
                'nama_produk' => strtolower($request->nama_produk)
            ]);
        }

        $request->validate([
            'id_kategori' => 'sometimes|required|exists:categories,id',
            'nama_produk' => 'sometimes|required|string|max:255|unique:products,nama_produk,' . $id,
            'harga_produk' => 'sometimes|required|integer',
            'deskripsi' => 'nullable|string',
            'foto' => 'nullable|string',
            'tier_level' => 'required|string',
        ], [
            'nama_produk.unique' => 'Nama produk ini sudah dipakai oleh produk lain.'
        ]);

        $data = $request->all();

        $product->update($data);

        ActivityLog::insertLog(
            'Edit Produk',
            'Mengedit produk: ' . $product->nama_produk
        );


        return response()->json([
            'success' => true,
            'message' => 'Produk berhasil diupdate.',
            'data' => $product
        ], 200);
    }

    /**
     * Remove the specified resource from storage.
     */
    /**
     * Remove the specified resource from storage.
     */
    public function destroy($id)
    {
        $product = products::find($id);

        if (!$product) {
            return response()->json([
                'success' => false,
                'message' => 'Produk tidak ditemukan',
            ], 404);
        }

        $product->delete();

        ActivityLog::insertLog(
            'Hapus Produk',
            'Menghapus produk: ' . $product->nama_produk
        );

        return response()->json([
            'success' => true,
            'message' => 'Produk berhasil dihapus (soft delete).',
            'data' => $product
        ], 200);
    }
}
