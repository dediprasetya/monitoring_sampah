@extends('layouts.app')

@section('content')
    <meta http-equiv="refresh" content="10"> <!-- Auto-refresh setiap 10 detik -->
    <h1 class="text-center mb-4">Grafik Tinggi Sampah</h1>

    {{-- Filter Bin & Rentang Waktu --}}
    <form method="GET" action="{{ url()->current() }}" class="row justify-content-center mb-4">
        <div class="col-md-3 col-12 mb-2">
            <label for="bin_id">Pilih Tempat Sampah:</label>
            <select name="bin_id" id="bin_id" onchange="this.form.submit()" class="form-select">
                <option value="">Semua Tempat Sampah</option>
                @foreach($availableBins as $bin)
                    <option value="{{ $bin }}" {{ request('bin_id') == $bin ? 'selected' : '' }}>
                        {{ strtoupper($bin) }}
                    </option>
                @endforeach
            </select>
        </div>
        <div class="col-md-3 col-12 mb-2">
            <label for="range">Rentang Waktu:</label>
            <select name="range" id="range" onchange="this.form.submit()" class="form-select">
                <option value="5min" {{ request('range') == '5min' ? 'selected' : '' }}>5 Menit</option>
                <option value="1h" {{ request('range') == '1h' ? 'selected' : '' }}>1 Jam</option>
                <option value="12h" {{ request('range') == '12h' ? 'selected' : '' }}>12 Jam</option>
                <option value="1d" {{ request('range') == '1d' ? 'selected' : '' }}>1 Hari</option>
                <option value="7d" {{ request('range') == '7d' ? 'selected' : '' }}>7 Hari</option>
            </select>
        </div>
    </form>

    {{-- Chart --}}
    <div class="chart-container" style="position: relative; width: 100%; max-width: 1000px; margin: auto;">
        <canvas id="sampahChart"></canvas>
    </div>

    <script src="https://cdn.jsdelivr.net/npm/chart.js"></script>
    <script>
        const ctx = document.getElementById('sampahChart').getContext('2d');

        const datasets = [
            @foreach($chartData as $binId => $logs)
            {
                label: "Bin {{ strtoupper($binId) }}",
                data: {!! json_encode($logs->pluck('volume')->toArray()) !!},
                fill: true,
                backgroundColor: 'rgba({{ rand(50,200) }}, {{ rand(50,200) }}, {{ rand(50,200) }}, 0.2)',
                borderColor: 'rgba({{ rand(50,200) }}, {{ rand(50,200) }}, {{ rand(50,200) }}, 1)',
                borderWidth: 2,
                tension: 0.4,
                pointRadius: 3
            },
            @endforeach
        ];

        const chart = new Chart(ctx, {
            type: 'line',
            data: {
                labels: {!! json_encode($timeLabels) !!},
                datasets: datasets
            },
            options: {
                responsive: true,
                maintainAspectRatio: false,
                aspectRatio: 2,
                plugins: {
                    legend: {
                        display: true,
                        position: 'top'
                    }
                },
                scales: {
                    y: {
                        beginAtZero: true,
                        suggestedMax: 35,
                        title: {
                            display: true,
                            text: 'Tinggi Sampah (cm)'
                        }
                    },
                    x: {
                        title: {
                            display: true,
                            text: 'Waktu (GMT+7)'
                        }
                    }
                }
            }
        });
    </script>

    <style>
        .chart-container {
            height: 300px;
        }

        @media (max-width: 768px) {
            .chart-container {
                height: 250px;
                padding: 10px;
            }

            form select {
                width: 100%;
                margin-top: 5px;
            }
        }
    </style>
@endsection
