<?php
/**
 * Verificação de Autenticação e Controle de Sessão
 * Sistema: Cerbero
 */

if (session_status() === PHP_SESSION_NONE) {
    session_start();
}

require_once __DIR__ . '/../config/bd.php';

// Se não houver usuário logado, redireciona para login
if (!isset($_SESSION['usuario_id'])) {
    set_toast('warning', 'Faça login para acessar o sistema.');
    header('Location: login.php');
    exit;
}

/**
 * Valida se o usuário conectado possui um dos níveis permitidos
 * 
 * @param array $niveisPermitidos Ex: ['Administrador', 'Operador']
 */
function checarNivelAcesso($niveisPermitidos = ['Administrador']) {
    $nivelAtual = $_SESSION['usuario_nivel'] ?? '';
    if (!in_array($nivelAtual, $niveisPermitidos)) {
        set_toast('error', 'Acesso negado: seu nível de acesso não permite esta operação.');
        header('Location: dashboard.php');
        exit;
    }
}
