@extends('layouts.app')

@section('content')
    <h3 class="mb-4">Analisis Perbandingan Fuzzy dan Non-Fuzzy</h3>

    {{-- Filter Bin --}}
    <form method="GET" action="{{ route('analisis.index') }}" class="row g-3 mb-3">
        <div class="col-md-3">
            <label for="bin_id" class="form-label">Pilih Tempat Sampah</label>
            <select name="bin_id" id="bin_id" class="form-select" onchange="this.form.submit()">
                <option value="">Semua Bin</option>
                @foreach($availableBins as $bin)
                    <option value="{{ $bin }}" {{ request('bin_id') == $bin ? 'selected' : '' }}>
                        {{ strtoupper($bin) }}
                    </option>
                @endforeach
            </select>
        </div>
    </form>

    <a href="{{ route('analisis.export', request()->query()) }}" class="btn btn-success mb-3">
        <i class="bi bi-download"></i> Export Excel
    </a>

    <div id="analisis-table">
        <div class="table-responsive">
            <table class="table table-bordered table-striped table-hover">
                <thead class="table-dark text-center">
                    <tr>
                        <th>Bin</th>
                        <th>Waktu</th>
                        <th colspan="3">Fuzzy</th>
                        <th colspan="3">Non-Fuzzy</th>
                    </tr>
                    <tr>
                        <th></th>
                        <th></th>
                        <th>Tinggi Sampah</th>
                        <th>Status</th>
                        <th>Rekomendasi</th>
                        <th>Tinggi Sampah</th>
                        <th>Status</th>
                        <th>Rekomendasi</th>
                    </tr>
                </thead>
                <tbody>
                    @forelse ($data as $row)
                        <tr>
                            <td class="text-center">{{ $row['bin_id'] ?? '-' }}</td>
                            <td>{{ $row['waktu'] }}</td>
                            <td>{{ $row['fuzzy_volume'] }}</td>
                            <td>{{ $row['fuzzy_status'] }}</td>
                            <td>{{ $row['fuzzy_rekomendasi'] }}</td>
                            <td>{{ $row['non_volume'] }}</td>
                            <td>{{ $row['non_status'] }}</td>
                            <td>{{ $row['non_rekomendasi'] }}</td>
                        </tr>
                    @empty
                        <tr>
                            <td colspan="8" class="text-center">Tidak ada data tersedia.</td>
                        </tr>
                    @endforelse
                </tbody>
            </table>
        </div>

        {{-- Pagination --}}
        <div class="d-flex justify-content-center">
            {{ $data->withQueryString()->links() }}
        </div>
    </div>

    {{-- Auto Refresh --}}
    <script>
        setInterval(() => {
            fetch(window.location.href, { headers: { 'X-Requested-With': 'XMLHttpRequest' }})
                .then(res => res.text())
                .then(html => {
                    const parser = new DOMParser();
                    const doc = parser.parseFromString(html, 'text/html');
                    const newTable = doc.querySelector('#analisis-table');
                    if (newTable) {
                        document.querySelector('#analisis-table').innerHTML = newTable.innerHTML;
                    }
                });
        }, 5000);
    </script>

    <style>
        @media (max-width: 768px) {
            .table-responsive {
                font-size: 12px;
            }
            table th, table td {
                white-space: nowrap;
            }
        }
    </style>
@endsection
