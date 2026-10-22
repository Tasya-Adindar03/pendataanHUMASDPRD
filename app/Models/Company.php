<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Database\Eloquent\SoftDeletes;

class Company extends Model
{
    use HasFactory, SoftDeletes;

    protected $fillable = [
        'nama_perusahaan',
        'pic_nama',
        'pic_kontak',
        'email',
        'alamat',
    ];

    public function advertorials(): HasMany
    {
        return $this->hasMany(Advertorial::class);
    }

    // total nominal semua advertorial milik perusahaan ini
    public function getTotalTagihanAttribute(): float
    {
        return $this->advertorials()->sum('nominal');
    }

    public function getTotalBelumDibayarAttribute(): float
    {
        return $this->advertorials()
            ->whereIn('status', ['belum_dibayar', 'jatuh_tempo'])
            ->sum('nominal');
    }
}