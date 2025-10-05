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
    protected $signature = 'mqtt:listen4';
    protected $description = 'Listen to MQTT data from HiveMQ';

    private $binId = null;
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

        // ✅ Subscribe ke semua topik sampah
        $mqtt->subscribe('sampah/#', function ($topic, $message) {
            $data = json_decode($message, true);

            if (!$data || !isset($data['jarakA'], $data['jarakB'], $data['bin_id'])) {
                $this->warn("⚠ Payload tidak valid di topic {$topic}: {$message}");
                return;
            }

            $this->binId  = $data['bin_id'];
            $this->jarakA = floatval($data['jarakA']);
            $this->jarakB = floatval($data['jarakB']);

            $this->processIfReady();
        }, 0);

        $this->info('✅ Listening to sampah/# ...');
        $mqtt->loop(true);
    }

    private function processIfReady()
    {
        if ($this->jarakA !== null && $this->jarakB !== null) {
            $startTime = microtime(true);

            // Proses fuzzy
            $volume = $this->fuzzyMamdani($this->jarakA, $this->jarakB);

            $endTime = microtime(true);
            $fuzzyExecutionTime = ($endTime - $startTime) * 1000;
            $this->info("⏱ Bin={$this->binId} Waktu eksekusi Fuzzy: {$fuzzyExecutionTime} ms");

            // Status fuzzy
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

            // ✅ Simpan fuzzy
            SampahLog::create([
                'bin_id' => $this->binId,
                'jarakA' => $this->jarakA,
                'jarakB' => $this->jarakB,
                'volume' => $volume,
                'status' => $status,
                'rekomendasi' => $rekomendasi,
            ]);

            // =====================
            // Non-fuzzy
            // =====================
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

            // ✅ Simpan non-fuzzy
            SampahLogNonFuzzy::create([
                'bin_id' => $this->binId,
                'jarakA' => $this->jarakA,
                'jarakB' => $this->jarakB,
                'volume' => $volume_nf,
                'status' => $status_nf,
                'rekomendasi' => $rekomendasi_nf,
            ]);

            $this->info("Data Bin={$this->binId} | Fuzzy Volume={$volume} Status={$status} | Non-Fuzzy Volume={$volume_nf} Status={$status_nf}");

            // Reset
            $this->jarakA = null;
            $this->jarakB = null;
        }
    }

    private function fuzzyMamdani($a, $b)
    {
        $tinggiA = 35 - $a;
        $tinggiB = 35 - $b;

        $trapezoid = function ($x, $a, $b, $c, $d) {
            if ($x <= $a || $x >= $d) return 0;
            elseif ($x >= $b && $x <= $c) return 1;
            elseif ($x > $a && $x < $b) return ($x - $a) / ($b - $a);
            else return ($d - $x) / ($d - $c);
        };

        $triangle = function ($x, $a, $b, $c) {
            if ($x <= $a || $x >= $c) return 0;
            elseif ($x == $b) return 1;
            elseif ($x < $b) return ($x - $a) / ($b - $a);
            else return ($c - $x) / ($c - $b);
        };

        $A_rendah  = $trapezoid($tinggiA, 0, 0, 5, 10);
        $A_sedang  = $triangle($tinggiA, 5, 15, 25);
        $A_tinggi  = $trapezoid($tinggiA, 20, 25, 35, 35);

        $B_rendah  = $trapezoid($tinggiB, 0, 0, 5, 10);
        $B_sedang  = $triangle($tinggiB, 5, 15, 25);
        $B_tinggi  = $trapezoid($tinggiB, 20, 25, 35, 35);

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

        $zMin = 0;
        $zMax = 35;
        $step = 0.1;
        $numerator = 0;
        $denominator = 0;

        $membership = function ($x, $a, $b, $c) {
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
}