<?php
require_once __DIR__ . '/db.php';
require_once __DIR__ . '/config.php';

class AuthModel {

  public static function login(string $correo, string $password): ?array
  {
    if ($correo === '' || $password === '') return null;

    $allowedRoles = [ROLE_SUPERUSUARIO, ROLE_SEGURIDAD];
    $placeholders = implode(',', array_fill(0, count($allowedRoles), '?'));

    $sql = "
      SELECT 
        u.Usuario_ID,
        u.Nombre,
        u.Apellido,
        u.Correo,
        u.Num_Documento,
        u.Rol_ID,
        r.Nombre AS rol,
        u.Password
      FROM USUARIOS u
      LEFT JOIN ROLES r ON r.Rol_ID = u.Rol_ID
      WHERE u.Correo = ?
        AND u.Rol_ID IN ($placeholders)
      LIMIT 1
    ";

    $params = array_merge([$correo], $allowedRoles);
    $stmt   = DB::conn()->prepare($sql);
    $stmt->execute($params);
    $u = $stmt->fetch();

    if (!$u) return null;

    // *** Texto plano (como pediste) ***
    if ((string)$u['Password'] !== (string)$password) {
      return null;
    }

    // OK
    return $u;
  }
}
