<?php
/**
 * Ponto de Entrada Principal (index.php)
 * Sistema: Cerbero
 */

if (session_status() === PHP_SESSION_NONE) {
    session_start();
}

// Se o usuário já estiver autenticado, redireciona para o Dashboard
if (isset($_SESSION['usuario_id'])) {
    header('Location: dashboard.php');
    exit;
}

// Caso contrário, redireciona para a tela de Login
header('Location: login.php');
exit;
