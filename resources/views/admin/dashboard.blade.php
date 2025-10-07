@extends('layouts.app')

@section('content')
<meta http-equiv="refresh" content="5"> <!-- Auto-refresh setiap 5 detik -->

<style>
    .trash-fill {
        animation: growHeight 0.8s ease-out forwards;
    }

    @keyframes growHeight {
        from {
            height: 0%;
        }
        to {
            height: var(--target-height);
        }
    }

    @media (max-width: 768px) {
        .trash-can {
            width: 120px !important;
            height: 260px !important;
        }

        .can-body {
            height: 240px !important;
        }
    }
</style>

<h2 class="text-center mb-4">📊 Pemantauan Realtime Semua Tempat Sampah</h2>

@if($latestPerBin && count($latestPerBin) > 0)
    <div class="container">
        <div class="row justify-content-center">
            @foreach($latestPerBin as $binId => $latest)
                <div class="col-md-6 mb-4">
                    <div class="card shadow">
                        <div class="card-header text-center fw-bold">
                            Tempat Sampah: {{ strtoupper($binId) }}
                        </div>
                        <div class="card-body d-flex flex-column align-items-center">
                            <!-- Gambar Tempat Sampah -->
                            <div class="trash-can position-relative mb-3" style="width: 160px; height: 320px;">
                                <div class="lid bg-dark rounded-top" style="height: 25px; width: 100%;"></div>
                                <div class="can-body position-relative bg-light border border-dark rounded-bottom overflow-hidden" style="height: 300px; width: 100%;">
                                    <div class="trash-fill position-absolute bottom-0 start-0 w-100"
                                        style="
                                            --target-height: {{ min($latest->volume / 35 * 100, 100) }}%;
                                            background-color:
                                                @if($latest->status == 'PENUH') #e74c3c
                                                @elseif($latest->status == 'SEDANG') #f1c40f
                                                @else #2ecc71
                                                @endif;
                                        ">
                                    </div>
                                </div>
                            </div>

                            <!-- Info -->
                            <div class="info bg-white p-3 rounded text-center w-100" style="max-width: 400px;">
                                <p><strong>Jarak Sensor A:</strong> {{ number_format($latest->jarakA, 2) }} cm</p>
                                <p><strong>Jarak Sensor B:</strong> {{ number_format($latest->jarakB, 2) }} cm</p>

                                <p><strong>Tinggi Sampah:</strong> {{ number_format($latest->volume, 2) }} cm</p>

                                <!-- Tambahan: tinggi sampah dalam persen -->
                                <p><strong>Tinggi Sampah (%):</strong>
                                    {{ number_format(min($latest->volume / 35 * 100, 100), 2) }} %
                                </p>

                                <p><strong>Status:</strong>
                                    <span class="badge
                                        @if($latest->status == 'PENUH') bg-danger
                                        @elseif($latest->status == 'SEDANG') bg-warning text-dark
                                        @else bg-success
                                        @endif">{{ $latest->status }}
                                    </span>
                                </p>
                                <p><strong>Rekomendasi:</strong> {{ $latest->rekomendasi }}</p>
                            </div>
                        </div>
                    </div>
                </div>
            @endforeach
        </div>
    </div>
@else
    <p class="text-center">Belum ada data yang diterima.</p>
@endif
@endsection
