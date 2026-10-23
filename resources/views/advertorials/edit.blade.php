@extends('layouts.app')
@section('title', 'Edit Data Advertorial')

@section('content')
    <div class="card-custom" style="max-width: 720px;">

        @if ($errors->any())
            <div class="alert alert-danger">
                <ul class="mb-0">
                    @foreach ($errors->all() as $error)
                        <li>{{ $error }}</li>
                    @endforeach
                </ul>
            </div>
        @endif

        <form action="{{ route('advertorials.update', $advertorial) }}" method="POST" enctype="multipart/form-data">
            @csrf
            @method('PUT')

            <div class="mb-3">
                <label class="form-label">Perusahaan</label>
                <div class="input-group">
                    <select name="company_id" class="form-select" required>
                        <option value="">-- Pilih Perusahaan --</option>
                        @foreach ($companies as $company)
                            <option value="{{ $company->id }}" @selected(old('company_id', $advertorial->company_id) == $company->id)>{{ $company->nama_perusahaan }}</option>
                        @endforeach
                    </select>
                    <a href="{{ route('companies.create') }}" class="btn btn-outline-secondary" title="Tambah perusahaan baru">
                        <i class="bi bi-plus-lg"></i>
                    </a>
                </div>
            </div>

            <div class="mb-3">
                <label class="form-label">Media</label>
                <select name="media_id" class="form-select" required>
                    <option value="">-- Pilih Media --</option>
                    @foreach ($mediaList as $media)
                        <option value="{{ $media->id }}" @selected(old('media_id', $advertorial->media_id) == $media->id)>{{ $media->nama_media }} ({{ ucfirst($media->tipe) }})</option>
                    @endforeach
                </select>
            </div>

            <div class="mb-3">
                <label class="form-label">Planning Iklan (opsional)</label>
                <select name="plan_id" id="plan_id" class="form-select">
                    <option value="">-- Pilih Planning Iklan --</option>
                    @foreach ($plans as $plan)
                        @if ($plan['company_id'] == old('company_id', $advertorial->company_id) && $plan['media_id'] == old('media_id', $advertorial->media_id))
                            <option value="{{ $plan['id'] }}" @selected(old('plan_id', $advertorial->plan_id) == $plan['id'])>{{ $plan['label'] }}</option>
                        @endif
                    @endforeach
                </select>
                <small class="text-muted">Jika data ini terkait dengan planning iklan yang sudah dibuat, pilih planning iklan yang sesuai. Jika tidak, biarkan kosong.</small>
            </div>

            <div class="mb-3">
                <label class="form-label">Judul Advertorial / Rilis</label>
                <input type="text" name="judul" class="form-control" value="{{ old('judul', $advertorial->judul) }}" required>
            </div>

            <div class="row">
                <div class="col-md-6 mb-3">
                    <label class="form-label">Waktu Tayang</label>
                    <input type="date" name="waktu_tayang" class="form-control" value="{{ old('waktu_tayang', $advertorial->waktu_tayang->format('Y-m-d')) }}" required>
                </div>
                <div class="col-md-6 mb-3">
                    <label class="form-label">Tanggal Kwitansi</label>
                    <input type="date" name="tanggal_kwitansi" class="form-control" value="{{ old('tanggal_kwitansi', $advertorial->tanggal_kwitansi->format('Y-m-d')) }}" required>
                </div>
            </div>

            @php
                $statusLama = $advertorial->status === 'sudah_dibayar' ? 'sudah' : 'belum';
            @endphp

            <div class="mb-3">
                <label class="form-label d-block">Status Pembayaran</label>
                <div class="btn-group" role="group">
                    <input type="radio" class="btn-check" name="status_bayar" id="status_belum" value="belum" {{ old('status_bayar', $statusLama) === 'belum' ? 'checked' : '' }}>
                    <label class="btn btn-outline-warning" for="status_belum">Belum Dibayar</label>

                    <input type="radio" class="btn-check" name="status_bayar" id="status_sudah" value="sudah" {{ old('status_bayar', $statusLama) === 'sudah' ? 'checked' : '' }}>
                    <label class="btn btn-outline-success" for="status_sudah">Sudah Dibayar</label>
                </div>
            </div>

            <div class="row">
                <div class="col-md-6 mb-3" id="wrapper_jatuh_tempo">
                    <label class="form-label">Tenggat / Jatuh Tempo Pembayaran</label>
                    <input type="date" name="tanggal_jatuh_tempo" class="form-control"
                        value="{{ old('tanggal_jatuh_tempo', $advertorial->tanggal_jatuh_tempo?->format('Y-m-d')) }}">
                </div>
                <div class="col-md-6 mb-3" id="wrapper_dibayar">
                    <label class="form-label">Tanggal Dibayar</label>
                    <input type="date" name="tanggal_dibayar" class="form-control"
                        value="{{ old('tanggal_dibayar', $advertorial->tanggal_dibayar?->format('Y-m-d')) }}">
                </div>
            </div>

            <div class="mb-3">
                <label class="form-label">Nominal</label>
                <div class="input-group">
                    <span class="input-group-text">Rp</span>
                    <input type="text" id="nominal_display" class="form-control" inputmode="numeric" autocomplete="off">
                    <input type="hidden" name="nominal" id="nominal" value="{{ old('nominal', (int) $advertorial->nominal) }}">
                </div>
            </div>

            <div class="mb-3">
                <label class="form-label">File Kwitansi (opsional)</label>
                @if ($advertorial->file_kwitansi)
                    <div class="mb-2">
                        <a href="{{ asset('storage/' . $advertorial->file_kwitansi) }}" target="_blank">
                            <i class="bi bi-paperclip"></i> Lihat file saat ini
                        </a>
                    </div>
                @endif
                <input type="file" name="file_kwitansi" class="form-control" accept=".pdf,.jpg,.jpeg,.png">
                <small class="text-muted">Kosongkan jika tidak ingin mengganti file</small>
            </div>

            <div class="mb-3">
                <label class="form-label">Catatan</label>
                <textarea name="catatan" class="form-control" rows="3">{{ old('catatan', $advertorial->catatan) }}</textarea>
            </div>

            <button type="submit" class="btn btn-primary-custom">Update</button>
            <a href="{{ route('advertorials.index') }}" class="btn btn-outline-secondary">Batal</a>
        </form>
    </div>
