<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class Transaction extends Model
{
   protected $fillable = [
    'user_id',
    'book_id',
    'tanggal_pinjam',
    'tanggal_kembali',
    'status'
];
}
