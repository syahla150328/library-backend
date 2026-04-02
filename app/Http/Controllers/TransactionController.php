<?php

namespace App\Http\Controllers;

use App\Models\Book;
use App\Models\Transaction;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;

class TransactionController extends Controller
{
    public function borrow(Request $request)
    {
        $request->validate([
            'book_id' => 'required|exists:books,id',
        ]);

        $book = Book::find($request->book_id);

        // 1. Cek apakah stok tersedia
        if ($book->stok <= 0) {
            return response()->json(['message' => 'Stok buku habis!'], 400);
        }

        // 2. Buat transaksi
        $transaction = Transaction::create([
            'user_id' => Auth::id(),
            'book_id' => $book->id,
            'tanggal_pinjam' => now(),
            'status' => 'dipinjam',
        ]);

        // 3. Kurangi stok buku
        $book->decrement('stok');

        return response()->json([
            'message' => 'Berhasil meminjam buku',
            'transaction' => $transaction,
            'sisa_stok' => $book->stok
        ]);
    }

    public function returnBook(Request $request)
{
    $request->validate([
        'transaction_id' => 'required|exists:transactions,id',
    ]);

    $transaction = Transaction::find($request->transaction_id);

    if ($transaction->status === 'kembali') {
        return response()->json(['message' => 'Buku ini sudah dikembalikan sebelumnya'], 400);
    }

    // 1. Ubah status & isi tanggal kembali
    $transaction->update([
        'status' => 'kembali',
        'tanggal_kembali' => now(),
    ]);

    // 2. Tambah stok buku kembali
    $book = Book::find($transaction->book_id);
    $book->increment('stok');

    return response()->json([
        'message' => 'Buku berhasil dikembalikan',
        'stok_sekarang' => $book->stok
    ]);
}

}