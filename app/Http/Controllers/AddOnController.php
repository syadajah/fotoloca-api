<?php

namespace App\Http\Controllers;

use App\Models\ActivityLog;
use App\Models\AddOn;
use Illuminate\Http\Request;

class AddOnController extends Controller
{
    public function index()
    {
        return response()->json(['data' => AddOn::all()], 200);
    }

    public function store(Request $request)
    {
        $request->validate([
            'nama_addon' => 'required|string',
            'harga_addon' => 'required|integer',
        ]);

        $addon = AddOn::create($request->all());

        ActivityLog::insertLog(
            'Buat Add ons',
            'Membuat Add ons: ' . $addon->nama_addon
        );
        return response()->json(['success' => true, 'message' => 'Add-on berhasil ditambah', 'data' => $addon], 201);
    }

    public function update(Request $request, $id)
    {
        $addon = AddOn::find($id);
        if (!$addon) return response()->json(['success' => false, 'message' => 'Not found'], 404);

        $addon->update($request->all());

        ActivityLog::insertLog(
            'Ubah Add ons',
            'Mengubah Add ons: ' . $addon->nama_addon
        );
        return response()->json(['success' => true, 'message' => 'Add-on berhasil diupdate'], 200);
    }

    public function destroy($id)
    {
        $addon = AddOn::find($id);
        if (!$addon) return response()->json(['success' => false, 'message' => 'Not found'], 404);

        $addon->delete();

        ActivityLog::insertLog(
            'Hapus Add ons',
            'Menghapus Add ons: ' . $addon->nama_addon
        );
        return response()->json(['success' => true, 'message' => 'Add-on dihapus'], 200);
    }
}
