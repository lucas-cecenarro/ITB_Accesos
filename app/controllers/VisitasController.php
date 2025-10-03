<?php

declare(strict_types=1);

require_once __DIR__ . '/../db.php';

class VisitasController
{

    private PDO $db;

    // Ajustá si en tu tabla TIPOS_USUARIO el id de "Invitado" no es 6
    private const TIPO_USUARIO_INVITADO = 6;

    // Estados de ACCESOS según tu tabla estado_acceso
    private const ESTADO_EN_CURSO   = 1;
    private const ESTADO_COMPLETADO = 2;
    private const TIPO_USUARIO_VISITA_ID = 6; // "Invitado"
    private const ESTADO_EN_CURSO_ID     = 1; // EN_CURSO
    private const ESTADO_COMPLETADO_ID   = 2; // COMPLETADO

    public function __construct(?PDO $pdo = null)
    {
        $this->db = $pdo ?: DB::conn();
        $this->db->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);
        @date_default_timezone_set('America/Argentina/Buenos_Aires');
    }

    private function operadorActualId(): ?int
    {
        if (session_status() === PHP_SESSION_NONE) session_start();
        $rid = (int)($_SESSION['rol_id'] ?? 0);
        if ($rid === 1 || $rid === 2) {
            return (int)($_SESSION['user_id'] ?? 0) ?: null;
        }
        return null;
    }

    /** Para el combo */
    public function tiposDocumento(): array
    {
        $sql = "SELECT TipoDoc_ID AS tipodoc_id, Nombre AS nombre
                FROM TIPO_DOCUMENTO
                ORDER BY Nombre";
        return $this->db->query($sql)->fetchAll(PDO::FETCH_ASSOC);
    }

    /** Busca o crea el usuario visitante por DNI+TipoDoc */
    private function asegurarUsuario(string $nombre, string $apellido, string $dni, int $tipoDocId): int
    {
        $st = $this->db->prepare(
            "SELECT Usuario_ID FROM USUARIOS
             WHERE Num_Documento = ? AND TipoDoc_ID = ?
             LIMIT 1"
        );
        $st->execute([$dni, $tipoDocId]);
        $row = $st->fetch(PDO::FETCH_ASSOC);
        if ($row) return (int)$row['Usuario_ID'];

        // Crear visitante (Rol_ID NULL, TipoUsuario_ID = Invitado)
        $ins = $this->db->prepare(
            "INSERT INTO USUARIOS
     (Nombre, Apellido, Correo, `Password`, Num_Documento, TipoDoc_ID,
      Direccion_ID, Rol_ID, TipoUsuario_ID, Fecha_Registro)
     VALUES (?,?,?,?,?,?,NULL,NULL,?,?)"
        );
        $ins->execute([
            $nombre,
            $apellido,
            null,
            null,
            $dni,
            $tipoDocId,
            self::TIPO_USUARIO_INVITADO,
            date('Y-m-d')
        ]);
        return (int)$this->db->lastInsertId();
    }

    /** Devuelve el último acceso abierto (sin salida) o null */
    private function ultimoAccesoAbierto(int $usuarioId): ?array
    {
        $st = $this->db->prepare(
            "SELECT Acceso_ID, FechaHora_Entrada
               FROM ACCESOS
              WHERE Usuario_ID = ? AND FechaHora_Salida IS NULL
              ORDER BY FechaHora_Entrada DESC
              LIMIT 1"
        );
        $st->execute([$usuarioId]);
        $r = $st->fetch(PDO::FETCH_ASSOC);
        return $r ?: null;
    }

    /** Inserta una alerta. Prueba FechaHora; si falla, usa Fecha/Hora */
    private function registrarAlerta(?int $usuarioId, string $motivo): void
    {
        try {
            $st = $this->db->prepare(
                "INSERT INTO ALERTAS (Usuario_ID, FechaHora, Motivo)
                 VALUES (?, NOW(), ?)"
            );
            $st->execute([$usuarioId, $motivo]);
        } catch (Throwable $e) {
            // Fallback si tu tabla tiene columnas separadas Fecha / Hora
            $st2 = $this->db->prepare(
                "INSERT INTO ALERTAS (Usuario_ID, Fecha, Hora, Motivo)
                 VALUES (?, CURDATE(), CURTIME(), ?)"
            );
            $st2->execute([$usuarioId, $motivo]);
        }
    }

    /** Registra ingreso/egreso y devuelve [mensaje, warning, error] */
    // --- helpers de bloqueo por usuario (pegarlos dentro de la clase) ---
    private function lockUser(int $usuarioId, int $timeout = 5): void
    {
        $st = $this->db->prepare('SELECT GET_LOCK(?, ?)');
        $st->execute(['acceso_user_' . $usuarioId, $timeout]);
        $ok = (int)$st->fetchColumn();
        if ($ok !== 1) {
            throw new RuntimeException('Otra operación está procesando este usuario. Intentá nuevamente.');
        }
    }

    private function unlockUser(int $usuarioId): void
    {
        $st = $this->db->prepare('SELECT RELEASE_LOCK(?)');
        $st->execute(['acceso_user_' . $usuarioId]);
    }

    // --- método principal ---
    public function registrar(array $post): array
    {
        $mensaje = $warning = $error = null;

        $nombre    = trim($post['nombre']     ?? '');
        $apellido  = trim($post['apellido']   ?? '');
        $dni       = trim($post['dni']        ?? '');
        $tipoDocId = (int)($post['tipodoc_id'] ?? 1);
        $motivo    = trim($post['motivo']     ?? '');
        if (mb_strlen($motivo) > 200) $motivo = mb_substr($motivo, 0, 200);

        $tipo = strtoupper(trim($post['tipo'] ?? 'INGRESO')); // INGRESO | EGRESO

        if ($nombre === '' || $apellido === '' || $dni === '') {
            return [null, null, 'Complete nombre, apellido y DNI.'];
        }
        if (!in_array($tipo, ['INGRESO', 'EGRESO'], true)) {
            return [null, null, 'Tipo inválido.'];
        }

        // operador actual (seguridad / superusuario)
        if (session_status() === PHP_SESSION_NONE) session_start();
        $opId = (int)($_SESSION['user_id'] ?? 0) ?: null;

        try {
            $this->db->beginTransaction();

            $usuarioId = $this->asegurarUsuario($nombre, $apellido, $dni, $tipoDocId);
            $abierto   = $this->ultimoAccesoAbierto($usuarioId); // null o fila con Acceso_ID

            if ($tipo === 'INGRESO') {
                if ($abierto) {
                    // Ingreso consecutivo sin egreso
                    $this->registrarAlerta($usuarioId, 'Ingreso consecutivo sin egreso');
                    $this->db->commit();
                    return [null, 'Se registró una alerta: Ingreso consecutivo sin egreso.', null];
                }

                // NUEVO registro de acceso (en curso) guardando el operador que abrió
                $ins = $this->db->prepare(
                    "INSERT INTO ACCESOS
                 (Usuario_ID, Operador_Ingreso_ID, FechaHora_Entrada, TipoAcceso, Motivo, Estado_ID)
                 VALUES (?, ?, NOW(), 'INGRESO', ?, ?)"
                );
                $ins->execute([
                    $usuarioId,
                    $opId,
                    ($motivo !== '' ? $motivo : null),
                    self::ESTADO_EN_CURSO // = 1 (EN_CURSO)
                ]);

                $this->db->commit();
                return ['INGRESO registrado con éxito.', null, null];
            }

            // EGRESO
            if (!$abierto) {
                // Egreso sin ingreso previo
                $this->registrarAlerta($usuarioId, 'Egreso sin ingreso previo');
                $this->db->commit();
                return [null, 'Se registró una alerta: Egreso sin ingreso previo.', null];
            }

            // Cerrar el acceso abierto: registrar operador que cerró y opcionalmente motivo
            $upd = $this->db->prepare(
                "UPDATE ACCESOS
                SET FechaHora_Salida   = NOW(),
                    TipoAcceso         = 'EGRESO',
                    Estado_ID          = ?,
                    Operador_Egreso_ID = ?,
                    Motivo             = COALESCE(NULLIF(?, ''), Motivo)
              WHERE Acceso_ID = ?"
            );
            $upd->execute([
                self::ESTADO_COMPLETADO,  // = 2 (COMPLETADO)
                $opId,
                $motivo,
                (int)$abierto['Acceso_ID']
            ]);

            $this->db->commit();
            return ['EGRESO registrado con éxito.', null, null];
        } catch (Throwable $e) {
            if ($this->db->inTransaction()) $this->db->rollBack();
            // si querés, logueá $e->getMessage()
            return [null, null, 'Error al registrar la acción.'];
        }
    }
    public function listarVisitasEnCurso(): array
    {
        $pdo = DB::conn();
        $pdo->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);

        $sql = "
      SELECT
        a.Acceso_ID,
        u.Usuario_ID,
        u.Nombre,
        u.Apellido,
        u.Num_Documento,
        u.TipoDoc_ID            AS TipoDocId,     -- << AÑADIDO
        td.Nombre               AS TipoDocNombre, -- (texto “DNI”, “Pasaporte”)
        a.Motivo,
        a.FechaHora_Entrada
      FROM accesos a
      INNER JOIN usuarios u       ON u.Usuario_ID   = a.Usuario_ID
      LEFT  JOIN tipo_documento td ON td.TipoDoc_ID = u.TipoDoc_ID
      INNER JOIN estado_acceso e  ON e.Estado_ID    = a.Estado_ID
      WHERE u.TipoUsuario_ID = :id_visita
        AND a.TipoAcceso     = 'INGRESO'
        AND a.Estado_ID      = :id_en_curso
        AND a.FechaHora_Salida IS NULL
      ORDER BY a.FechaHora_Entrada DESC, a.Acceso_ID DESC
    ";

        $st = $pdo->prepare($sql);
        $st->execute([
            ':id_visita'   => self::TIPO_USUARIO_VISITA_ID,
            ':id_en_curso' => self::ESTADO_EN_CURSO_ID,
        ]);

        return $st->fetchAll(PDO::FETCH_ASSOC);
    }

    /**
     * Egreso rápido: cierra el mismo registro de INGRESO EN_CURSO.
     * - setea FechaHora_Salida = NOW()
     * - Estado = COMPLETADO
     * - Operador_Egreso_ID = $operadorId (seguridad logueado)
     */
    public function egresoRapido(int $accesoId, int $operadorId): array
    {
        if ($accesoId <= 0 || $operadorId <= 0) {
            return ['ok' => false, 'error' => 'Parámetros inválidos'];
        }

        $pdo = DB::conn();
        $pdo->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);
        $pdo->beginTransaction();

        try {
            // Traigo y bloqueo el registro para asegurar estado/consistencia
            $row = $pdo->prepare("
          SELECT Acceso_ID, Usuario_ID, Estado_ID, TipoAcceso, FechaHora_Salida
          FROM accesos
          WHERE Acceso_ID = :id
          FOR UPDATE
        ");
            $row->execute([':id' => $accesoId]);
            $acc = $row->fetch(PDO::FETCH_ASSOC);

            if (!$acc) {
                throw new RuntimeException('Acceso no encontrado');
            }
            if ($acc['TipoAcceso'] !== 'INGRESO') {
                throw new RuntimeException('El acceso no es un INGRESO');
            }
            if ((int)$acc['Estado_ID'] !== self::ESTADO_EN_CURSO_ID) {
                throw new RuntimeException('El acceso no está EN_CURSO');
            }
            if (!is_null($acc['FechaHora_Salida'])) {
                throw new RuntimeException('El acceso ya posee salida');
            }

            // Cierro el acceso
            $upd = $pdo->prepare("
            UPDATE accesos
            SET FechaHora_Salida   = NOW(),
                Estado_ID          = :comp,
                Operador_Egreso_ID = :op,
                TipoAcceso         = 'EGRESO'
            WHERE Acceso_ID = :id
            ");
            $upd->execute([
                ':comp' => self::ESTADO_COMPLETADO_ID,
                ':op'   => $operadorId,
                ':id'   => $accesoId,
            ]);

            $pdo->commit();
            return ['ok' => true];
        } catch (Throwable $e) {
            $pdo->rollBack();
            return ['ok' => false, 'error' => $e->getMessage()];
        }
    }
}
