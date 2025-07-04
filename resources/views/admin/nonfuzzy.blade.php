@extends('layouts.app')

@section('content')
<h3 class="mb-4">Monitoring Versi Non-Fuzzy</h3>
<form method="GET" action="{{ route('nonfuzzy') }}" class="row g-3 mb-4">
    <div class="col-md-3">
        <label for="tanggal_awal" class="form-label">Tanggal Awal</label>
        <input type="date" name="tanggal_awal" id="tanggal_awal" class="form-control" value="{{ request('tanggal_awal') }}">
    </div>
    <div class="col-md-3">
        <label for="tanggal_akhir" class="form-label">Tanggal Akhir</label>
        <input type="date" name="tanggal_akhir" id="tanggal_akhir" class="form-control" value="{{ request('tanggal_akhir') }}">
    </div>
    <div class="col-md-3 align-self-end">
        <button type="submit" class="btn btn-primary">Cari</button>
        <a href="{{ route('nonfuzzy') }}" class="btn btn-secondary ms-2">Reset</a>
    </div>
</form>
{{-- Filter & Hapus Berdasarkan Tanggal --}}
<form action="{{ route('nonfuzzy.hapus') }}" method="POST" class="row g-3 mb-4">
    @csrf
    <div class="col-md-3">
        <label for="tanggal_awal" class="form-label">Tanggal Awal</label>
        <input type="date" name="tanggal_awal" id="tanggal_awal" class="form-control" value="{{ request('tanggal_awal') }}">
    </div>
    <div class="col-md-3">
        <label for="tanggal_akhir" class="form-label">Tanggal Akhir</label>
        <input type="date" name="tanggal_akhir" id="tanggal_akhir" class="form-control" value="{{ request('tanggal_akhir') }}">
    </div>
    <div class="col-md-3 align-self-end d-flex gap-2">
        <button type="submit" class="btn btn-danger">Hapus Riwayat</button>
        <a href="{{ route('nonfuzzy') }}" class="btn btn-secondary">Reset</a>
    </div>
</form>

{{-- Tabel Non-Fuzzy --}}
<div id="table-wrapper">
    <table class="table table-bordered table-striped table-responsive">
        <thead class="table-dark">
            <tr>
                <th>Tanggal</th>
                <th>Jarak A</th>
                <th>Jarak B</th>
                <th>Tinggi sampah</th>
                <th>Status</th>
                <th>Rekomendasi</th>
            </tr>
        </thead>
        <tbody>
            @forelse($logs as $log)
            <tr>
                <td>{{ \Carbon\Carbon::parse($log->created_at)->timezone('Asia/Jakarta')->format('Y-m-d H:i:s') }}</td>
                <td>{{ $log->jarakA }} cm</td>
                <td>{{ $log->jarakB }} cm</td>
                <td>{{ $log->volume }} cm</td>
                <td>{{ $log->status }}</td>
                <td>{{ $log->rekomendasi }}</td>
            </tr>
            @empty
            <tr>
                <td colspan="6" class="text-center">Tidak ada data.</td>
            </tr>
            @endforelse
        </tbody>
    </table>
    {{ $logs->withQueryString()->links() }}
</div>

{{-- Auto Refresh bagian tabel --}}
<script>
    setInterval(() => {
        fetch(window.location.href, { headers: { 'X-Requested-With': 'XMLHttpRequest' }})
            .then(res => res.text())
            .then(html => {
                const parser = new DOMParser();
                const doc = parser.parseFromString(html, 'text/html');
                const newTable = doc.querySelector('#table-wrapper');
                document.querySelector('#table-wrapper').innerHTML = newTable.innerHTML;
            });
    }, 5000);
</script>

<style>
    @media (max-width: 768px) {
        .table-responsive {
            font-size: 13px;
        }

        form select, form input {
            width: 100%;
        }
    }
</style>
@endsection
