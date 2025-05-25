<?php

namespace App\Console\Commands;

use Illuminate\Console\Command;
use PhpMqtt\Client\MqttClient;
use PhpMqtt\Client\ConnectionSettings;
use PhpMqtt\Client\Socket\TlsSocket;
use App\Models\SampahLog;

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
            $volume = $this->fuzzyMamdani($this->jarakA, $this->jarakB);

            // Status berdasarkan volume (berasal dari fuzzy)
            $status = 'KOSONG';
            if ($volume >= 26) {
                $status = 'PENUH';
            } elseif ($volume >= 13) {
                $status = 'SEDANG';
            }

            // Rekomendasi berdasarkan volume (tanpa threshold kaku)
            $rekomendasi = 'TIDAK';
            if ($volume >= 30) {
                $rekomendasi = 'SEGERA BERSIHKAN';
            } elseif ($volume >= 20) {
                $rekomendasi = 'PERLU DIPANTAU';
            }

            // Simpan ke database
            SampahLog::create([
                'jarakA' => $this->jarakA,
                'jarakB' => $this->jarakB,
                'volume' => $volume,
                'status' => $status,
                'rekomendasi' => $rekomendasi,
            ]);

            $this->info("Data disimpan: A={$this->jarakA} B={$this->jarakB} => Volume={$volume} Status={$status} Rekomendasi={$rekomendasi}");

            // Reset nilai agar tidak membaca dua kali
            $this->jarakA = null;
            $this->jarakB = null;
        }
    }


    private function fuzzyMamdani($a, $b)
    {
        // Fungsi keanggotaan segitiga untuk jarak
        $membership = function($x, $a, $b, $c) {
            if ($x <= $a || $x >= $c) return 0;
            elseif ($x == $b) return 1;
            elseif ($x < $b) return ($x - $a) / ($b - $a);
            else return ($c - $x) / ($c - $b);
        };

        // Derajat keanggotaan untuk masing-masing input
        $A_dekat  = $membership($a, 0, 0, 10);
        $A_sedang = $membership($a, 5, 15, 25);
        $A_jauh   = $membership($a, 20, 35, 35);

        $B_dekat  = $membership($b, 0, 0, 10);
        $B_sedang = $membership($b, 5, 15, 25);
        $B_jauh   = $membership($b, 20, 35, 35);

        // Aturan fuzzy (contoh 3 nilai output: 5, 15, 30)
        $rules = [
            [min($A_dekat, $B_dekat),     5],
            [min($A_dekat, $B_sedang),    10],
            [min($A_dekat, $B_jauh),      15],
            [min($A_sedang, $B_dekat),    10],
            [min($A_sedang, $B_sedang),   15],
            [min($A_sedang, $B_jauh),     20],
            [min($A_jauh, $B_dekat),      15],
            [min($A_jauh, $B_sedang),     20],
            [min($A_jauh, $B_jauh),       30],
        ];

        // Defuzzifikasi (metode rata-rata terbobot)
        $numerator = 0;
        $denominator = 0;
        foreach ($rules as [$alpha, $z]) {
            $numerator += $alpha * $z;
            $denominator += $alpha;
        }

        return $denominator == 0 ? 0 : $numerator / $denominator;
            }

}
