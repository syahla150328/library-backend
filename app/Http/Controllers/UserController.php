<?php

namespace App\Http\Controllers;

use App\Models\User;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Storage;
use Illuminate\Support\Facades\Validator;

class UserController extends Controller
{
    // Mengambil semua anggota dengan role 'siswa'
    public function index()
    {
        $users = User::where('role', 'siswa')
                    ->orderBy('name', 'asc')
                    ->get();
                    
        return response()->json($users);
    }

    // Fungsi Update Profil (Tambahan Baru)
    public function updateProfile(Request $request, $id)
    {
        $user = User::findOrFail($id);

        // Validasi input
        $validator = Validator::make($request->all(), [
            'name'  => 'required|string|max:255',
            'photo' => 'nullable|image|mimes:jpeg,png,jpg|max:2048', // Maks 2MB
        ]);

        if ($validator->fails()) {
            return response()->json(['errors' => $validator->errors()], 422);
        }

        // Update nama
        $user->name = $request->name;

        // Logika Upload Foto
        if ($request->hasFile('photo')) {
            // Hapus foto lama di storage jika ada (biar gak menumpuk sampah)
            if ($user->photo_path && Storage::disk('public')->exists($user->photo_path)) {
                Storage::disk('public')->delete($user->photo_path);
            }
            
            // Simpan foto baru ke folder 'storage/app/public/profiles'
            $path = $request->file('photo')->store('profiles', 'public');
            $user->photo_path = $path;
        }

        $user->save();

        return response()->json([
            'message' => 'Profil berhasil diperbarui!',
            'user' => $user
        ], 200);
    }

    // Menghapus anggota
    public function destroy($id)
    {
        $user = User::findOrFail($id);
        
        // Proteksi tambahan: Pastikan tidak menghapus admin lewat sini
        if ($user->role === 'admin') {
            return response()->json(['message' => 'Admin tidak bisa dihapus!'], 403);
        }

        // Hapus foto profil jika ada sebelum user dihapus
        if ($user->photo_path) {
            Storage::disk('public')->delete($user->photo_path);
        }

        $user->delete();
        return response()->json(['message' => 'User berhasil dihapus']);
    }
}