<?php

declare(strict_types=1);

require_once __DIR__ . '/../db.php';
require_once __DIR__ . '/../auth.php';

class ReportesController
{
    private PDO $db;
    private int $pageSize = 100;

    public function __construct(PDO $pdo)
    {
        $this->db = $pdo;
        @date_default_timezone_set('America/Argentina/Buenos_Aires');
        $this->db->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);
    }

    public function assertSuperusuario(): void
    {
        if (session_status() === PHP_SESSION_NONE) session_start();
        // 1 = superusuario (ajusta si tu ID es otro)
        if (!isset($_SESSION['rol_id']) || (int)$_SESSION['rol_id'] !== 1) {
            http_response_code(403);
            die('Acceso restringido a Superusuario.');
        }
    }

    /** Filtros desde GET/POST; por defecto últimos 7 días */
    public function filtros(array $src): array
    {
        $hasta = new DateTime('now');
        $desde = (clone $hasta)->modify('-7 days');

        return [
            'desde'    => $this->parseDT($src['desde'] ?? $desde->format('Y-m-d\TH:i')),
            'hasta'    => $this->parseDT($src['hasta'] ?? $hasta->format('Y-m-d\TH:i')),
            'tipo'     => strtoupper(trim($src['tipo'] ?? 'AMBOS')), // AMBOS|INGRESO|EGRESO
            'dni'      => trim($src['dni'] ?? ''),
            'nombre'   => trim($src['nombre'] ?? ''),
            'apellido' => trim($src['apellido'] ?? ''),
            'rol_id'   => (int)($src['rol_id'] ?? 0),
            'page'     => max(1, (int)($src['page'] ?? 1)),
        ];
    }

    private function parseDT(string $val): string
    {
        $val = str_replace('T', ' ', $val);
        $dt  = DateTime::createFromFormat('Y-m-d H:i', $val) ?: DateTime::createFromFormat('Y-m-d H:i:s', $val);
        if (!$dt) $dt = new DateTime($val);
        return $dt->format('Y-m-d H:i:s');
    }

    public function validar(array $f): array
    {
        $err = [];
        if ($f['desde'] > $f['hasta']) $err[] = 'El rango de fechas es inválido (desde > hasta).';

        $d1 = new DateTime($f['desde']);
        $d2 = new DateTime($f['hasta']);
        $dias = (int)$d1->diff($d2)->format('%a');
        if ($dias > 31) $err[] = 'El rango supera 31 días. Particioná por semanas/meses.';

        if (!in_array($f['tipo'], ['AMBOS', 'INGRESO', 'EGRESO'], true)) $err[] = 'Tipo desconocido.';
        return $err;
    }

    /** Catálogo de roles para combos */
    public function roles(): array
    {
        $st = $this->db->query("SELECT TipoUsuario_ID AS rol_id, Nombre AS nombre FROM TIPO_USUARIO ORDER BY Nombre");
        return $st->fetchAll(PDO::FETCH_ASSOC);
    }

    /** Solo filtros de usuario (sin fecha/tipo) para reusar en las consultas */
    private function userFilters(array $f): array
    {
        $where = [];
        $p = [];

        if (!empty($f['dni'])) {
            $where[] = "u.Num_Documento = :dni";
            $p[':dni'] = $f['dni'];
        }
        if (!empty($f['nombre'])) {
            $where[] = "u.Nombre LIKE CONCAT('%', :nombre, '%')";
            $p[':nombre'] = $f['nombre'];
        }
        if (!empty($f['apellido'])) {
            $where[] = "u.Apellido LIKE CONCAT('%', :apellido, '%')";
            $p[':apellido'] = $f['apellido'];
        }
        // Categoría -> TIPOS_USUARIO
        if (!empty($f['rol_id'])) { // seguimos usando la misma key que tu UI envía
            $where[] = "u.TipoUsuario_ID = :rol_id";
            $p[':rol_id'] = (int)$f['rol_id'];
        }

        $w = count($where) ? (' AND ' . implode(' AND ', $where)) : '';
        return [$w, $p];
    }

    /** Igual que userFilters pero con sufijo para evitar placeholders duplicados en UNION */
    private function userFiltersWithSuffix(array $f, string $suf): array
    {
        $where = [];
        $p = [];

        if (!empty($f['dni'])) {
            $where[] = "u.Num_Documento = :dni{$suf}";
            $p[":dni{$suf}"] = $f['dni'];
        }
        if (!empty($f['nombre'])) {
            $where[] = "u.Nombre LIKE CONCAT('%', :nombre{$suf}, '%')";
            $p[":nombre{$suf}"] = $f['nombre'];
        }
        if (!empty($f['apellido'])) {
            $where[] = "u.Apellido LIKE CONCAT('%', :apellido{$suf}, '%')";
            $p[":apellido{$suf}"] = $f['apellido'];
        }
        if (!empty($f['rol_id'])) { // Categoría -> tipos_usuario
            $where[] = "u.TipoUsuario_ID = :rol_id{$suf}";
            $p[":rol_id{$suf}"] = (int)$f['rol_id'];
        }

        $w = count($where) ? (' AND ' . implode(' AND ', $where)) : '';
        return [$w, $p];
    }


    /** Conteo de filas según filtros */
    public function contar(array $f): int
    {
        // INGRESO solo
        if ($f['tipo'] === 'INGRESO') {
            [$w1, $p1] = $this->userFiltersWithSuffix($f, '1');
            $sql = "SELECT COUNT(*)
                FROM ACCESOS a
                JOIN USUARIOS u ON u.Usuario_ID = a.Usuario_ID
                WHERE a.FechaHora_Entrada BETWEEN :desde AND :hasta {$w1}";
            $st = $this->db->prepare($sql);
            $st->bindValue(':desde', $f['desde']);
            $st->bindValue(':hasta', $f['hasta']);
            foreach ($p1 as $k => $v) $st->bindValue($k, $v);
            $st->execute();
            return (int)$st->fetchColumn();
        }

        // EGRESO solo
        if ($f['tipo'] === 'EGRESO') {
            [$w1, $p1] = $this->userFiltersWithSuffix($f, '1');
            $sql = "SELECT COUNT(*)
                FROM ACCESOS a
                JOIN USUARIOS u ON u.Usuario_ID = a.Usuario_ID
                WHERE a.FechaHora_Salida IS NOT NULL
                  AND a.FechaHora_Salida BETWEEN :desde AND :hasta {$w1}";
            $st = $this->db->prepare($sql);
            $st->bindValue(':desde', $f['desde']);
            $st->bindValue(':hasta', $f['hasta']);
            foreach ($p1 as $k => $v) $st->bindValue($k, $v);
            $st->execute();
            return (int)$st->fetchColumn();
        }

        // AMBOS: usar placeholders con sufijos distintos en cada subquery
        [$w1, $p1] = $this->userFiltersWithSuffix($f, '1');
        [$w2, $p2] = $this->userFiltersWithSuffix($f, '2');

        $sql = "SELECT SUM(cnt) FROM (
              SELECT COUNT(*) AS cnt
              FROM ACCESOS a
              JOIN USUARIOS u ON u.Usuario_ID = a.Usuario_ID
              WHERE a.FechaHora_Entrada BETWEEN :d1 AND :h1 {$w1}
              UNION ALL
              SELECT COUNT(*) AS cnt
              FROM ACCESOS a
              JOIN USUARIOS u ON u.Usuario_ID = a.Usuario_ID
              WHERE a.FechaHora_Salida IS NOT NULL
                AND a.FechaHora_Salida BETWEEN :d2 AND :h2 {$w2}
            ) t";

        $st = $this->db->prepare($sql);
        $st->bindValue(':d1', $f['desde']);
        $st->bindValue(':h1', $f['hasta']);
        $st->bindValue(':d2', $f['desde']);
        $st->bindValue(':h2', $f['hasta']);
        foreach ($p1 as $k => $v) $st->bindValue($k, $v);
        foreach ($p2 as $k => $v) $st->bindValue($k, $v);
        $st->execute();

        return (int)$st->fetchColumn();
    }

    /** Resumen de totales (ingresos/egresos) */
    public function resumen(array $f): array
    {
        [$wUser, $pUser] = $this->userFilters($f);

        // Ingresos
        $sqlIn = "SELECT COUNT(*) FROM ACCESOS a
                  JOIN USUARIOS u ON u.Usuario_ID = a.Usuario_ID
                  WHERE a.FechaHora_Entrada BETWEEN :desde AND :hasta {$wUser}";
        $stIn = $this->db->prepare($sqlIn);
        $stIn->execute(array_merge([':desde' => $f['desde'], ':hasta' => $f['hasta']], $pUser));
        $ing = (int)$stIn->fetchColumn();

        // Egresos
        $sqlEg = "SELECT COUNT(*) FROM ACCESOS a
                  JOIN USUARIOS u ON u.Usuario_ID = a.Usuario_ID
                  WHERE a.FechaHora_Salida IS NOT NULL
                    AND a.FechaHora_Salida BETWEEN :desde AND :hasta {$wUser}";
        $stEg = $this->db->prepare($sqlEg);
        $stEg->execute(array_merge([':desde' => $f['desde'], ':hasta' => $f['hasta']], $pUser));
        $egr = (int)$stEg->fetchColumn();

        if ($f['tipo'] === 'INGRESO') $egr = 0;
        if ($f['tipo'] === 'EGRESO') $ing = 0;

        return ['total' => $ing + $egr, 'ingresos' => $ing, 'egresos' => $egr];
    }

    /** Listado paginado (normalizado a columnas fecha/hora/tipo) */
    public function listar(array $f): array
    {
        $limit  = $this->pageSize;
        $offset = ($f['page'] - 1) * $limit;

        // Subquery INGRESOS (sufijo 1)
        [$w1, $p1] = $this->userFiltersWithSuffix($f, '1');
        $subIng = "
      SELECT
        a.Acceso_ID                           AS acceso_id,
        u.Num_Documento                       AS dni,
        u.Nombre                              AS nombre,
        u.Apellido                            AS apellido,
        COALESCE(tu.Nombre,'Invitado')                  AS categoria,
        'INGRESO'                             AS tipo,
        DATE(a.FechaHora_Entrada)             AS fecha,
        TIME(a.FechaHora_Entrada)             AS hora,
        e.Nombre                              AS estado
      FROM ACCESOS a
      JOIN USUARIOS u        ON u.Usuario_ID = a.Usuario_ID
      LEFT JOIN TIPO_USUARIO tu      ON tu.TipoUsuario_ID     = u.TipoUsuario_ID
      LEFT JOIN estado_acceso e ON e.Estado_ID = a.Estado_ID
      WHERE a.FechaHora_Entrada BETWEEN :d1 AND :h1 {$w1}
    ";

        // Subquery EGRESOS (sufijo 2)
        [$w2, $p2] = $this->userFiltersWithSuffix($f, '2');
        $subEg = "
      SELECT
        a.Acceso_ID                           AS acceso_id,
        u.Num_Documento                       AS dni,
        u.Nombre                              AS nombre,
        u.Apellido                            AS apellido,
        COALESCE(tu.Nombre,'Invitado')                  AS categoria,
        'EGRESO'                              AS tipo,
        DATE(a.FechaHora_Salida)              AS fecha,
        TIME(a.FechaHora_Salida)              AS hora,
        e.Nombre                              AS estado
      FROM ACCESOS a
      JOIN USUARIOS u        ON u.Usuario_ID = a.Usuario_ID
      LEFT JOIN TIPO_USUARIO tu      ON tu.TipoUsuario_ID     = u.TipoUsuario_ID
      LEFT JOIN estado_acceso e ON e.Estado_ID = a.Estado_ID
      WHERE a.FechaHora_Salida IS NOT NULL
        AND a.FechaHora_Salida BETWEEN :d2 AND :h2 {$w2}
    ";

        if ($f['tipo'] === 'INGRESO') {
            $sql = "$subIng ORDER BY fecha DESC, hora DESC LIMIT :limit OFFSET :offset";
            $st  = $this->db->prepare($sql);
            // binds
            foreach ($p1 as $k => $v) $st->bindValue($k, $v);
            $st->bindValue(':d1', $f['desde']);
            $st->bindValue(':h1', $f['hasta']);
        } elseif ($f['tipo'] === 'EGRESO') {
            $sql = "$subEg ORDER BY fecha DESC, hora DESC LIMIT :limit OFFSET :offset";
            $st  = $this->db->prepare($sql);
            foreach ($p2 as $k => $v) $st->bindValue($k, $v);
            $st->bindValue(':d2', $f['desde']);
            $st->bindValue(':h2', $f['hasta']);
        } else { // AMBOS
            $sql = "($subIng) UNION ALL ($subEg) ORDER BY fecha DESC, hora DESC LIMIT :limit OFFSET :offset";
            $st  = $this->db->prepare($sql);
            foreach ($p1 as $k => $v) $st->bindValue($k, $v);
            foreach ($p2 as $k => $v) $st->bindValue($k, $v);
            $st->bindValue(':d1', $f['desde']);
            $st->bindValue(':h1', $f['hasta']);
            $st->bindValue(':d2', $f['desde']);
            $st->bindValue(':h2', $f['hasta']);
        }

        $st->bindValue(':limit',  $limit,  PDO::PARAM_INT);
        $st->bindValue(':offset', $offset, PDO::PARAM_INT);
        $st->execute();
        return $st->fetchAll(PDO::FETCH_ASSOC);
    }


    /** Cursor para exportes (columnas compatibles con tu UI) */
    public function exportCursor(array $f): PDOStatement
    {
        [$w1, $p1] = $this->userFiltersWithSuffix($f, '1');
        $subIng = "
      SELECT
        DATE(a.FechaHora_Entrada)             AS fecha,
        TIME(a.FechaHora_Entrada)             AS hora,
        'INGRESO'                              AS tipo,
        u.Num_Documento                       AS dni,
        u.Nombre                              AS nombre,
        u.Apellido                            AS apellido,
        COALESCE(tu.Nombre,'Invitado')                  AS categoria,
        e.Nombre                              AS estado
      FROM ACCESOS a
      JOIN USUARIOS u        ON u.Usuario_ID = a.Usuario_ID
      LEFT JOIN TIPO_USUARIO tu      ON tu.TipoUsuario_ID     = u.TipoUsuario_ID
      LEFT JOIN estado_acceso e ON e.Estado_ID = a.Estado_ID
      WHERE a.FechaHora_Entrada BETWEEN :d1 AND :h1 {$w1}
    ";

        [$w2, $p2] = $this->userFiltersWithSuffix($f, '2');
        $subEg = "
      SELECT
        DATE(a.FechaHora_Salida)              AS fecha,
        TIME(a.FechaHora_Salida)              AS hora,
        'EGRESO'                               AS tipo,
        u.Num_Documento                       AS dni,
        u.Nombre                              AS nombre,
        u.Apellido                            AS apellido,
        COALESCE(tu.Nombre,'Invitado')                   AS categoria,
        e.Nombre                              AS estado
      FROM ACCESOS a
      JOIN USUARIOS u        ON u.Usuario_ID = a.Usuario_ID
      LEFT JOIN TIPO_USUARIO tu      ON tu.TipoUsuario_ID     = u.TipoUsuario_ID
      LEFT JOIN estado_acceso e ON e.Estado_ID = a.Estado_ID
      WHERE a.FechaHora_Salida IS NOT NULL
        AND a.FechaHora_Salida BETWEEN :d2 AND :h2 {$w2}
    ";

        if ($f['tipo'] === 'INGRESO') {
            $sql = "$subIng ORDER BY fecha DESC, hora DESC";
            $st  = $this->db->prepare($sql);
            foreach ($p1 as $k => $v) $st->bindValue($k, $v);
            $st->bindValue(':d1', $f['desde']);
            $st->bindValue(':h1', $f['hasta']);
        } elseif ($f['tipo'] === 'EGRESO') {
            $sql = "$subEg ORDER BY fecha DESC, hora DESC";
            $st  = $this->db->prepare($sql);
            foreach ($p2 as $k => $v) $st->bindValue($k, $v);
            $st->bindValue(':d2', $f['desde']);
            $st->bindValue(':h2', $f['hasta']);
        } else {
            $sql = "($subIng) UNION ALL ($subEg) ORDER BY fecha DESC, hora DESC";
            $st  = $this->db->prepare($sql);
            foreach ($p1 as $k => $v) $st->bindValue($k, $v);
            foreach ($p2 as $k => $v) $st->bindValue($k, $v);
            $st->bindValue(':d1', $f['desde']);
            $st->bindValue(':h1', $f['hasta']);
            $st->bindValue(':d2', $f['desde']);
            $st->bindValue(':h2', $f['hasta']);
        }

        $st->execute();
        return $st;
    }


    /**
     * Registra un reporte en la tabla REPORTES (ajustado al esquema real).
     * Mantengo la firma para no romper llamadas, pero la inserción usa solo:
     *   Usuario_ID (de sesión), FechaIni, FechaFin, TipoReporte, Formato
     */
    public function logReporte(array $f, ?string $formato, int $filas, string $estado = 'ok', ?string $error = null): void
    {
        if (session_status() === PHP_SESSION_NONE) session_start();
        $usuarioId = (int)($_SESSION['user_id'] ?? 0); // quien generó el reporte

        // TipoReporte puede derivar del filtro 'tipo', por ejemplo:
        $tipoReporte = 'GENERAL';
        if (isset($f['tipo']) && in_array($f['tipo'], ['INGRESO', 'EGRESO'], true)) {
            $tipoReporte = 'POR_' . $f['tipo']; // POR_INGRESO | POR_EGRESO
        }

        $sql = "INSERT INTO REPORTES (Usuario_ID, FechaIni, FechaFin, TipoReporte, Formato)
                VALUES (:uid, :ini, :fin, :tipo, :fmt)";
        $st = $this->db->prepare($sql);
        $st->bindValue(':uid',  $usuarioId, PDO::PARAM_INT);
        $st->bindValue(':ini',  substr($f['desde'], 0, 10)); // DATE desde string
        $st->bindValue(':fin',  substr($f['hasta'], 0, 10)); // DATE hasta string
        $st->bindValue(':tipo', $tipoReporte);
        $st->bindValue(':fmt',  $formato ?? 'PDF');
        $st->execute();
    }
}
