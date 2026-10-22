<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\SoftDeletes;
use Illuminate\Support\Str;

class Advertorial extends Model
{
    use HasFactory, SoftDeletes;

    protected $fillable = [
    'company_id',
    'media_id',
    'plan_id',
    'judul',
    'waktu_tayang',
    'tanggal_kwitansi',
    'tanggal_jatuh_tempo',
    'tanggal_dibayar',
    'nominal',
    'status',
    'no_invoice',
    'file_kwitansi',
    'catatan',
    'created_by',
    ];

    protected $casts = [
        'waktu_tayang' => 'date',
        'tanggal_kwitansi' => 'date',
        'tanggal_jatuh_tempo' => 'date',
        'tanggal_dibayar' => 'date',
        'nominal' => 'decimal:2',
    ];

    // ==== Relasi ====

    public function company(): BelongsTo
    {
        return $this->belongsTo(Company::class);
    }

    public function plan(): BelongsTo
    {
        return $this->belongsTo(AdvertisingPlan::class);
    }

    public function media(): BelongsTo
    {
        return $this->belongsTo(Media::class);
    }

    public function creator(): BelongsTo
    {
        return $this->belongsTo(User::class, 'created_by');
    }

    // ==== Scopes untuk filter cepat ====

    public function scopeCetak($query)
    {
        return $query->whereHas('media', fn ($q) => $q->where('tipe', 'cetak'));
    }

    public function scopeOnline($query)
    {
        return $query->whereHas('media', fn ($q) => $q->where('tipe', 'online'));
    }

    public function scopeBelumDibayar($query)
    {
        return $query->where('status', 'belum_dibayar');
    }

    public function scopeSudahDibayar($query)
    {
        return $query->where('status', 'sudah_dibayar');
    }

    public function scopeJatuhTempo($query)
    {
        return $query->where('status', 'jatuh_tempo');
    }

    // ==== Auto-generate no_invoice saat data dibuat ====

    protected static function booted(): void
    {
        static::creating(function (Advertorial $advertorial) {
            if (empty($advertorial->no_invoice)) {
                $advertorial->no_invoice = static::generateNoInvoice();
            }
        });
    }

    protected static function generateNoInvoice(): string
    {
        $prefix = 'INV-' . now()->format('Ym') . '-';

        // ambil nomor urut terakhir bulan ini
        $lastNumber = static::withTrashed()
            ->where('no_invoice', 'like', $prefix . '%')
            ->orderByDesc('no_invoice')
            ->value('no_invoice');

        $nextNumber = $lastNumber
            ? ((int) Str::afterLast($lastNumber, '-')) + 1
            : 1;

        return $prefix . str_pad($nextNumber, 4, '0', STR_PAD_LEFT);
    }

    // ==== Helper untuk aging (umur piutang) ====

    public function getUmurPiutangAttribute(): ?int
    {
        if ($this->status === 'sudah_dibayar' || !$this->tanggal_jatuh_tempo) {
            return null;
        }

        return now()->diffInDays($this->tanggal_jatuh_tempo, false); // negatif = sudah lewat tenggat
    }
}  