<?php

namespace App\Http\Controllers;

use App\Models\ActivityLog;
use App\Models\User;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Hash;

class UserController extends Controller
{

    public function index()
    {
        $users = User::latest()->get();
        return response()->json(['success' => true, 'data' => $users], 200);
    }


    public function store(Request $request)
    {

        $request->merge([
            'username' => $request->has('username') ? strtolower($request->username) : null,
            'email' => $request->has('email') ? strtolower($request->email) : null,
        ]);

        $request->validate([
            'name' => 'required|string|max:255',
            'username' => 'required|string|max:255|unique:users,username',
            'email' => 'required|string|email|max:255|unique:users',
            'password' => 'required|string|min:8',
            'role' => 'required|string|in:admin,kasir,owner',
        ], [
            'username.unique' => 'Username ini sudah dipakai, coba gunakan variasi lain.',
            'email.unique' => 'Email ini sudah terdaftar.'
        ]);

        try {
            $user = User::create([
                'name' => $request->name,
                'email' => $request->email,
                'username' => $request->username,
                'password' => Hash::make($request->password),
                'role' => $request->role,
                'is_active' => true,
            ]);

            ActivityLog::insertLog(
                'Buat Pengguna',
                'Membuat pengguna: ' . $user->name
            );

            return response()->json(['success' => true, 'message' => 'Pengguna berhasil ditambahkan', 'data' => $user], 201);
        } catch (\Exception $e) {
            return response()->json(['success' => false, 'message' => 'Gagal: ' . $e->getMessage()], 500);
        }
    }


    public function update(Request $request, $id)
    {
        $user = User::find($id);
        if (!$user) return response()->json(['success' => false, 'message' => 'Pengguna tidak ditemukan'], 404);

        $currentUser = Auth::user();

        // REVISI PROTEKSI: Tambahkan 'email' ke daftar blacklist kasir
        if ($currentUser->role === 'kasir' && $currentUser->id == $id) {
            if ($request->hasAny(['name', 'username', 'email', 'role'])) {
                return response()->json([
                    'success' => false,
                    'message' => 'Akses ditolak! Kasir tidak diperbolehkan mengubah Nama, Username, Email, atau Role.'
                ], 403);
            }
        }

        $request->merge([
            'username' => $request->has('username') ? strtolower($request->username) : null,
            'email' => $request->has('email') ? strtolower($request->email) : null,
        ]);

        $request->validate([
            'name' => 'nullable|string|max:255',
            'username' => 'nullable|string|max:255|unique:users,username,' . $id,
            'email' => 'nullable|string|email|max:255|unique:users,email,' . $id,
            'role' => 'nullable|string|in:admin,kasir,owner',
            'password' => 'nullable|string|min:8',
        ], [
            'username.unique' => 'Username ini sudah dipakai oleh akun lain.',
            'email.unique' => 'Email ini sudah dipakai oleh akun lain.'
        ]);

        $data = [];
        if ($request->filled('name')) $data['name'] = $request->name;
        if ($request->filled('username')) $data['username'] = $request->username;
        if ($request->filled('email')) $data['email'] = $request->email;
        if ($request->filled('role')) $data['role'] = $request->role;
        if ($request->filled('password')) $data['password'] = Hash::make($request->password);

        $user->update($data);

        ActivityLog::insertLog(
            'Edit Pengguna',
            'Mengedit pengguna: ' . $user->name
        );

        return response()->json([
            'success' => true,
            'message' => 'Data pengguna berhasil diupdate',
            'data' => $user
        ], 200);
    }

    public function destroy($id)
    {
        $user = User::find($id);

        if (!$user) {
            return response()->json([
                'success' => false,
                'message' => 'User tidak ditemukan',
                'data' => $user
            ], 404);
        }

        $user->delete();

        ActivityLog::insertLog(
            'Hapus Pengguna',
            'Menghapus pengguna: ' . $user->name
        );

        return response()->json([
            'success' => true,
            'message' => 'User berhasil dihapus.',
            'data' => $user
        ], 200);
    }

    public function toggleStatus($id)
    {
        $user = User::find($id);
        if (!$user) return response()->json(['success' => false, 'message' => 'Pengguna tidak ditemukan'], 404);


        $user->is_active = !$user->is_active;
        $user->save();

        $statusText = $user->is_active ? 'diaktifkan' : 'dinonaktifkan';

        ActivityLog::insertLog(
            'Ubah Status Pengguna',
            'Mengubah status pengguna: ' . $user->name . ' menjadi ' . $statusText
        );

        return response()->json([
            'success' => true,
            'message' => "Akun berhasil $statusText",
            'is_active' => $user->is_active
        ], 200);
    }
}
