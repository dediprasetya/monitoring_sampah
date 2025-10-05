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
    protected $description = 'Listen to MQTT data from ESP32 tong sensor';

    public function handle()
    {
        $server   = 'hilirisasi.revolusi-it.com';
        $port     = 1883;
        $clientId = 'laravel-dashboard';
        $username = 'hilirisasi';
        $password = 'penelitianhilirisasi25';

        $connectionSettings = (new ConnectionSettings)
            ->setUsername($username)
            ->setPassword($password)
            ->setUseTls(false)
            ->setKeepAliveInterval(60)
            ->setReconnectAutomatically(true); // biar tetap reconnect

        $mqtt = new MqttClient(
            $server,
            $port,
            $clientId,
            MqttClient::MQTT_3_1
        );

        try {
            $this->info("🔌 Connecting to MQTT broker {$server}:{$port} ...");
            // 🚀 Clean session = false agar kompatibel dengan auto-reconnect
            $mqtt->connect($connectionSettings, false);
            $this->info("✅ Connected to MQTT broker {$server}");

            $mqtt->subscribe('tong/sensor', function ($topic, $message) {
                $data = json_decode($message, true);

                if (!$data) {
                    $this->warn("⚠ Payload tidak valid: {$message}");
                    return;
                }

                $bin_id = $data['id'] ?? null;
                $jarakA = $data['d1'] ?? null;
                $jarakB = $data['d2'] ?? null;

                if (is_null($bin_id) || is_null($jarakA) || is_null($jarakB)) {
                    $this->warn("⚠ Data tidak lengkap: {$message}");
                    return;
                }

                $volume = $this->fuzzyMamdani($jarakA, $jarakB);
                $status = $volume >= 26 ? 'PENUH' : ($volume >= 13 ? 'SEDANG' : 'KOSONG');
                $rekomendasi = $volume >= 30 ? 'SEGERA BERSIHKAN' : ($volume >= 20 ? 'PERLU DIPANTAU' : 'TIDAK');

                SampahLog::create([
                    'bin_id' => $bin_id,
                    'jarakA' => $jarakA,
                    'jarakB' => $jarakB,
                    'volume' => $volume,
                    'status' => $status,
                    'rekomendasi' => $rekomendasi,
                ]);

                $volume_nf = 35 - (($jarakA + $jarakB) / 2);
                $status_nf = $volume_nf >= 26 ? 'PENUH' : ($volume_nf >= 13 ? 'SEDANG' : 'KOSONG');
                $rekomendasi_nf = $volume_nf >= 30 ? 'SEGERA BERSIHKAN' : ($volume_nf >= 20 ? 'PERLU DIPANTAU' : 'TIDAK');

                SampahLogNonFuzzy::create([
                    'bin_id' => $bin_id,
                    'jarakA' => $jarakA,
                    'jarakB' => $jarakB,
                    'volume' => $volume_nf,
                    'status' => $status_nf,
                    'rekomendasi' => $rekomendasi_nf,
                ]);

                $this->info("🗑 Bin={$bin_id} | Fuzzy={$volume}({$status}) | NonFuzzy={$volume_nf}({$status_nf})");
            }, 0);

            $this->info('📡 Listening to tong/sensor ...');
            $mqtt->loop(true);
        } catch (\Exception $e) {
            $this->error('❌ MQTT connection failed: ' . $e->getMessage());
            \Log::error('MQTT Listener Error', ['error' => $e->getMessage()]);
        }
    }


    private function fuzzyMamdani($a, $b)
    {
        $tinggiA = 35 - $a;
        $tinggiB = 35 - $b;

        $trapezoid = fn($x, $a, $b, $c, $d)
            => ($x <= $a || $x >= $d) ? 0 : (($x >= $b && $x <= $c) ? 1 : (($x < $b) ? ($x - $a) / ($b - $a) : ($d - $x) / ($d - $c)));

        $triangle = fn($x, $a, $b, $c)
            => ($x <= $a || $x >= $c) ? 0 : (($x == $b) ? 1 : (($x < $b) ? ($x - $a) / ($b - $a) : ($c - $x) / ($c - $b)));

        $A_rendah = $trapezoid($tinggiA, 0, 0, 5, 10);
        $A_sedang = $triangle($tinggiA, 5, 15, 25);
        $A_tinggi = $trapezoid($tinggiA, 20, 25, 35, 35);

        $B_rendah = $trapezoid($tinggiB, 0, 0, 5, 10);
        $B_sedang = $triangle($tinggiB, 5, 15, 25);
        $B_tinggi = $trapezoid($tinggiB, 20, 25, 35, 35);

        $rules = [
            [min($A_tinggi, $B_tinggi), 30],
            [min($A_tinggi, $B_sedang), 25],
            [min($A_tinggi, $B_rendah), 20],
            [min($A_sedang, $B_tinggi), 25],
            [min($A_sedang, $B_sedang), 20],
            [min($A_sedang, $B_rendah), 15],
            [min($A_rendah, $B_tinggi), 20],
            [min($A_rendah, $B_sedang), 15],
            [min($A_rendah, $B_rendah), 5],
        ];

        $zMin = 0;
        $zMax = 35;
        $step = 0.1;
        $numerator = 0;
        $denominator = 0;

        $membership = fn($x, $a, $b, $c)
            => ($x <= $a || $x >= $c) ? 0 : (($x == $b) ? 1 : (($x < $b) ? ($x - $a) / ($b - $a) : ($c - $x) / ($c - $b)));

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
