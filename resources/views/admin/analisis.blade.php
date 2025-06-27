@extends('layouts.app')

@section('content')
    <h3 class="mb-4">Analisis Perbandingan Fuzzy dan Non-Fuzzy</h3>

    <a href="{{ route('analisis.export') }}" class="btn btn-success mb-3">
        <i class="bi bi-download"></i> Export Excel
    </a>

    <div class="table-responsive">
        <table class="table table-bordered table-striped table-hover">
            <thead class="table-dark text-center">
                <tr>
                    <th>Waktu</th>
                    <th colspan="3">Fuzzy</th>
                    <th colspan="3">Non-Fuzzy</th>
                </tr>
                <tr>
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
                @foreach ($data as $row)
                    <tr>
                        <td>{{ $row['waktu'] }}</td>
                        <td>{{ $row['fuzzy_volume'] }}</td>
                        <td>{{ $row['fuzzy_status'] }}</td>
                        <td>{{ $row['fuzzy_rekomendasi'] }}</td>
                        <td>{{ $row['non_volume'] }}</td>
                        <td>{{ $row['non_status'] }}</td>
                        <td>{{ $row['non_rekomendasi'] }}</td>
                    </tr>
                @endforeach
            </tbody>
        </table>
    </div>
@endsection
