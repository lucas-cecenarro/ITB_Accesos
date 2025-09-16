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

    public function __construct(?PDO $pdo = null)
    {
        $this->db = $pdo ?: DB::conn();
        $this->db->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);
        @date_default_timezone_set('America/Argentina/Buenos_Aires');
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

        $nombre     = trim($post['nombre']     ?? '');
        $apellido   = trim($post['apellido']   ?? '');
        $dni        = trim($post['dni']        ?? '');
        $tipoDocId  = (int)($post['tipodoc_id'] ?? 1);
        $motivo     = trim($post['motivo']     ?? '');
        if (mb_strlen($motivo) > 200) {
            $motivo = mb_substr($motivo, 0, 200);
        }

        $tipo = strtoupper(trim($post['tipo'] ?? 'INGRESO')); // INGRESO | EGRESO

        if ($nombre === '' || $apellido === '' || $dni === '') {
            return [null, null, 'Complete nombre, apellido y DNI.'];
        }
        if (!in_array($tipo, ['INGRESO', 'EGRESO'], true)) {
            return [null, null, 'Tipo inválido.'];
        }

        try {
            // 1) asegurar/obtener usuario
            $usuarioId = $this->asegurarUsuario($nombre, $apellido, $dni, $tipoDocId);

            // 2) tomar lock por usuario para evitar dobles envíos concurrentes
            $this->lockUser($usuarioId);

            try {
                // 3) transacción
                $this->db->beginTransaction();

                $abierto = $this->ultimoAccesoAbierto($usuarioId); // SELECT ... WHERE FechaHora_Salida IS NULL

                if ($tipo === 'INGRESO') {
                    if ($abierto) {
                        // Ingreso consecutivo sin egreso → alerta
                        $this->registrarAlerta($usuarioId, 'Ingreso consecutivo sin egreso');
                        $this->db->commit();
                        return [null, 'Se registró una alerta: Ingreso consecutivo sin egreso.', null];
                    }

                    // INSERT único de ingreso (con motivo)
                    $ins = $this->db->prepare("
                    INSERT INTO ACCESOS
                        (Usuario_ID, FechaHora_Entrada, TipoAcceso, Motivo, Estado_ID)
                    VALUES (?, NOW(), 'INGRESO', ?, ?)
                ");
                    $ins->execute([
                        $usuarioId,
                        ($motivo !== '' ? $motivo : null),
                        self::ESTADO_EN_CURSO
                    ]);

                    $this->db->commit();
                    return ['INGRESO registrado con éxito.', null, null];
                } else { // EGRESO
                    if (!$abierto) {
                        // Egreso sin ingreso previo → alerta
                        $this->registrarAlerta($usuarioId, 'Egreso sin ingreso previo');
                        $this->db->commit();
                        return [null, 'Se registró una alerta: Egreso sin ingreso previo.', null];
                    }

                    // UPDATE de egreso (cierra el abierto y actualiza motivo si vino)
                    $upd = $this->db->prepare("
                    UPDATE ACCESOS
                       SET FechaHora_Salida = NOW(),
                           TipoAcceso = 'EGRESO',
                           Estado_ID = ?,
                           Motivo = COALESCE(NULLIF(?, ''), Motivo)
                     WHERE Acceso_ID = ?
                ");
                    $upd->execute([
                        self::ESTADO_COMPLETADO,
                        $motivo,
                        (int)$abierto['Acceso_ID']
                    ]);

                    $this->db->commit();
                    return ['EGRESO registrado con éxito.', null, null];
                }
            } finally {
                // 4) liberar el lock sí o sí
                $this->unlockUser($usuarioId);
            }
        } catch (Throwable $e) {
            if ($this->db->inTransaction()) $this->db->rollBack();
            // Opcional: loguear $e->getMessage()
            return [null, null, 'Error al registrar la acción.'];
        }
    }
}
