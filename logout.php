<?php
/**
 * Encerramento Seguro de Sessão (logout.php)
 * Sistema: Cerbero
 */

require_once __DIR__ . '/config/bd.php';

if (session_status() === PHP_SESSION_NONE) {
    session_start();
}

if (isset($_SESSION['usuario_id'])) {
    registrar_log('Logout', 'Usuário encerrou a sessão', $_SESSION['usuario_id']);
}

// Limpar todos os dados da sessão
$_SESSION = [];

if (ini_get("session.use_cookies")) {
    $params = session_get_cookie_params();
    setcookie(
        session_name(),
        '',
        time() - 42000,
        $params["path"],
        $params["domain"],
        $params["secure"],
        $params["httponly"]
    );
}

session_destroy();

// Inicia nova sessão temporária apenas para a mensagem toast
session_start();
set_toast('info', 'Sessão encerrada com segurança. Até logo!');

header('Location: login.php');
exit;