@endsection

@push('scripts')
    @include('partials.rupiah-script')
    <script>
        const allPlans = @json($plans);
        const selectedPlanId = @json(old('plan_id', $advertorial->plan_id));

        document.addEventListener('DOMContentLoaded', function () {
            initRupiahInput('nominal_display', 'nominal');

            const companySelect = document.querySelector('select[name="company_id"]');
            const mediaSelect = document.querySelector('select[name="media_id"]');
            const planSelect = document.getElementById('plan_id');

            function updatePlanOptions(preselectId = null) {
                const companyId = companySelect.value;
                const mediaId = mediaSelect.value;

                planSelect.innerHTML = '<option value="">-- Tidak termasuk planning manapun --</option>';

                if (!companyId || !mediaId) return;

                const filtered = allPlans.filter(p => p.company_id == companyId && p.media_id == mediaId);

                filtered.forEach(p => {
                    const opt = document.createElement('option');
                    opt.value = p.id;
                    opt.textContent = p.label;
                    if (preselectId && p.id == preselectId) opt.selected = true;
                    planSelect.appendChild(opt);
                });
            }

            companySelect.addEventListener('change', () => updatePlanOptions());
            mediaSelect.addEventListener('change', () => updatePlanOptions());

            // set kondisi awal saat halaman dibuka (data lama)
            updatePlanOptions(selectedPlanId);

            const radioBelum = document.getElementById('status_belum');
            const radioSudah = document.getElementById('status_sudah');
            const wrapperJatuhTempo = document.getElementById('wrapper_jatuh_tempo');
            const wrapperDibayar = document.getElementById('wrapper_dibayar');

            function toggleStatusFields() {
                if (radioSudah.checked) {
                    wrapperJatuhTempo.classList.add('d-none');
                    wrapperDibayar.classList.remove('d-none');
                } else {
                    wrapperDibayar.classList.add('d-none');
                    wrapperJatuhTempo.classList.remove('d-none');
                }
            }

            radioBelum.addEventListener('change', toggleStatusFields);
            radioSudah.addEventListener('change', toggleStatusFields);
            toggleStatusFields();
        });
    </script>
@endpush