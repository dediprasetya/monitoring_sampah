@extends('layouts.app')

@section('content')
    <meta http-equiv="refresh" content="5"> <!-- Auto-refresh setiap 5 detik -->

    <h2>Pemantauan Realtime Tempat Sampah</h2>

    @if($latest)
        <div style="text-align: center; margin-top: 30px;">
            <div class="bin-lid" style="
                width: 150px;
                height: 30px;
                background-color: #444;
                margin: 0 auto;
                border-radius: 10px 10px 0 0;
                position: relative;
                top: 15px;
                z-index: 2;
            "></div>

            <div class="bin-container" style="
                position: relative;
                width: 120px;
                height: 350px;
                margin: 0 auto;
                background: #ccc;
                border: 3px solid #444;
                border-radius: 10px 10px 5px 5px;
                overflow: hidden;
            ">
                <div class="bin-fill" style="
                    position: absolute;
                    bottom: 0;
                    width: 100%;
                    height: {{ ($latest->volume) * 100 }}%;
                    background-color:
                        @if($latest->status == 'PENUH') red
                        @elseif($latest->status == 'SEDANG') yellow
                        @else green
                        @endif;
                    transition: height 0.5s ease-in-out;
                ">
                </div>

            </div>

            <div class="info" style="margin-top: 20px; font-size: 18px;">
                <p><strong>Jarak Sensor A:</strong> {{ number_format($latest->jarakA, 2) }} cm</p>
                <p><strong>Jarak Sensor B:</strong> {{ number_format($latest->jarakB, 2) }} cm</p>
                <p><span class="label">Tinggi Sampah:</span> {{ number_format($latest->volume, 2) }} cm</p>
                <p><strong>Status:</strong> {{ $latest->status }}</p>
                <p><strong>Rekomendasi Pembersihan:</strong> {{ $latest->rekomendasi }}</p>
            </div>
        </div>
    @else
        <p>Belum ada data yang diterima.</p>
    @endif
@endsection
