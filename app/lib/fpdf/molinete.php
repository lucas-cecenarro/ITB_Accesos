<?php
// app/lib/molinete.php
declare(strict_types=1);


class MolineteSim
{
    // Archivo de estado (queda 100% en backend, sin DB)
    private const STATE_FILE = __DIR__ . '/../tmp/molinete_state.json';

    // Duración "abierto" (segundos) y cooldown para evitar spam
    private const OPEN_SECONDS     = 10;
    private const COOLDOWN_SECONDS = 15;

    public static function abrir(int $molineteId = 1): array
    {
        $now   = time();
        $state = self::readState();
        $entry = $state[$molineteId] ?? ['busy_until' => 0, 'last_open' => 0];

        // ¿Ocupado?
        if ($now < ($entry['busy_until'] ?? 0)) {
            $secs = max(1, ($entry['busy_until'] - $now));
            return ['ok' => false, 'error' => "Molinete ocupado ($secs s)."];
        }

        // ¿En cooldown?
        $delta = $now - (int)($entry['last_open'] ?? 0);
        if ($delta < self::COOLDOWN_SECONDS) {
            $secs = self::COOLDOWN_SECONDS - $delta;
            return ['ok' => false, 'error' => "Esperá $secs s para volver a abrir."];
        }

        // Abrimos
        $entry['busy_until'] = $now + self::OPEN_SECONDS;
        $entry['last_open']  = $now;
        $state[$molineteId]  = $entry;
        self::writeState($state);

        return [
            'ok'        => true,
            'opened_at' => date('H:i:s', $now),
            'open_for'  => self::OPEN_SECONDS
        ];
    }

    private static function readState(): array
    {
        $f = self::STATE_FILE;
        if (!is_dir(dirname($f))) { @mkdir(dirname($f), 0777, true); }
        if (!file_exists($f)) return [];
        $json = @file_get_contents($f);
        $arr  = json_decode($json, true);
        return is_array($arr) ? $arr : [];
    }

    private static function writeState(array $state): void
    {
        $f = self::STATE_FILE;
        @file_put_contents($f, json_encode($state));
    }

    
}


