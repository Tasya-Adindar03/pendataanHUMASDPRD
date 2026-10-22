<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Database\Eloquent\SoftDeletes;

class AdvertisingPlan extends Model
{
    use HasFactory, SoftDeletes;

    protected $fillable = ['company_id', 'media_id', 'bulan', 'tahun', 'kuota', 'catatan'];

    public function company(): BelongsTo
    {
        return $this->belongsTo(Company::class);
    }

    public function media(): BelongsTo
    {
        return $this->belongsTo(Media::class);
    }

    // relasi langsung: data advertorial yang eksplisit terhubung ke planning ini
    public function advertorials(): HasMany
    {
        return $this->hasMany(Advertorial::class, 'plan_id');
    }

    public function getRealisasiAttribute(): int
    {
        return $this->advertorials()->count();
    }

    public function getSisaKuotaAttribute(): int
    {
        return max($this->kuota - $this->realisasi, 0);
    }

    public function getPersentaseAttribute(): float
    {
        if ($this->kuota <= 0) {
            return 0;
        }

        return min(round(($this->realisasi / $this->kuota) * 100), 100);
    }

    public function getLabelPeriodeAttribute(): string
    {
        return \Carbon\Carbon::create()->month($this->bulan)->translatedFormat('F') . ' ' . $this->tahun;
    }
}