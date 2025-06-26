<?php

namespace App\Console\Commands;

use Illuminate\Console\Command;
use PhpMqtt\Client\MqttClient;
use PhpMqtt\Client\ConnectionSettings;
use PhpMqtt\Client\Socket\TlsSocket;
use App\Models\SampahLog;
use App\Models\SampahLogNonFuzzy;

class MqttListener extends Command
{
    protected $signature = 'mqtt:listen';
    protected $description = 'Listen to MQTT data from HiveMQ';

    private $jarakA = null;
    private $jarakB = null;

    public function handle()
    {
        $server   = 'b1d4b1389ad74d338cb784c29bc573d8.s1.eu.hivemq.cloud';
        $port     = 8883;
        $clientId = 'laravel-dashboard';
        $username = 'hivemq.webclient.1746241860208';
        $password = '3OD012CLYabNqyrc,<.>';

        $connectionSettings = (new ConnectionSettings)
            ->setUsername($username)
            ->setPassword($password)
            ->setUseTls(true);

        $mqtt = new MqttClient(
            $server,
            $port,
            $clientId,
            MqttClient::MQTT_3_1,
            null,
            null,
            TlsSocket::class
        );

        $mqtt->connect($connectionSettings, true);

        $mqtt->subscribe('sampah/jarakA', function ($topic, $message) {
            $this->jarakA = floatval($message);
            $this->processIfReady();
        }, 0);

        $mqtt->subscribe('sampah/jarakB', function ($topic, $message) {
            $this->jarakB = floatval($message);
            $this->processIfReady();
        }, 0);

        $this->info('Listening to sampah/jarakA and sampah/jarakB...');
        $mqtt->loop(true);
    }

   private function processIfReady()
    {
        if ($this->jarakA !== null && $this->jarakB !== null) {
            // Proses Fuzzy Mamdani
            $volume = $this->fuzzyMamdani($this->jarakA, $this->jarakB);

            $status = 'KOSONG';
            if ($volume >= 26) {
                $status = 'PENUH';
            } elseif ($volume >= 13) {
                $status = 'SEDANG';
            }

            $rekomendasi = 'TIDAK';
            if ($volume >= 30) {
                $rekomendasi = 'SEGERA BERSIHKAN';
            } elseif ($volume >= 20) {
                $rekomendasi = 'PERLU DIPANTAU';
            }

            // ✅ Simpan ke tabel fuzzy (sampah_logs)
            SampahLog::create([
                'jarakA' => $this->jarakA,
                'jarakB' => $this->jarakB,
                'volume' => $volume,
                'status' => $status,
                'rekomendasi' => $rekomendasi,
            ]);

            // =============================
            // Proses Non-Fuzzy (Threshold Biasa)
            // =============================
            $volume_nf = 35 - (($this->jarakA + $this->jarakB) / 2); // contoh rumus non-fuzzy
            $status_nf = 'KOSONG';
            if ($volume_nf >= 26) {
                $status_nf = 'PENUH';
            } elseif ($volume_nf >= 13) {
                $status_nf = 'SEDANG';
            }

            $rekomendasi_nf = 'TIDAK';
            if ($volume_nf >= 30) {
                $rekomendasi_nf = 'SEGERA BERSIHKAN';
            } elseif ($volume_nf >= 20) {
                $rekomendasi_nf = 'PERLU DIPANTAU';
            }

            // ✅ Simpan ke tabel non-fuzzy (sampah_logs_nonfuzzy)
            \App\Models\SampahLogNonFuzzy::create([
                'jarakA' => $this->jarakA,
                'jarakB' => $this->jarakB,
                'volume' => $volume_nf,
                'status' => $status_nf,
                'rekomendasi' => $rekomendasi_nf,
            ]);

            $this->info("Data Fuzzy: A={$this->jarakA} B={$this->jarakB} => Volume={$volume} Status={$status}");
            $this->info("Data Non-Fuzzy: Volume={$volume_nf} Status={$status_nf}");

            // Reset nilai agar tidak membaca dua kali
            $this->jarakA = null;
            $this->jarakB = null;
        }
    }



   private function fuzzyMamdani($a, $b)
    {
        // Fungsi keanggotaan segitiga
        $membership = function($x, $a, $b, $c) {
            if ($x <= $a || $x >= $c) return 0;
            elseif ($x == $b) return 1;
            elseif ($x < $b) return ($x - $a) / ($b - $a);
            else return ($c - $x) / ($c - $b);
        };

        // Derajat keanggotaan input
        $A_dekat  = $membership($a, 0, 5, 10);
        $A_sedang = $membership($a, 5, 15, 25);
        $A_jauh   = $membership($a, 20, 30, 35);

        $B_dekat  = $membership($b, 0, 5, 10);
        $B_sedang = $membership($b, 5, 15, 25);
        $B_jauh   = $membership($b, 20, 30, 35);

        // Aturan dan output z untuk tiap kombinasi
        $rules = [
            [min($A_dekat, $B_dekat),     5],   // PENUH
            [min($A_dekat, $B_sedang),    10],
            [min($A_dekat, $B_jauh),      15],
            [min($A_sedang, $B_dekat),    10],
            [min($A_sedang, $B_sedang),   15],  // SEDANG
            [min($A_sedang, $B_jauh),     20],
            [min($A_jauh, $B_dekat),      15],
            [min($A_jauh, $B_sedang),     20],
            [min($A_jauh, $B_jauh),       30],  // KOSONG
        ];

        // ==============================
        // Defuzzifikasi: Centroid Integral
        // ==============================
        $zMin = 0;
        $zMax = 35;
        $step = 0.1; // Semakin kecil = semakin presisi
        $numerator = 0;
        $denominator = 0;

        for ($z = $zMin; $z <= $zMax; $z += $step) {
            $mu = 0;

            foreach ($rules as [$alpha, $z_output]) {
                // Fungsi keanggotaan output (segitiga sempit di sekitar z_output ±5)
                $μz = $membership($z, $z_output - 5, $z_output, $z_output + 5);
                $mu = max($mu, min($alpha, $μz)); // max untuk OR antar aturan
            }

            // Trapezoidal approximation
            $numerator += $z * $mu * $step;
            $denominator += $mu * $step;
        }

        return $denominator == 0 ? 0 : $numerator / $denominator;
    }


}
