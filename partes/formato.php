<?php
declare(strict_types=1);

/**
 * Funciones de presentación: dan formato a fechas, horas, dinero y nombres para mostrarlos en las vistas.
 * No consultan la base de datos.
 */

function hora_legible(string $hora): string
{
    [$h, $m] = array_map('intval', explode(':', $hora));
    $h12 = $h % 12 === 0 ? 12 : $h % 12;
    return $h12 . ':' . sprintf('%02d', $m) . ' ' . ($h < 12 ? 'a. m.' : 'p. m.');
}

function fecha_corta(string $fechaIso, bool $conAnio = false): string
{
    $dias = ['Dom', 'Lun', 'Mar', 'Mié', 'Jue', 'Vie', 'Sáb'];
    $meses = ['', 'ene', 'feb', 'mar', 'abr', 'may', 'jun', 'jul', 'ago', 'sep', 'oct', 'nov', 'dic'];
    $t = strtotime($fechaIso);
    return $dias[(int) date('w', $t)] . ' ' . date('j', $t) . ' ' . $meses[(int) date('n', $t)] . ($conAnio ? ' ' . date('Y', $t) : '');
}

function edad(?string $nacimiento): ?int
{
    if (!$nacimiento) {
        return null;
    }
    return (int) (new DateTime($nacimiento))->diff(new DateTime('today'))->y;
}

function iniciales(string $nombre): string
{
    $partes = preg_split('/\s+/u', trim((string) preg_replace('/^(dr|dra)\.?\s+/iu', '', $nombre))) ?: [];
    return mb_strtoupper(mb_substr($partes[0] ?? '', 0, 1) . mb_substr($partes[1] ?? '', 0, 1));
}

function dinero($monto): string
{
    return '$' . number_format((float) $monto, 2);
}

/** Divide las características de un tratamiento del catálogo (una por línea) en una lista. */
function lineas_catalogo(?string $texto): array
{
    return array_values(array_filter(array_map('trim', preg_split('/\R/u', (string) $texto) ?: []), 'strlen'));
}
