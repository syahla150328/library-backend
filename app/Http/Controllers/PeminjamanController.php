<?php

namespace App\Http\Controllers;

use App\Models\Book;
use App\Models\Peminjaman;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;

class PeminjamanController extends Controller
{
    public function pinjam(Request $request)
    {
        $book = Book::find($request->book_id);

        // Cek apakah stok masih ada
        if ($book->stok <= 0) {
            return response()->json(['message' => 'Stok buku habis!'], 400);
        }

        // 1. Catat di tabel peminjaman
        Peminjaman::create([
            'user_id' => Auth::id(),
            'book_id' => $book->id,
            'tanggal_pinjam' => now(),
            'status' => 'dipinjam'
        ]);

        // 2. KURANGI STOK BUKU
        $book->decrement('stok');

        return response()->json(['message' => 'Berhasil meminjam buku!']);
    }


    // Ambil daftar buku yang sedang dipinjam oleh user yang login
public function riwayat()
{
    // Mengambil peminjaman milik user login yang statusnya masih 'dipinjam'
    $riwayat = Peminjaman::with('book')
                ->where('user_id', auth()->id()) 
                ->where('status', 'dipinjam')
                ->get();

    return response()->json($riwayat);
}

// Logika Pengembalian Buku
public function kembalikan($id)
{
    $pinjam = Peminjaman::findOrFail($id);
    $pinjam->update([
        'status' => 'dikembalikan',
        'tanggal_kembali' => now()
    ]);

    // Tambah lagi stok bukunya
    $book = Book::find($pinjam->book_id);
    $book->increment('stok');

    return response()->json(['message' => 'Buku berhasil dikembalikan!']);
}

public function riwayatSelesai()
{
    $selesai = Peminjaman::with('book')
                ->where('user_id', auth()->id()) 
                ->where('status', 'dikembalikan') // Filter status yang sudah balik
                ->orderBy('updated_at', 'desc')
                ->get();

    return response()->json($selesai);
}

public function allTransaksi()
{
    // Mengambil semua data peminjaman beserta data user dan bukunya
    $transaksi = Peminjaman::with(['user', 'book'])
                ->orderBy('created_at', 'desc')
                ->get();

    return response()->json($transaksi);
}

}