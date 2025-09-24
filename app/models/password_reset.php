<?php
declare(strict_types=1);

require_once __DIR__ . '/../db.php';

class PasswordReset
{
    private PDO $db;

    public function __construct(?PDO $pdo = null)
    {
        $this->db = $pdo ?: DB::conn();
        $this->db->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);
        @date_default_timezone_set('America/Argentina/Buenos_Aires');
    }

    /** Genera un token, guarda su hash y devuelve el token en texto plano */
    public function createForUser(int $userId, string $ip = null, string $ua = null, int $ttlMinutes = 30): string
    {
        // invalidar tokens previos pendientes del usuario (opcional)
        $this->db->prepare("UPDATE password_resets SET used_at = NOW() WHERE user_id = :u AND used_at IS NULL AND expires_at > NOW()")
                 ->execute([':u' => $userId]);

        $token = bin2hex(random_bytes(32)); // 64 chars
        $hash  = hash('sha256', $token);
        $exp   = (new DateTime("+{$ttlMinutes} minutes"))->format('Y-m-d H:i:s');

        $sql = "INSERT INTO password_resets(user_id, token_hash, expires_at, requested_ip, user_agent, created_at)
                VALUES(:u, :h, :e, :ip, :ua, NOW())";
        $this->db->prepare($sql)->execute([
            ':u'  => $userId,
            ':h'  => $hash,
            ':e'  => $exp,
            ':ip' => $ip,
            ':ua' => $ua,
        ]);

        return $token;
    }

    /** Devuelve el registro válido (no usado y no vencido) o null */
    public function findValidByToken(string $token): ?array
    {
        $hash = hash('sha256', $token);
        $sql  = "SELECT * FROM password_resets
                 WHERE token_hash = :h AND used_at IS NULL AND expires_at > NOW()
                 LIMIT 1";
        $st   = $this->db->prepare($sql);
        $st->execute([':h' => $hash]);
        $row = $st->fetch(PDO::FETCH_ASSOC);
        return $row ?: null;
    }

    /** Marca el token como usado */
    public function markUsed(int $id): void
    {
        $this->db->prepare("UPDATE password_resets SET used_at = NOW() WHERE id = :id")
                 ->execute([':id' => $id]);
    }
}
