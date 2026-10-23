<?php
/**
 * Ações de Gestão de Registros do Sistema
 * Sistema: Cerbero
 */

require_once __DIR__ . '/../includes/auth_check.php';

// Administradores e Operadores podem alterar; Visualizadores podem apenas ler
checarNivelAcesso(['Administrador', 'Operador']);

$action = $_POST['action'] ?? $_GET['action'] ?? '';
$pdo = getConexao();

// 1. CRIAR REGISTRO
if ($action === 'criar') {
    $titulo = trim($_POST['titulo'] ?? '');
    $categoria = trim($_POST['categoria'] ?? '');
    $prioridade = $_POST['prioridade'] ?? 'Média';
    $status = $_POST['status'] ?? 'Pendente';
    $descricao = trim($_POST['descricao'] ?? '');

    if (empty($titulo) || empty($categoria)) {
        set_toast('warning', 'Título e Categoria são obrigatórios.');
        header('Location: ../registros.php');
        exit;
    }

    try {
        $stmt = $pdo->prepare("INSERT INTO registros (titulo, categoria, prioridade, status, descricao, usuario_id) VALUES (?, ?, ?, ?, ?, ?)");
        $stmt->execute([$titulo, $categoria, $prioridade, $status, $descricao, $_SESSION['usuario_id']]);

        registrar_log('Criar Registro', "Novo registro criado: '{$titulo}'");
        set_toast('success', "Registro '{$titulo}' cadastrado com sucesso!");
    } catch (Exception $e) {
        set_toast('error', 'Erro ao cadastrar registro: ' . $e->getMessage());
    }

    header('Location: ../registros.php');
    exit;
}

// 2. EDITAR REGISTRO
if ($action === 'editar') {
    $id = (int)($_POST['id'] ?? 0);
    $titulo = trim($_POST['titulo'] ?? '');
    $categoria = trim($_POST['categoria'] ?? '');
    $prioridade = $_POST['prioridade'] ?? 'Média';
    $status = $_POST['status'] ?? 'Pendente';
    $descricao = trim($_POST['descricao'] ?? '');

    if ($id <= 0 || empty($titulo) || empty($categoria)) {
        set_toast('warning', 'Dados incompletos para atualização.');
        header('Location: ../registros.php');
        exit;
    }

    try {
        $stmt = $pdo->prepare("UPDATE registros SET titulo = ?, categoria = ?, prioridade = ?, status = ?, descricao = ? WHERE id = ?");
        $stmt->execute([$titulo, $categoria, $prioridade, $status, $descricao, $id]);

        registrar_log('Editar Registro', "Registro #{$id} ({$titulo}) atualizado");
        set_toast('success', "Registro atualizado com sucesso!");
    } catch (Exception $e) {
        set_toast('error', 'Erro ao atualizar registro: ' . $e->getMessage());
    }

    header('Location: ../registros.php');
    exit;
}

// 3. EXCLUIR REGISTRO
if ($action === 'excluir') {
    $id = (int)($_POST['id'] ?? $_GET['id'] ?? 0);

    if ($id <= 0) {
        set_toast('error', 'Identificador de registro inválido.');
        header('Location: ../registros.php');
        exit;
    }

    try {
        $stmt = $pdo->prepare("DELETE FROM registros WHERE id = ?");
        $stmt->execute([$id]);

        registrar_log('Excluir Registro', "Registro #{$id} removido");
        set_toast('success', 'Registro removido com sucesso!');
    } catch (Exception $e) {
        set_toast('error', 'Erro ao excluir registro: ' . $e->getMessage());
    }

    header('Location: ../registros.php');
    exit;
}

header('Location: ../registros.php');
exit;
