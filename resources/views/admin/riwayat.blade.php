@extends('layouts.app')
@section('content')

<h3>Riwayat Monitoring</h3>

{{-- Filter Form --}}
<form id="filterForm" method="GET" class="row g-3 mb-3">
    <div class="col-md-3">
        <label for="tanggal_awal" class="form-label">Tanggal Awal</label>
        <input type="date" name="tanggal_awal" id="tanggal_awal" class="form-control" value="{{ request('tanggal_awal') }}">
    </div>
    <div class="col-md-3">
        <label for="tanggal_akhir" class="form-label">Tanggal Akhir</label>
        <input type="date" name="tanggal_akhir" id="tanggal_akhir" class="form-control" value="{{ request('tanggal_akhir') }}">
    </div>
    <div class="col-md-3 d-flex align-items-end gap-2">
        <button type="submit" class="btn btn-primary">Cari</button>
    </div>
</form>

{{-- Hapus Riwayat --}}
<form action="{{ route('riwayat.hapus') }}" method="POST" class="mb-4">
    @csrf
    <div class="row g-2">
        <div class="col-md-3">
            <input type="date" name="tanggal_awal" class="form-control" required>
        </div>
        <div class="col-md-3">
            <input type="date" name="tanggal_akhir" class="form-control" required>
        </div>
        <div class="col-md-3">
            <button type="submit" class="btn btn-danger">Hapus Riwayat</button>
        </div>
    </div>
</form>

{{-- Tabel Riwayat --}}
<div id="tabel-riwayat">
    @include('partials.tabel-riwayat', ['logs' => $logs])
</div>

{{-- Auto Refresh --}}
<script>
    setInterval(function () {
        fetch("{{ url()->current() . '?ajax=1' }}")
            .then(res => res.text())
            .then(html => {
                const parser = new DOMParser();
                const doc = parser.parseFromString(html, 'text/html');
                const newTable = doc.querySelector('#tabel-riwayat');
                if (newTable) {
                    document.getElementById('tabel-riwayat').innerHTML = newTable.innerHTML;
                }
            });
    }, 5000);
</script>

@endsection
