@extends('layouts.app')
@section('content')
<meta http-equiv="refresh" content="5"> <!-- Auto-refresh setiap 5 detik -->
<form action="{{ route('riwayat.hapus') }}" method="POST" class="row g-3 mb-4">
    @csrf
    <div class="col-md-3">
        <label for="tanggal_awal" class="form-label">Tanggal Awal</label>
        <input type="date" name="tanggal_awal" id="tanggal_awal" class="form-control" required>
    </div>
    <div class="col-md-3">
        <label for="tanggal_akhir" class="form-label">Tanggal Akhir</label>
        <input type="date" name="tanggal_akhir" id="tanggal_akhir" class="form-control" required>
    </div>
    <div class="col-md-3 align-self-end">
        <button type="submit" class="btn btn-danger">Hapus Riwayat</button>
    </div>
</form>
<h3>Riwayat Monitoring</h3>
<table class="table table-bordered">
    <thead>
        <tr>
            <th>Tanggal</th>
            <th>Jarak A</th>
            <th>Jarak B</th>
            <th>Volume</th>
            <th>Status</th>
            <th>Rekomendasi</th>
        </tr>
    </thead>
    <tbody>
        @foreach($logs as $log)
        <tr>
            <td>{{ $log->created_at }}</td>
            <td>{{ $log->jarakA }} cm</td>
            <td>{{ $log->jarakB }} cm</td>
            <td>{{ $log->volume }} cm³</td>
            <td>{{ $log->status }}</td>
            <td>{{ $log->rekomendasi }}</td>
        </tr>
        @endforeach
    </tbody>
</table>
{{ $logs->links() }}
@endsection
