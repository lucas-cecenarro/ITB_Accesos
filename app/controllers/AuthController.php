<?php
require_once __DIR__ . '/../session.php';
require_once __DIR__ . '/../auth.php';
require_once __DIR__ . '/../config.php';

class AuthController
{
    public static function login(): void
    {
        $email = trim($_POST['email'] ?? '');
        $pass  = (string)($_POST['password'] ?? '');

        if ($email === '' || $pass === '') {
            $_SESSION['login_error'] = 'Completá email y contraseña.';
            header('Location: ' . (BASE_URL . '/login.php'));
            exit;
        }

        // Autentica contra USUARIOS (Password en texto plano)
        $user = AuthModel::login($email, $pass);
        if (!$user) {
            $_SESSION['login_error'] = 'Email o contraseña incorrectos.';
            header('Location: ' . (BASE_URL . '/login.php'));
            exit;
        }

        // Sesión
        setUserSession($user, true);

        // Redirección que pediste
        header('Location: ' . (BASE_URL . '/dashboard.php'));
        exit;
    }

    public static function logout(): void
    {
        logout();
        header('Location: ' . (BASE_URL . '/login.php'));
        exit;
    }
}
