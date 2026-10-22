<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Database\Eloquent\SoftDeletes;

class Media extends Model
{
    use HasFactory, SoftDeletes;

    // nama tabel eksplisit karena "media" adalah kata plural juga di Eloquent (rawan salah tebak)
    protected $table = 'media';

    protected $fillable = [
        'nama_media',
        'tipe',
        'rate_card',
        'pic_kontak',
    ];

    public function advertorials(): HasMany
    {
        return $this->hasMany(Advertorial::class);
    }

    // scope biar gampang query: Media::cetak()->get() atau Media::online()->get()
    public function scopeCetak($query)
    {
        return $query->where('tipe', 'cetak');
    }

    public function scopeOnline($query)
    {
        return $query->where('tipe', 'online');
    }
}