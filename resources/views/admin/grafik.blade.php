@extends('layouts.app')

@section('content')
    <meta http-equiv="refresh" content="5"> <!-- Auto-refresh setiap 5 detik -->
    <h1 class="text-center mb-4">Grafik Tinggi Sampah</h1>

    {{-- Dropdown Filter Rentang Waktu --}}
    <form method="GET" action="{{ url()->current() }}" class="text-center mb-4">
        <label for="range">Pilih Rentang Waktu:</label>
        <select name="range" id="range" onchange="this.form.submit()" class="form-select d-inline-block w-auto ms-2">
            <option value="5min" {{ request('range') == '5min' ? 'selected' : '' }}>5 Menit</option>
            <option value="1h" {{ request('range') == '1h' ? 'selected' : '' }}>1 Jam</option>
            <option value="12h" {{ request('range') == '12h' ? 'selected' : '' }}>12 Jam</option>
            <option value="1d" {{ request('range') == '1d' ? 'selected' : '' }}>1 Hari</option>
            <option value="7d" {{ request('range') == '7d' ? 'selected' : '' }}>7 Hari</option>
        </select>
    </form>

    {{-- Chart --}}
    <div class="chart-container" style="position: relative; width: 100%; max-width: 1000px; margin: auto;">
        <canvas id="sampahChart"></canvas>
    </div>

    <script src="https://cdn.jsdelivr.net/npm/chart.js"></script>
    <script>
        const ctx = document.getElementById('sampahChart').getContext('2d');
        const chart = new Chart(ctx, {
            type: 'line',
            data: {
                labels: {!! json_encode(
                    $data->pluck('created_at')->map(fn($d) => $d->setTimezone('Asia/Jakarta')->format('d/m H:i'))->toArray()
                ) !!},
                datasets: [{
                    label: 'Tinggi Sampah (cm)',
                    data: {!! json_encode($data->pluck('volume')->toArray()) !!},
                    fill: true,
                    backgroundColor: 'rgba(54, 162, 235, 0.2)',
                    borderColor: 'rgba(54, 162, 235, 1)',
                    borderWidth: 2,
                    tension: 0.4,
                    pointRadius: 3
                }]
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
                margin-top: 10px;
            }
        }
    </style>
@endsection
