<?php

declare(strict_types=1);
require_once __DIR__ . '/../db.php'; // Para DB::conn()

class UsuarioBusqueda
{
    private PDO $db;

    public function __construct(?PDO $pdo = null)
    {
        $this->db = $pdo ?: DB::conn();
        $this->db->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);
        @date_default_timezone_set('America/Argentina/Buenos_Aires');
    }

    /** Normaliza fechas de filtros: acepta yyyy-mm-dd, dd/mm/yyyy, yyyy-mm-ddTHH:ii, texto suelto */
    public static function toYmdFlex(?string $s): ?string
    {
        if (!$s) return null;
        $s = trim($s);
        if (preg_match('/^\d{4}-\d{2}-\d{2}$/', $s)) return $s;
        $dt = DateTime::createFromFormat('d/m/Y', $s);
        if ($dt) return $dt->format('Y-m-d');
        $dt = DateTime::createFromFormat('Y-m-d\TH:i', $s);
        if ($dt) return $dt->format('Y-m-d');
        $t = strtotime($s);
        return $t ? date('Y-m-d', $t) : null;
    }

    /** Lee filtros desde GET/POST (mantengo las mismas claves que tu UI) */
    public static function leerFiltros(array $src): array
    {
        return [
            'nombre'     => trim($src['nombre']   ?? ''),
            'apellido'   => trim($src['apellido'] ?? ''),
            'dni'        => trim($src['dni']      ?? ''),
            'desde_raw'  => trim($src['desde']    ?? ''),   // por si mostrás el valor original
            'hasta_raw'  => trim($src['hasta']    ?? ''),
            'desde'      => self::toYmdFlex($src['desde'] ?? ''),
            'hasta'      => self::toYmdFlex($src['hasta'] ?? ''),
            'operadorId' => (isset($src['operador']) && ctype_digit((string)$src['operador']))
                ? (int)$src['operador'] : null,            // Nota: hoy NO se aplica en la búsqueda
        ];
    }

    /** Devuelve usuarios con rol SEGURIDAD (para poblar un combo si querés mostrarlo) */
    public function operadoresActivos(): array
    {
        // Ya no existe tabla 'operadores'; usamos USUARIOS con Rol_ID = 2 (ajustá si tu ID cambia)
        $sql = "SELECT Usuario_ID AS operador_id, Nombre AS nombre, Apellido AS apellido
                FROM USUARIOS
                WHERE Rol_ID = 2
                ORDER BY Nombre, Apellido";
        return $this->db->query($sql)->fetchAll(PDO::FETCH_ASSOC);
    }

    /** WHERE solo de filtros de usuario (nombre/apellido/dni/rol si algún día quisieras) */
    private function whereUsuario(array $f): array
    {
        $w = [];
        $p = [];

        if ($f['nombre']   !== '') {
            $w[] = "u.Nombre LIKE :nombre";
            $p[':nombre'] = "%{$f['nombre']}%";
        }
        if ($f['apellido'] !== '') {
            $w[] = "u.Apellido LIKE :apellido";
            $p[':apellido'] = "%{$f['apellido']}%";
        }
        if ($f['dni']      !== '') {
            $w[] = "u.Num_Documento LIKE :dni";
            $p[':dni'] = "%{$f['dni']}%";
        }

        $sql = $w ? ' AND ' . implode(' AND ', $w) : '';
        return [$sql, $p];
    }

    /** Igual que whereUsuario pero agrega un sufijo a los placeholders para usar en UNION */
    private function whereUsuarioSufijo(array $f, string $suf): array
    {
        $w = [];
        $p = [];

        if ($f['nombre']   !== '') {
            $w[] = "u.Nombre LIKE :nombre{$suf}";
            $p[":nombre{$suf}"] = "%{$f['nombre']}%";
        }
        if ($f['apellido'] !== '') {
            $w[] = "u.Apellido LIKE :apellido{$suf}";
            $p[":apellido{$suf}"] = "%{$f['apellido']}%";
        }
        if ($f['dni']      !== '') {
            $w[] = "u.Num_Documento LIKE :dni{$suf}";
            $p[":dni{$suf}"] = "%{$f['dni']}%";
        }

        $sql = $w ? ' AND ' . implode(' AND ', $w) : '';
        return [$sql, $p];
    }


    /** Calcular rango datetime a partir de yyyy-mm-dd o por defecto últimos 7 días */
    private function rangoFechas(array $f): array
    {
        if ($f['desde'] && $f['hasta']) {
            return [$f['desde'] . ' 00:00:00', $f['hasta'] . ' 23:59:59'];
        }
        $hasta = new DateTime('now');
        $desde = (clone $hasta)->modify('-7 days');
        return [$desde->format('Y-m-d 00:00:00'), $hasta->format('Y-m-d 23:59:59')];
    }

    /** Ejecuta la búsqueda y devuelve filas normalizadas (tipo/fecha/hora) */
    public function buscarAccesos(array $f): array
    {
        [$desdeDT, $hastaDT] = $this->rangoFechas($f);

        // Filtros con sufijo para cada subconsulta del UNION
        [$w1, $p1] = $this->whereUsuarioSufijo($f, '1'); // ingresos
        [$w2, $p2] = $this->whereUsuarioSufijo($f, '2'); // egresos

        $subIng = "
      SELECT
        u.Nombre        AS nombre,
        u.Apellido      AS apellido,
        u.Num_Documento AS nro_documento,
        'INGRESO'       AS tipo,
        DATE(a.FechaHora_Entrada) AS fecha,
        TIME(a.FechaHora_Entrada) AS hora,
        NULL            AS operador
      FROM ACCESOS a
      JOIN USUARIOS u ON u.Usuario_ID = a.Usuario_ID
      WHERE a.FechaHora_Entrada BETWEEN :d1 AND :h1
      {$w1}
    ";

        $subEg = "
      SELECT
        u.Nombre        AS nombre,
        u.Apellido      AS apellido,
        u.Num_Documento AS nro_documento,
        'EGRESO'        AS tipo,
        DATE(a.FechaHora_Salida) AS fecha,
        TIME(a.FechaHora_Salida) AS hora,
        NULL            AS operador
      FROM ACCESOS a
      JOIN USUARIOS u ON u.Usuario_ID = a.Usuario_ID
      WHERE a.FechaHora_Salida IS NOT NULL
        AND a.FechaHora_Salida BETWEEN :d2 AND :h2
      {$w2}
    ";

        $sql = "($subIng) UNION ALL ($subEg) ORDER BY fecha DESC, hora DESC";
        $st  = $this->db->prepare($sql);

        // Rangos
        $st->bindValue(':d1', $desdeDT);
        $st->bindValue(':h1', $hastaDT);
        $st->bindValue(':d2', $desdeDT);
        $st->bindValue(':h2', $hastaDT);

        // Filtros de usuario en cada subconsulta (placeholders distintos)
        foreach ($p1 as $k => $v) $st->bindValue($k, $v);
        foreach ($p2 as $k => $v) $st->bindValue($k, $v);

        $st->execute();
        return $st->fetchAll(PDO::FETCH_ASSOC);
    }
}
