@extends('layouts.app')
@section('title', 'Dashboard 2')
@section('content')
<link rel="stylesheet" href="{{ asset('css/dashboard-two.css') }}">
@php
    $money = fn ($n) => 'Rp '.number_format($n / 1000000, 1, ',', '.').' jt';
    $percent = fn ($n) => number_format($n, 1, ',', '.').'%';
    $periodFilters = ['dari_tanggal' => $month->toDateString(), 'sampai_tanggal' => $end->toDateString()];
    $maxDay = max(1, collect($daily)->max('value'));
    $current = $month->isSameMonth(now());
@endphp
<div class="d2">
    <header class="d2-hero">
        <div><div class="d2-eyebrow"><span class="d2-dot"></span> Publication intelligence</div><h2>Kinerja publikasi,<br>dalam satu pandangan.</h2><p>Telusuri nilai publikasi, kesehatan pembayaran, dan peluang penagihan. Pilih bulan untuk melihat pola di balik setiap angka.</p></div>
        <form method="GET" action="{{ route('dashboard.two') }}" class="d2-filter">
            <label for="d2-month">Periode analisis</label><div class="d-flex gap-2"><input type="month" id="d2-month" name="bulan" value="{{ $month->format('Y-m') }}" required><button type="submit">Terapkan</button></div>
            <a href="{{ route('dashboard.two') }}">Kembali ke bulan berjalan <i class="bi bi-arrow-up-right"></i></a>
        </form>
    </header>
    @if ($errors->any())<div class="alert alert-danger mt-3">{{ $errors->first() }}</div>@endif
    <div class="d2-explore" aria-label="Kontrol eksplorasi">
        <div class="d2-segments" aria-label="Jenis media">
            @foreach (['' => 'Semua kanal', 'cetak' => 'Cetak', 'online' => 'Online'] as $value => $label)
                <button type="button" data-focus-key="tipe" data-focus-value="{{ $value }}" aria-pressed="{{ request('tipe', '') === $value ? 'true' : 'false' }}">{{ $label }}</button>
            @endforeach
        </div>
        <div class="d2-focus-tags">
            @forelse ($focusNames as $key => $label)<button type="button" data-clear-focus="{{ $key }}" title="Hapus filter {{ $label }}">{{ $label }} ×</button>@empty<span>Klik grafik untuk memfokuskan seluruh dashboard</span>@endforelse
        </div>
        <div class="d2-tools"><button type="button" data-reset-focus title="Hapus semua fokus"><i class="bi bi-arrow-counterclockwise"></i> Reset</button><button type="button" data-open-details><i class="bi bi-list-ul"></i> Detail</button><button type="button" data-fullscreen title="Mode layar penuh"><i class="bi bi-fullscreen"></i></button></div>
    </div>
    <div class="d2-live" role="status" aria-live="polite"></div>
    <div class="d2-context"><span><strong>{{ $month->translatedFormat('F Y') }}</strong> · {{ $count }} advertorial · berdasarkan tanggal kwitansi{{ $current ? ' · bulan berjalan belum final' : '' }}</span><span>Status pembayaran & umur piutang dihitung saat halaman dibuka</span></div>
    <div class="d2-kpis">
        <section class="d2-kpi"><div class="d2-kpi-label">Nilai publikasi <i class="bi bi-bar-chart-line"></i></div><div class="d2-kpi-value">{{ $money($total) }}</div><div class="d2-kpi-note">{{ $growth === null ? 'Belum ada pembanding bulan lalu' : ($growth > 0 ? '+' : '').$percent($growth).' vs bulan lalu'.($current ? ' (sementara)' : '') }}</div></section>
        <section class="d2-kpi"><div class="d2-kpi-label">Porsi sudah lunas <i class="bi bi-check2-circle"></i></div><div class="d2-kpi-value" style="color:#159b83">{{ $percent($paidRate) }}</div><div class="d2-kpi-note"><b>{{ $money($paid) }}</b> dari nilai bulan terpilih</div></section>
        <section class="d2-kpi"><div class="d2-kpi-label">Piutang lewat tenggat <i class="bi bi-hourglass-split"></i></div><div class="d2-kpi-value" style="color:#cd6275">{{ $money($overdue) }}</div><div class="d2-kpi-note"><b>{{ $overdueRows->count() }} tagihan</b> perlu ditindaklanjuti</div></section>
        <section class="d2-kpi"><div class="d2-kpi-label">Pemenuhan kuota <i class="bi bi-bullseye"></i></div><div class="d2-kpi-value">{{ $percent($planRate) }}</div><div class="d2-kpi-note"><b>{{ $realized }} / {{ $quota }}</b> slot publikasi terpenuhi</div></section>
    </div>
    <div class="d2-insights">
        <article class="d2-insight warn"><strong><i class="bi bi-flag me-1"></i> Prioritas penagihan</strong>@if ($overdue > 0){{ $percent($outstanding > 0 ? $overdue / $outstanding * 100 : 0) }} dari saldo belum lunas sudah melewati tenggat. Dahulukan {{ $overdueRows->count() }} tagihan ini.@elseif ($outstanding > 0)Belum ada tagihan melewati tenggat. Pantau {{ $money($outstanding) }} saldo yang masih terbuka.@else Tidak ada saldo piutang dari periode ini.@endif<div class="d2-simulation"><label for="d2-recovery">Simulasi tertagih <output id="d2-recovery-value">0%</output></label><input id="d2-recovery" type="range" min="0" max="100" step="5" value="0" aria-label="Persentase saldo belum lunas yang diasumsikan tertagih"><small data-recovery-result>Geser untuk simulasi; data asli tetap.</small></div></article>
        <article class="d2-insight"><strong><i class="bi bi-building me-1"></i> Konsentrasi saldo</strong>@if ($debtor && $debtor['outstanding'] > 0){{ $debtor['name'] }} menyumbang <b>{{ $percent($outstanding > 0 ? $debtor['outstanding'] / $outstanding * 100 : 0) }}</b> piutang ({{ $money($debtor['outstanding']) }}). Jadwalkan tindak lanjut terarah.@else Belum ada perusahaan dengan saldo belum lunas pada periode ini.@endif</article>
        <article class="d2-insight good"><strong><i class="bi bi-lightning-charge me-1"></i> Momentum publikasi</strong>@if ($total > 0)Puncak nilai terjadi pada <b>{{ \Carbon\Carbon::parse($peak['date'])->locale('id')->translatedFormat('d F') }}</b>, senilai {{ $money($peak['value']) }} dari {{ $peak['count'] }} publikasi. Gunakan kalender untuk menelusuri rinciannya.@else Belum ada publikasi di bulan ini. Pilih periode lain atau tambahkan advertorial.@endif</article>
    </div>
    <div class="d2-section"><h2>Peta kinerja {{ $month->translatedFormat('F') }}</h2><span>Klik grafik = fokus bersama · ⛶ perbesar · ↓ unduh PNG</span></div>
    <div id="d2-error" class="d2-error" role="alert">Grafik belum dapat dimuat. Periksa koneksi internet dan muat ulang. Ringkasan dan kalender tetap dapat digunakan.</div>
    <noscript><p>Aktifkan JavaScript untuk menampilkan grafik interaktif.</p></noscript>
    <div class="d2-grid">
        <section class="d2-panel wide" id="panel-daily"><div class="d2-panel-head"><h3>Perjalanan nilai publikasi</h3><span class="d2-panel-index">01 / AREA</span></div><div class="d2-panel-sub">Akumulasi nilai kwitansi & bagian yang saat ini lunas · Rp juta</div><div class="d2-chart"><canvas id="d2-daily" role="img" aria-label="Tren kumulatif nilai publikasi dan nilai lunas per hari"></canvas><div class="d2-empty">Belum ada nilai publikasi</div></div><div class="d2-foot">Jarak antar garis menunjukkan <strong>nilai yang belum lunas</strong>, bukan arus kas harian.</div></section>
        <section class="d2-panel narrow" id="panel-status"><div class="d2-panel-head"><h3>Komposisi nilai</h3><span class="d2-panel-index">02 / RING</span></div><div class="d2-panel-sub">Nilai rupiah menurut status pembayaran tersimpan</div><div class="d2-chart"><canvas id="d2-status" role="img" aria-label="Komposisi nilai lunas, belum dibayar, dan jatuh tempo"></canvas><div class="d2-empty">Belum ada data pembayaran</div></div><div class="d2-foot"><strong>{{ $money($outstanding) }}</strong> masih belum lunas.</div></section>
        <section class="d2-panel narrow" id="panel-type"><div class="d2-panel-head"><h3>Jejak kanal media</h3><span class="d2-panel-index">03 / POLAR</span></div><div class="d2-panel-sub">Radius sektor mewakili nilai publikasi · Rp juta</div><div class="d2-chart"><canvas id="d2-type" role="img" aria-label="Perbandingan nilai publikasi media cetak dan online"></canvas><div class="d2-empty">Belum ada data kanal media</div></div><div class="d2-foot">Bandingkan kontribusi <strong>cetak dan online</strong>.</div></section>
        <section class="d2-panel"><div class="d2-panel-head"><h3>Kalender intensitas</h3><span class="d2-panel-index">04 / HEATMAP</span></div><div class="d2-panel-sub">Warna lebih pekat = nilai kwitansi harian lebih besar</div><div class="d2-heat">
            @foreach (['Sen','Sel','Rab','Kam','Jum','Sab','Min'] as $day)<span class="d2-daylabel">{{ $day }}</span>@endforeach
            @for ($i = 1; $i < $month->dayOfWeekIso; $i++)<span aria-hidden="true"></span>@endfor
            @foreach ($daily as $day)
                @php($level = $day['value'] > 0 ? min(4, max(1, (int) ceil($day['value'] / $maxDay * 4))) : 0)
                <a class="d2-day l{{ $level }}" data-focus-key="hari" data-focus-value="{{ $day['day'] }}" href="{{ route('dashboard.two', array_merge(request()->query(), ['bulan' => $month->format('Y-m'), 'hari' => $day['day']])) }}" title="{{ $day['date'] }} · {{ $day['count'] }} publikasi · Rp {{ number_format($day['value'], 0, ',', '.') }}" aria-label="{{ $day['date'] }}, {{ $day['count'] }} publikasi, Rp {{ number_format($day['value'], 0, ',', '.') }}">{{ $day['day'] }}</a>
            @endforeach
        </div><div class="d2-heat-legend">Rendah <i style="background:#eef5f6"></i><i style="background:#d2eee7"></i><i style="background:#8ed9c8"></i><i style="background:#41b99f"></i><i style="background:#168a78"></i> Tinggi</div><div class="d2-foot">Klik tanggal untuk memfokuskan seluruh grafik ke hari tersebut.</div></section>
        <section class="d2-panel" id="panel-bubble"><div class="d2-panel-head"><h3>Peta performa media</h3><span class="d2-panel-index">05 / BUBBLE</span></div><div class="d2-panel-sub">X: jumlah publikasi · Y: rata-rata nilai · luas: total nilai</div><div class="d2-chart"><canvas id="d2-bubble" role="img" aria-label="Peta jumlah publikasi dan rata-rata nilai per media"></canvas><div class="d2-empty">Belum ada data media</div></div><div class="d2-foot">Kanan atas = <strong>volume dan rata-rata nilai tinggi</strong>. Bukan ukuran efektivitas iklan.</div></section>
        <section class="d2-panel" id="panel-companies"><div class="d2-panel-head"><h3>Portofolio perusahaan</h3><span class="d2-panel-index">06 / STACK</span></div><div class="d2-panel-sub">5 perusahaan dengan nilai terbesar · Rp juta</div><div class="d2-chart"><canvas id="d2-companies" role="img" aria-label="Nilai lunas dan belum lunas dari lima perusahaan terbesar"></canvas><div class="d2-empty">Belum ada perusahaan</div></div><div class="d2-foot">Bagian merah menunjukkan <strong>saldo belum lunas</strong>. Klik untuk detail.</div></section>
        <section class="d2-panel" id="panel-flow"><div class="d2-panel-head"><h3>Jembatan pembayaran</h3><span class="d2-panel-index">07 / WATERFALL</span></div><div class="d2-panel-sub">Dari total nilai menuju sisa piutang · Rp juta</div><div class="d2-chart"><canvas id="d2-flow" role="img" aria-label="Total nilai dikurangi nilai lunas menghasilkan sisa piutang"></canvas><div class="d2-empty">Belum ada nilai publikasi</div></div><div class="d2-foot">Total <strong>− lunas = belum lunas</strong> untuk kwitansi bulan terpilih.</div></section>
        <section class="d2-panel" id="panel-aging"><div class="d2-panel-head"><h3>Spektrum umur piutang</h3><span class="d2-panel-index">08 / AGING</span></div><div class="d2-panel-sub">Saldo terbuka menurut umur sejak tenggat · Rp juta</div><div class="d2-chart"><canvas id="d2-aging" role="img" aria-label="Saldo piutang menurut kelompok umur jatuh tempo"></canvas><div class="d2-empty">Tidak ada saldo piutang</div></div><div class="d2-foot">Tanggal jatuh tempo dihitung terhadap <strong>hari ini</strong>, bukan akhir bulan terpilih.</div></section>
        <section class="d2-panel" id="panel-plans"><div class="d2-panel-head"><h3>Meter realisasi rencana</h3><span class="d2-panel-index">09 / GAUGE</span></div><div class="d2-panel-sub">Kuota rencana {{ $month->translatedFormat('F Y') }}</div><div class="d2-chart"><canvas id="d2-plans" role="img" aria-label="{{ $percent($planRate) }} kuota rencana terpenuhi"></canvas><div class="d2-plan-center"><b>{{ $percent($planRate) }}</b><span>{{ $realized }} dari {{ $quota }} slot</span></div><div class="d2-empty">Belum ada kuota rencana</div></div><div class="d2-foot"><strong>{{ $planDone }}/{{ $plans->count() }}</strong> rencana mencapai kuota. Kuota mengikuti kanal/perusahaan/media; tidak mengikuti status atau hari.</div></section>
    </div>
    <div class="d2-bottom"><span>Perbandingan memakai bulan kalender penuh. Bulan berjalan belum final. Tidak ada proyeksi.</span><a href="{{ route('advertorials.index', $periodFilters) }}" style="color:#47799a">Lihat seluruh data periode ini <i class="bi bi-arrow-up-right"></i></a></div>
    <dialog class="d2-dialog" id="d2-details" aria-labelledby="d2-detail-title">
        <header><div><h2 id="d2-detail-title">Telusuri data terpilih</h2><p>{{ $month->translatedFormat('F Y') }} · {{ $count }} advertorial · menampilkan {{ $detailRows->count() }} nilai terbesar</p></div><div class="d-flex gap-2"><button type="button" data-export-csv>Unduh CSV</button><button type="button" data-close-details aria-label="Tutup detail">×</button></div></header>
        <div class="d2-detail-scroll"><table class="table table-sm align-middle"><thead><tr><th>Invoice / perusahaan</th><th>Media</th><th>Nilai</th><th>Status</th><th>Tenggat</th><th></th></tr></thead><tbody>
        @forelse ($detailRows as $row)
            <tr data-status="{{ $row->status }}"><td><strong>{{ $row->no_invoice }}</strong><small>{{ $row->company->nama_perusahaan ?? 'Perusahaan dihapus' }}</small></td><td>{{ $row->media->nama_media ?? 'Media dihapus' }}</td><td class="text-nowrap">Rp {{ number_format($row->nominal, 0, ',', '.') }}</td><td>{{ ['sudah_dibayar' => 'Lunas', 'belum_dibayar' => 'Belum dibayar', 'jatuh_tempo' => 'Jatuh tempo'][$row->status] }}</td><td>{{ $row->tanggal_jatuh_tempo?->format('d/m/Y') ?? '—' }}</td><td><a href="{{ route('advertorials.edit', $row) }}" aria-label="Buka {{ $row->no_invoice }}"><i class="bi bi-arrow-up-right"></i></a></td></tr>
        @empty<tr><td colspan="6">Tidak ada data untuk fokus ini. Hapus sebagian filter untuk melihat data lainnya.</td></tr>@endforelse
        </tbody></table></div>
        <footer>CSV memuat baris yang ditampilkan pada tabel ini. Tekan Esc untuk menutup.</footer>
    </dialog>
</div>
<script type="application/json" id="d2-data">@json($chartData)</script>
<script type="application/json" id="d2-links">@json(['base' => route('advertorials.index'), 'media' => route('advertorials.media'), 'filters' => $periodFilters])</script>
@endsection
@push('scripts')
<script src="https://cdn.jsdelivr.net/npm/chart.js@4.4.4/dist/chart.umd.min.js"></script>
<script src="{{ asset('js/dashboard-two.js') }}"></script>
@endpush
