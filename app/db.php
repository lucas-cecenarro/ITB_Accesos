<?php
require_once __DIR__ . '/config.php';

class DB {
  private static $pdo = null;

  public static function conn() {
    if (self::$pdo !== null) return self::$pdo;

    $dsn  = "mysql:host=".DB_HOST.";dbname=".DB_NAME.";charset=".DB_CHARSET;
    $opts = [
      PDO::ATTR_ERRMODE            => PDO::ERRMODE_EXCEPTION,
      PDO::ATTR_DEFAULT_FETCH_MODE => PDO::FETCH_ASSOC,
      PDO::ATTR_EMULATE_PREPARES   => false,
    ];

    try {
      self::$pdo = new PDO($dsn, DB_USER, DB_PASS, $opts);
      // Zona horaria por offset
      self::$pdo->exec("SET time_zone = '-03:00'");
      return self::$pdo;
    } catch (Throwable $e) {
      if (APP_DEBUG) {
        die("Error de conexión a BD: ".$e->getMessage());
      }
      http_response_code(500);
      die("No se pudo conectar a la base de datos.");
    }
  }
}
