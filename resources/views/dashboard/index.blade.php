@extends('layouts.app')
@section('title', 'Dashboard')

@section('content')
<style>
    .main-content { padding: 16px 22px; }
    .topbar { margin-bottom: 10px; }
    .topbar h1 { font-size: 1.2rem; }
    .dashboard { --ink: #172f4f; --blue: #357ded; --green: #149c80; --red: #dc5765; --amber: #d69a25; }
    .dash-heading { display:flex; justify-content:space-between; align-items:center; gap:12px; margin-bottom:12px; }
    .dash-eyebrow { font-size:10px; letter-spacing:.12em; font-weight:700; color:#698099; text-transform:uppercase; }
    .dash-subtitle { font-size:11px; color:#718096; margin-top:3px; }
    .dash-period { font-size:11px; border:1px solid #dfe7f0; border-radius:8px; padding:7px 10px; background:#fff; white-space:nowrap; }
    .dash-kpis { display:grid; grid-template-columns:repeat(6,minmax(0,1fr)); gap:10px; margin-bottom:10px; }
    .dash-kpi { text-decoration:none; color:var(--ink); background:white; padding:11px 12px; border:1px solid #e7edf4; border-radius:10px; min-width:0; }
    .dash-kpi:hover { border-color:#a2bfe5; background:#fafdff; }
    .dash-kpi span { font-size:10px; color:#697c91; display:block; margin-bottom:3px; }
    .dash-kpi strong { font-size:clamp(14px,1.4vw,21px); letter-spacing:-.6px; display:block; white-space:nowrap; }
    .dash-kpi small { font-size:10px; color:#7a899b; }
    .dash-insights { display:flex; flex-wrap:wrap; gap:8px 18px; padding:8px 12px; border-radius:8px; background:#eaf1fa; margin-bottom:12px; font-size:11px; color:#415b78; }
    .dash-insights a { color:#a93649; text-decoration:none; }
    .dash-grid { display:grid; grid-template-columns:repeat(3,minmax(0,1fr)); gap:10px; }
    .chart-card { min-width:0; border:1px solid #e3eaf2; border-radius:11px; padding:10px 12px 8px; background:#fff; }
    .chart-heading { display:flex; align-items:center; justify-content:space-between; gap:6px; margin-bottom:5px; }
    .chart-heading h2 { font-size:12px; font-weight:700; color:var(--ink); margin:0; }
    .chart-number { font-size:9px; font-weight:600; color:#8597ad; margin-right:6px; }
    .chart-download { border:0; background:transparent; color:#8293a7; padding:0 3px; border-radius:4px; font-size:12px; }
    .chart-download:hover { background:#eaf1fa; color:var(--blue); }
    .chart-frame { height:clamp(105px,15vh,150px); position:relative; }
    .chart-note { font-size:9px; color:#78899c; margin:5px 0 0; white-space:nowrap; overflow:hidden; text-overflow:ellipsis; }
    .chart-empty { display:none; position:absolute; inset:0; align-items:center; justify-content:center; color:#7b8b9e; font-size:12px; background:#fff; }
    .chart-card.is-empty .chart-empty { display:flex; }
    .dash-footer { display:flex; justify-content:space-between; flex-wrap:wrap; gap:8px; margin-top:9px; font-size:10px; color:#7b8b9e; }
    .dash-details { margin-top:16px; background:white; border:1px solid #e3eaf2; border-radius:10px; padding:12px; }
    .dash-details summary { cursor:pointer; font-size:12px; font-weight:600; color:var(--ink); }
    .dash-details td, .dash-details th { font-size:11px !important; }
    .dash-details a { text-decoration:none; }
    .chart-failure { display:none; background:#fff4e6; color:#855c12; padding:10px; border-radius:8px; font-size:12px; margin-bottom:12px; }
    @media(min-width:1600px) { .chart-frame { height:clamp(150px,17vh,200px); } }
    @media(max-width:1100px) { .dash-kpis { grid-template-columns:repeat(3,minmax(0,1fr)); } .chart-heading h2 { font-size:11px; } }
    @media(max-width:850px) { .dash-grid { grid-template-columns:repeat(2,minmax(0,1fr)); } .chart-frame { height:155px; } }
    @media(max-width:600px) {
        .sidebar { width:70px; padding:12px 6px; } .sidebar .brand-text,.sidebar .link-label { display:none; }
        .sidebar .nav-link { justify-content:center; padding:10px; } .sidebar .brand { justify-content:center; }
        .main-content,.main-content.collapsed { margin-left:70px; padding:12px; }
        .dash-grid { grid-template-columns:1fr; } .dash-kpis { grid-template-columns:repeat(2,minmax(0,1fr)); }
        .dash-heading { align-items:flex-start; flex-direction:column; } .chart-frame { height:180px; }
    }
.dash-range {display:flex;gap:7px;align-items:end;flex-wrap:wrap;font-size:10px;} .dash-range label {display:grid;gap:3px;color:#718096;} .dash-range input,.dash-range button,.dash-range a {padding:5px 7px;border:1px solid #dce5ef;border-radius:6px;background:#fff;color:#37536e;font-size:10px;}
.chart-card.is-expanded {position:fixed;inset:4vh 4vw;z-index:1050;box-shadow:0 0 0 100vmax #112638bb;display:flex;flex-direction:column;padding:22px;}
    .chart-card.is-expanded .chart-frame {flex:1;min-height:0;height:auto;}
    .chart-card.is-expanded .chart-heading h2 {font-size:18px;}
    .chart-card.is-expanded .chart-note {font-size:12px;white-space:normal;}
    .chart-card.is-expanded .chart-download {font-size:18px;}
</style>
@php
    $money = fn ($value) => 'Rp '.number_format($value / 1000000, 1, ',', '.').' jt';
    $filters = ['dari_tanggal' => $start->format('Y-m-d'), 'sampai_tanggal' => $end->format('Y-m-d')];
    $cards = [
        ['trend', 'Tren nilai advertorial', 'Nilai kwitansi per bulan · Rp juta'],
        ['status', 'Komposisi pembayaran', 'Jumlah advertorial menurut status tersimpan'],
        ['type', 'Kontribusi cetak & online', 'Nilai advertorial menurut jenis media · Rp juta'],
        ['volume', 'Volume & status bulanan', 'Jumlah advertorial berdasarkan bulan kwitansi'],
        ['companies', '5 perusahaan terbesar', 'Peringkat berdasarkan total nilai · Rp juta'],
        ['media', '5 media terbesar', 'Peringkat berdasarkan total nilai · Rp juta'],
        ['aging', 'Umur piutang', 'Saldo belum lunas menurut tanggal jatuh tempo · Rp juta'],
        ['payments', 'Tren penerimaan pembayaran', 'Tanggal dibayar, termasuk kwitansi di luar periode · Rp juta'],
        ['plans', 'Capaian rencana periode', 'Jumlah rencana pada periode pilihan'],
    ];
@endphp
<div class="dashboard">
    @if ($errors->any())<div class="alert alert-danger">{{ $errors->first() }}</div>@endif
    <div class="dash-heading">
        <div><div class="dash-eyebrow">Ringkasan kinerja publikasi</div><div class="dash-subtitle">9 diagram · periode pilihan · bulan berjalan belum final</div></div>
        <form method="GET" class="dash-range"><label>Mulai <input type="month" name="bulan_awal" value="{{ $start->format('Y-m') }}" required aria-label="Bulan awal"></label><label>Sampai <input type="month" name="bulan_akhir" value="{{ $end->format('Y-m') }}" required aria-label="Bulan akhir"></label><button type="submit">Terapkan</button><a href="{{ route('dashboard') }}">Reset</a></form>
    </div>
    <div class="dash-kpis">
        <a class="dash-kpi" href="{{ route('advertorials.index', $filters) }}"><span>Total advertorial</span><strong>{{ number_format($totalData, 0, ',', '.') }}</strong><small>{{ $monthCount }} bulan terpilih</small></a>
        <a class="dash-kpi" href="{{ route('advertorials.index', $filters) }}" title="Rp {{ number_format($totalNominal, 0, ',', '.') }}"><span>Nilai publikasi</span><strong>{{ $money($totalNominal) }}</strong><small>berdasarkan kwitansi</small></a>
        <a class="dash-kpi" href="{{ route('advertorials.index', $filters + ['status' => 'sudah_dibayar']) }}"><span>Sudah dibayar</span><strong style="color:var(--green)">{{ $money($totalSudahDibayar) }}</strong><small>{{ $paidRate }}% dari nilai publikasi</small></a>
        <div class="dash-kpi"><span>Belum lunas</span><strong style="color:var(--amber)">{{ $money($totalBelumDibayar) }}</strong><small>termasuk jatuh tempo</small></div>
        <div class="dash-kpi"><span>Lewat jatuh tempo</span><strong style="color:var(--red)">{{ $money($overdueTotal) }}</strong><small>{{ $overdue->count() }} tagihan · dihitung dari tenggat</small></div>
        <a class="dash-kpi" href="{{ route('plans.index') }}"><span>Realisasi kuota periode</span><strong>{{ $planRate }}%</strong><small>{{ $realized }} / {{ $quota }} slot terpenuhi</small></a>
    </div>
    <div class="dash-insights">
        <span><i class="bi bi-graph-up-arrow me-1"></i> Nilai bulan terakhir: <strong>{{ $growth === null ? 'belum ada pembanding' : ($growth > 0 ? '+' : '').$growth.'%' }}</strong> vs bulan sebelumnya di rentang</span>
        <span><i class="bi bi-pie-chart me-1"></i> Porsi belum lunas: <strong>{{ $totalNominal > 0 ? round($totalBelumDibayar / $totalNominal * 100, 1) : 0 }}%</strong></span>
        <span><i class="bi bi-bullseye me-1"></i> <strong>{{ $charts['planCounts'][0] }} / {{ $plans->count() }}</strong> rencana mencapai kuota</span>
    </div>
    <div id="chartFailure" class="chart-failure" role="alert">Grafik belum dapat dimuat. Periksa koneksi internet lalu muat ulang halaman. Ringkasan angka tetap tersedia.</div>
    <noscript><p class="chart-failure" style="display:block">Aktifkan JavaScript untuk menampilkan sembilan diagram.</p></noscript>
    <div class="dash-grid">
        @foreach ($cards as [$id, $title, $note])
            <section class="chart-card" id="card-{{ $id }}" aria-labelledby="title-{{ $id }}">
                <div class="chart-heading"><h2 id="title-{{ $id }}"><span class="chart-number">{{ sprintf('%02d', $loop->iteration) }}</span>{{ $title }}</h2><div class="d-flex gap-2"><button type="button" class="chart-download" data-expand-card="{{ $id }}" aria-expanded="false" aria-label="Perbesar {{ $title }}" title="Perbesar kartu"><i class="bi bi-arrows-fullscreen"></i></button><button type="button" class="chart-download" data-card-csv="{{ $id }}" title="Unduh data CSV" aria-label="Unduh data {{ $title }} sebagai CSV"><i class="bi bi-filetype-csv"></i></button><button type="button" class="chart-download" data-chart="{{ $id }}" title="Unduh {{ $title }}" aria-label="Unduh {{ $title }} sebagai PNG"><i class="bi bi-download"></i></button></div></div>
                <div class="chart-frame"><canvas id="chart-{{ $id }}" role="img" aria-label="{{ $title }}. {{ $note }}"></canvas><div class="chart-empty">Belum ada data untuk periode ini</div></div>
                <p class="chart-note" title="{{ $note }}">{{ $note }}</p>
            </section>
        @endforeach
    </div>
    <div class="dash-footer"><span>Klik grafik tren, status, jenis media, atau peringkat untuk membuka data terkait.</span><span>Nilai dalam juta rupiah · diperbarui {{ now('Asia/Jakarta')->format('d/m/Y H:i') }} WIB</span></div>
    <details class="dash-details">
        <summary>Rincian {{ $plans->count() }} rencana iklan periode pilihan <span class="text-muted fw-normal">— buka untuk melihat perusahaan & kuota</span></summary>
        <div class="table-responsive mt-3"><table class="table table-custom mb-0"><thead><tr><th>Perusahaan</th><th>Media</th><th>Kuota</th><th>Realisasi</th><th>Capaian</th></tr></thead><tbody>
            @forelse ($plans as $plan)
                <tr><td><a href="{{ route('plans.edit', $plan) }}">{{ $plan->company->nama_perusahaan ?? 'Perusahaan dihapus' }}</a></td><td>{{ $plan->media->nama_media ?? 'Media dihapus' }}</td><td>{{ $plan->kuota }}</td><td>{{ $plan->advertorials_count }}</td><td>{{ $plan->kuota > 0 ? min(100, round($plan->advertorials_count / $plan->kuota * 100)) : 100 }}%</td></tr>
            @empty
                <tr><td colspan="5" class="text-muted text-center">Belum ada rencana di periode ini. <a href="{{ route('plans.create') }}">Buat rencana</a></td></tr>
            @endforelse
        </tbody></table></div>
    </details>
</div>
@endsection

@push('scripts')
<script src="https://cdn.jsdelivr.net/npm/chart.js@4.4.4/dist/chart.umd.min.js"></script>
<script>
(() => {
    if (typeof Chart === 'undefined') { document.getElementById('chartFailure').style.display = 'block'; return; }
    const d = @json($charts);
    const baseUrl = @json(route('advertorials.index'));
    const period = @json($filters);
    const colors = ['#149c80', '#d69a25', '#dc5765'];
    const months = d.monthly.map(m => m.label);
    const money = new Intl.NumberFormat('id-ID', { style:'currency', currency:'IDR', maximumFractionDigits:0 });
    const charts = {};
    Chart.defaults.font.family = 'Inter, sans-serif';
    Chart.defaults.font.size = 10;
    Chart.defaults.color = '#78899c';
    const open = params => { window.location.href = baseUrl + '?' + new URLSearchParams({...period, ...params}); };
    const monthlyClick = elements => {
        const month = d.monthly[elements[0].index];
        open({dari_tanggal:month.from, sampai_tanggal:month.to});
    };
    const dataset = (label, data, color, extra = {}) => ({label, data, backgroundColor:color, borderColor:color, borderWidth:0, borderRadius:3, ...extra});
    function draw(id, type, labels, datasets, {currency = false, horizontal = false, stacked = false, click = null, legend = false} = {}) {
        const round = type === 'doughnut';
        const values = datasets.flatMap(s => s.data);
        const card = document.getElementById('card-' + id);
        card.classList.toggle('is-empty', values.every(v => Number(v) === 0));
        const valueTicks = { maxTicksLimit:4, callback:v => currency ? new Intl.NumberFormat('id-ID', {maximumFractionDigits:1}).format(v / 1000000) : Number.isInteger(v) ? v : '' };
        const options = {
            maintainAspectRatio:false, responsive:true, animation:{duration:350},
            indexAxis:horizontal ? 'y' : 'x',
            interaction:{mode:round ? 'nearest' : 'index', intersect:false},
            plugins:{
                legend:{display:legend, position:'bottom', labels:{boxWidth:7, boxHeight:7, usePointStyle:true, padding:7, font:{size:9}}},
                tooltip:{callbacks:{
                    title:items => labels[items[0].dataIndex],
                    label:ctx => ' ' + (ctx.dataset.label || ctx.label) + ': ' + (currency ? money.format(ctx.raw) : ctx.raw + (id === 'plans' ? ' rencana' : ' advertorial'))
                }}
            },
            onHover:(event, elements) => { event.native.target.style.cursor = click && elements.length ? 'pointer' : 'default'; },
            onClick:(event, elements) => { if (click && elements.length) click(elements); }
        };
        if (round) { options.cutout = '68%'; options.plugins.tooltip.callbacks.title = () => ''; }
        else {
            options.scales = {
                x:{stacked, beginAtZero:horizontal, grid:{display:horizontal, color:'#eff3f8'}, border:{display:false}, ticks:horizontal ? valueTicks : {maxRotation:0, autoSkip:true, maxTicksLimit:6, font:{size:9}}},
                y:{stacked, beginAtZero:!horizontal, grid:{display:!horizontal, color:'#eff3f8'}, border:{display:false}, ticks:horizontal ? {font:{size:9}, callback:function(v) { const text = this.getLabelForValue(v); return text.length > 22 ? text.slice(0,11) + '…' + text.slice(-10) : text; }} : valueTicks}
            };
        }
        charts[id] = new Chart(document.getElementById('chart-' + id), {type, data:{labels, datasets}, options});
    }
    draw('trend', 'line', months, [dataset('Nilai kwitansi', d.monthly.map(m => m.nominal), '#357ded', {borderWidth:2, backgroundColor:'rgba(53,125,237,.09)', fill:true, tension:.3, pointRadius:2})], {currency:true, click:monthlyClick});
    draw('status', 'doughnut', ['Lunas', 'Belum dibayar', 'Jatuh tempo'], [dataset('Status', d.statusCounts, colors)], {legend:true, click:e => open({status:['sudah_dibayar','belum_dibayar','jatuh_tempo'][e[0].index]})});
    draw('type', 'bar', ['Cetak', 'Online'], [dataset('Nilai publikasi', d.typeValues, ['#29496e','#6b9fed'])], {currency:true, horizontal:true, click:e => { window.location.href = @json(route('advertorials.media')) + '?' + new URLSearchParams({...period, tipe:['cetak','online'][e[0].index]}); }});
    draw('volume', 'bar', months, ['Lunas','Belum dibayar','Jatuh tempo'].map((label,i) => dataset(label, d.monthly.map(m => m.statuses[i]), colors[i])), {stacked:true, legend:true, click:monthlyClick});
    draw('companies', 'bar', d.topCompanies.map(r => r.label), [dataset('Nilai publikasi', d.topCompanies.map(r => r.total), '#357ded')], {currency:true, horizontal:true, click:e => open({company_id:d.topCompanies[e[0].index].id})});
    draw('media', 'bar', d.topMedia.map(r => r.label), [dataset('Nilai publikasi', d.topMedia.map(r => r.total), '#7b72d2')], {currency:true, horizontal:true, click:e => open({media_id:d.topMedia[e[0].index].id})});
    draw('aging', 'bar', ['Belum lewat', '1–30 hari', '31–60 hari', '>60 hari', 'Tanpa tenggat'], [dataset('Saldo piutang', d.aging, ['#6b9fed','#e9b654','#e88e54','#dc5765','#a1afc1'])], {currency:true});
    draw('payments', 'line', months, [dataset('Penerimaan', d.monthly.map(m => m.paid), '#149c80', {borderWidth:2, backgroundColor:'rgba(20,156,128,.09)', fill:true, tension:.3, pointRadius:3})], {currency:true});
    draw('plans', 'doughnut', ['Tercapai','Berjalan','Belum mulai'], [dataset('Rencana', d.planCounts, ['#149c80','#357ded','#d9e2ef'])], {legend:true});
    document.querySelectorAll('[data-chart]').forEach(button => button.addEventListener('click', () => {
        const id = button.dataset.chart;
        const link = document.createElement('a');
        link.href = charts[id].toBase64Image(); link.download = 'dashboard-' + id + '.png'; link.click();
    }));
    const cardExports = {
        trend: [['Bulan','Nilai kwitansi (IDR)'],d.monthly.map(row=>[row.label,row.nominal])],
        payments: [['Bulan pembayaran','Penerimaan (IDR)'],d.monthly.map(row=>[row.label,row.paid])],
        volume: [['Bulan','Lunas (jumlah)','Belum dibayar (jumlah)','Jatuh tempo (jumlah)','Total (jumlah)'],d.monthly.map(row=>[row.label,...row.statuses,row.count])],
        status: [['Status','Jumlah advertorial'],['Lunas','Belum dibayar','Jatuh tempo'].map((name,i)=>[name,d.statusCounts[i]])],
        type: [['Jenis media','Nilai publikasi (IDR)'],['Cetak','Online'].map((name,i)=>[name,d.typeValues[i]])],
        companies: [['ID perusahaan','Perusahaan','Nilai publikasi (IDR)'],d.topCompanies.map(row=>[row.id,row.label,row.total])],
        media: [['ID media','Media','Nilai publikasi (IDR)'],d.topMedia.map(row=>[row.id,row.label,row.total])],
        aging: [['Umur piutang','Saldo belum lunas (IDR)'],['Belum lewat','1–30 hari','31–60 hari','>60 hari','Tanpa tenggat'].map((name,i)=>[name,d.aging[i]])],
        plans: [['Capaian rencana','Jumlah rencana'],['Tercapai','Berjalan','Belum mulai'].map((name,i)=>[name,d.planCounts[i]])]
    };
    document.querySelectorAll('[data-card-csv]').forEach(button=>button.addEventListener('click',()=>{
        const id=button.dataset.cardCsv;const [headers,rows]=cardExports[id];
        DashboardExport.download('dashboard-1-'+id+'_'+period.dari_tanggal+'_'+period.sampai_tanggal,
            ['Periode awal','Periode akhir',...headers],rows.map(row=>[period.dari_tanggal,period.sampai_tanggal,...row]));
    }));
    function closeExpandedCard() {
        const card = document.querySelector('.chart-card.is-expanded');
        if(!card) return;
        card.classList.remove('is-expanded');
        const button=card.querySelector('[data-expand-card]');
        button.setAttribute('aria-expanded','false');button.title='Perbesar kartu';button.querySelector('i').className='bi bi-arrows-fullscreen';button.focus();
        Object.values(charts).forEach(chart=>chart.resize());
    }
    document.querySelectorAll('[data-expand-card]').forEach(button=>button.addEventListener('click',()=>{
        const card=document.getElementById('card-'+button.dataset.expandCard);
        const wasExpanded=card.classList.contains('is-expanded');closeExpandedCard();
        if(!wasExpanded) {card.classList.add('is-expanded');button.setAttribute('aria-expanded','true');button.title='Kecilkan kartu (Esc)';button.querySelector('i').className='bi bi-fullscreen-exit';}
        Object.values(charts).forEach(chart=>chart.resize());
    }));
    document.addEventListener('keydown',event=>{if(event.key==='Escape')closeExpandedCard();});
    new ResizeObserver(() => Object.values(charts).forEach(chart => chart.resize())).observe(document.querySelector('.dash-grid'));
})();
</script>
@endpush
