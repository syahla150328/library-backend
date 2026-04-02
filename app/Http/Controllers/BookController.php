<?php

namespace App\Http\Controllers;

use App\Models\Book;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Storage;

class BookController extends Controller
{
    // Menampilkan semua buku
    public function index()
    {
        return response()->json(Book::all());
    }

    // Simpan buku baru
  public function store(Request $request)
    {
        $validatedData = $request->validate([
            'judul'     => 'required',
            'penulis'   => 'required',
            'stok'      => 'required|numeric',
            'kategori'  => 'nullable|string',
            'deskripsi' => 'nullable|string',
            'cover_image' => 'nullable|image|mimes:jpeg,png,jpg,gif,svg|max:2048', // Tambahkan validasi gambar
        ]);

        $imagePath = null;
        if ($request->hasFile('cover_image')) {
            // Simpan gambar di folder storage/app/public/covers
            $imagePath = $request->file('cover_image')->store('covers', 'public');
        }

        $book = Book::create([
            'judul' => $validatedData['judul'],
            'penulis' => $validatedData['penulis'],
            'stok' => $validatedData['stok'],
            'kategori' => $validatedData['kategori'],
            'deskripsi' => $validatedData['deskripsi'],
            'cover_image' => $imagePath, // Simpan path gambar ke database
        ]);

        return response()->json($book, 201);
    }

    public function update(Request $request, Book $book)
    {
        $validatedData = $request->validate([
            'judul'     => 'required',
            'penulis'   => 'required',
            'stok'      => 'required|numeric',
            'kategori'  => 'nullable|string',
            'deskripsi' => 'nullable|string',
            'cover_image' => 'nullable|image|mimes:jpeg,png,jpg,gif,svg|max:2048', // Tambahkan validasi gambar
        ]);

        $imagePath = $book->cover_image; // Ambil path gambar lama

        if ($request->hasFile('cover_image')) {
            // Hapus gambar lama jika ada
            if ($book->cover_image) {
                Storage::disk('public')->delete($book->cover_image);
            }
            // Simpan gambar baru
            $imagePath = $request->file('cover_image')->store('covers', 'public');
        } elseif ($request->input('remove_cover_image')) { // Untuk menghapus gambar tanpa upload baru
            if ($book->cover_image) {
                Storage::disk('public')->delete($book->cover_image);
            }
            $imagePath = null;
        }

        $book->update([
            'judul' => $validatedData['judul'],
            'penulis' => $validatedData['penulis'],
            'stok' => $validatedData['stok'],
            'kategori' => $validatedData['kategori'],
            'deskripsi' => $validatedData['deskripsi'],
            'cover_image' => $imagePath,
        ]);

        return response()->json($book);
    }

    public function destroy(Book $book)
    {
        // Hapus gambar saat buku dihapus
        if ($book->cover_image) {
            Storage::disk('public')->delete($book->cover_image);
        }
        $book->delete();
        return response()->json(null, 204);
    }

} // Tanda penutup class yang benar