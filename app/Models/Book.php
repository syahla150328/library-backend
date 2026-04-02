<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Book extends Model
{
    use HasFactory;

    // Tambahkan kategori dan deskripsi di sini agar bisa disimpan
   protected $fillable = ['judul', 'penulis', 'stok', 'kategori', 'deskripsi', 'cover_image'];
}