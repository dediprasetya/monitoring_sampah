<table class="table table-bordered table-responsive">
    <thead>
        <tr>
            <th>Tanggal</th>
            <th>Jarak A</th>
            <th>Jarak B</th>
            <th>Tinggi Sampah</th>
            <th>Status</th>
            <th>Rekomendasi</th>
        </tr>
    </thead>
    <tbody>
        @forelse($logs as $log)
            <tr>
                <td>{{ $log->created_at->timezone('Asia/Jakarta')->format('d-m-Y H:i:s') }}</td>
                <td>{{ $log->jarakA }} cm</td>
                <td>{{ $log->jarakB }} cm</td>
                <td>{{ $log->volume }} cm</td>
                <td>{{ $log->status }}</td>
                <td>{{ $log->rekomendasi }}</td>
            </tr>
        @empty
            <tr><td colspan="6">Tidak ada data</td></tr>
        @endforelse
    </tbody>
</table>
<div>
    {{ $logs->links() }}
</div>
