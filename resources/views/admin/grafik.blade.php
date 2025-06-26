@extends('layouts.app')

@section('content')
    <h1 style="text-align: center;">Grafik Tinggi Sampah</h1>

    <div style="width: 100%; max-width: 800px; margin: 30px auto;">
        <canvas id="sampahChart" height="100"></canvas>
    </div>

    <script src="https://cdn.jsdelivr.net/npm/chart.js"></script>
    <script>
        const ctx = document.getElementById('sampahChart').getContext('2d');
        const chart = new Chart(ctx, {
            type: 'line',
            data: {
                labels: {!! json_encode($data->pluck('created_at')->map(fn($d) => $d->format('H:i:s'))->toArray()) !!},
                datasets: [{
                    label: 'Tinggi Sampah (cm)',
                    data: {!! json_encode($data->pluck('volume')->toArray()) !!},
                    fill: true,
                    backgroundColor: 'rgba(54, 162, 235, 0.2)',
                    borderColor: 'rgba(54, 162, 235, 1)',
                    borderWidth: 2,
                    tension: 0.3
                }]
            },
            options: {
                responsive: true,
                plugins: {
                    legend: {
                        display: true,
                        position: 'top'
                    },
                    title: {
                        display: false
                    }
                },
                scales: {
                    y: {
                        beginAtZero: true,
                        max: 40
                    }
                }
            }
        });
    </script>
@endsection
