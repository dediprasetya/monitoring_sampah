<?php

namespace App\Console\Commands;

use Illuminate\Console\Command;
use PhpMqtt\Client\MqttClient;
use PhpMqtt\Client\ConnectionSettings;
use PhpMqtt\Client\Socket\TlsSocket;
use App\Models\SampahLog;
use App\Models\SampahLogNonFuzzy;

class MqttListener2 extends Command
{
    protected $signature = 'mqtt:listen2';
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
            $startTime = microtime(true);
            $volume = $this->fuzzyMamdani($this->jarakA, $this->jarakB);
            $endTime = microtime(true);
            $fuzzyExecutionTime = ($endTime - $startTime) * 1000;
            $this->info("⏱ Waktu eksekusi Fuzzy Mamdani: {$fuzzyExecutionTime} ms");

            // 🔶 FUZZY KEANGGOTAAN UNTUK STATUS
            $status_kosong = $this->trapezoid($volume, 0, 0, 5, 10);
            $status_sedang = $this->triangle($volume, 10, 17.5, 25);
            $status_penuh  = $this->trapezoid($volume, 20, 25, 35, 35);

            $status = 'KOSONG';
            $maxStatus = max($status_kosong, $status_sedang, $status_penuh);
            if ($maxStatus == $status_penuh) {
                $status = 'PENUH';
            } elseif ($maxStatus == $status_sedang) {
                $status = 'SEDANG';
            }

            // 🔶 FUZZY KEANGGOTAAN UNTUK REKOMENDASI
            $rekomendasi_tidak = $this->trapezoid($volume, 0, 0, 10, 15);
            $rekomendasi_pantau = $this->triangle($volume, 10, 20, 30);
            $rekomendasi_segera = $this->trapezoid($volume, 25, 30, 35, 35);

            $rekomendasi = 'TIDAK';
            $maxRek = max($rekomendasi_tidak, $rekomendasi_pantau, $rekomendasi_segera);
            if ($maxRek == $rekomendasi_segera) {
                $rekomendasi = 'SEGERA BERSIHKAN';
            } elseif ($maxRek == $rekomendasi_pantau) {
                $rekomendasi = 'PERLU DIPANTAU';
            }

            SampahLog::create([
                'jarakA' => $this->jarakA,
                'jarakB' => $this->jarakB,
                'volume' => $volume,
                'status' => $status,
                'rekomendasi' => $rekomendasi,
            ]);

            // Tetap gunakan metode threshold untuk pembanding non-fuzzy
            $volume_nf = 35 - (($this->jarakA + $this->jarakB) / 2);
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

            SampahLogNonFuzzy::create([
                'jarakA' => $this->jarakA,
                'jarakB' => $this->jarakB,
                'volume' => $volume_nf,
                'status' => $status_nf,
                'rekomendasi' => $rekomendasi_nf,
            ]);

            $this->info("Data Fuzzy: A={$this->jarakA} B={$this->jarakB} => Volume={$volume} Status={$status}");
            $this->info("Data Non-Fuzzy: Volume={$volume_nf} Status={$status_nf}");

            $this->jarakA = null;
            $this->jarakB = null;
        }
    }



   private function fuzzyMamdani($a, $b)
    {
        $tinggiA = 35 - $a;
        $tinggiB = 35 - $b;

        // Fungsi keanggotaan trapezoid
        $trapezoid = function($x, $a, $b, $c, $d) {
            if ($x <= $a || $x >= $d) return 0;
            elseif ($x >= $b && $x <= $c) return 1;
            elseif ($x > $a && $x < $b) return ($x - $a) / ($b - $a);
            else return ($d - $x) / ($d - $c);
        };

        // Fungsi keanggotaan segitiga
        $triangle = function($x, $a, $b, $c) {
            if ($x <= $a || $x >= $c) return 0;
            elseif ($x == $b) return 1;
            elseif ($x < $b) return ($x - $a) / ($b - $a);
            else return ($c - $x) / ($c - $b);
        };

        // ❗ Perubahan utama: tinggi sekarang naik dari 20 ke 25 (bukan 30!)
        $A_rendah  = $trapezoid($tinggiA, 0, 0, 5, 10);          // Rendah (trapesium)
        $A_sedang  = $triangle($tinggiA, 5, 15, 25);             // Sedang (segitiga)
        $A_tinggi  = $trapezoid($tinggiA, 20, 25, 35, 35);       // Tinggi (trapesium, revised)

        $B_rendah  = $trapezoid($tinggiB, 0, 0, 5, 10);
        $B_sedang  = $triangle($tinggiB, 5, 15, 25);
        $B_tinggi  = $trapezoid($tinggiB, 20, 25, 35, 35);

        // Aturan fuzzy
        $rules = [
            [min($A_tinggi, $B_tinggi),     30],
            [min($A_tinggi, $B_sedang),     25],
            [min($A_tinggi, $B_rendah),     20],
            [min($A_sedang, $B_tinggi),     25],
            [min($A_sedang, $B_sedang),     20],
            [min($A_sedang, $B_rendah),     15],
            [min($A_rendah, $B_tinggi),     20],
            [min($A_rendah, $B_sedang),     15],
            [min($A_rendah, $B_rendah),     5],
        ];

        // Defuzzifikasi centroid
        $zMin = 0;
        $zMax = 35;
        $step = 0.1;
        $numerator = 0;
        $denominator = 0;

        $membership = function($x, $a, $b, $c) {
            if ($x <= $a || $x >= $c) return 0;
            elseif ($x == $b) return 1;
            elseif ($x < $b) return ($x - $a) / ($b - $a);
            else return ($c - $x) / ($c - $b);
        };

        for ($z = $zMin; $z <= $zMax; $z += $step) {
            $mu = 0;
            foreach ($rules as [$alpha, $z_output]) {
                $μz = $membership($z, $z_output - 5, $z_output, $z_output + 5);
                $mu = max($mu, min($alpha, $μz));
            }
            $numerator += $z * $mu * $step;
            $denominator += $mu * $step;
        }

        return $denominator == 0 ? 0 : $numerator / $denominator;
    }

    private function triangle($x, $a, $b, $c) {
        if ($x <= $a || $x >= $c) return 0;
        elseif ($x == $b) return 1;
        elseif ($x < $b) return ($x - $a) / ($b - $a);
        else return ($c - $x) / ($c - $b);
    }

    private function trapezoid($x, $a, $b, $c, $d) {
        if ($x <= $a || $x >= $d) return 0;
        elseif ($x >= $b && $x <= $c) return 1;
        elseif ($x > $a && $x < $b) return ($x - $a) / ($b - $a);
        else return ($d - $x) / ($d - $c);
    }


}
