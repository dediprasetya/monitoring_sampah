@extends('layouts.app')
@section('content')
<meta http-equiv="refresh" content="5"> <!-- Auto-refresh setiap 5 detik -->
<h3>Monitoring Versi Non-Fuzzy</h3>
<table class="table table-striped">
    <thead>
        <tr>
            <th>Tanggal</th>
            <th>Jarak A</th>
            <th>Jarak B</th>
            <th>Volume</th>
            <th>Status</th>
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
        </tr>
        @endforeach
    </tbody>
</table>
{{ $logs->links() }}
@endsection
