<?php
/**
 * Ações de Gestão de Usuários (CRUD)
 * Sistema: Cerbero
 */

require_once __DIR__ . '/../includes/auth_check.php';

// Apenas Administradores podem gerenciar usuários
checarNivelAcesso(['Administrador']);

$action = $_POST['action'] ?? $_GET['action'] ?? '';
$pdo = getConexao();

// 1. CRIAR USUÁRIO
if ($action === 'criar') {
    $nome = trim($_POST['nome'] ?? '');
    $email = trim($_POST['email'] ?? '');
    $cargo = trim($_POST['cargo'] ?? 'Colaborador');
    $nivel = $_POST['nivel_acesso'] ?? 'Operador';
    $status = $_POST['status'] ?? 'Ativo';
    $senha = $_POST['senha'] ?? '';

    if (empty($nome) || empty($email) || empty($senha)) {
        set_toast('warning', 'Nome, e-mail e senha são obrigatórios.');
        header('Location: ../usuarios.php');
        exit;
    }

    if (!filter_var($email, FILTER_VALIDATE_EMAIL)) {
        set_toast('error', 'E-mail inválido.');
        header('Location: ../usuarios.php');
        exit;
    }

    if (strlen($senha) < 6) {
        set_toast('error', 'A senha deve ter no mínimo 6 caracteres.');
        header('Location: ../usuarios.php');
        exit;
    }

    try {
        // Verificar e-mail duplicado
        $stmtChk = $pdo->prepare("SELECT id FROM usuarios WHERE email = ? LIMIT 1");
        $stmtChk->execute([$email]);
        if ($stmtChk->fetch()) {
            set_toast('error', 'Já existe um usuário com este e-mail cadastrado.');
            header('Location: ../usuarios.php');
            exit;
        }

        $senhaHash = password_hash($senha, PASSWORD_DEFAULT);
        $stmt = $pdo->prepare("INSERT INTO usuarios (nome, email, senha, nivel_acesso, cargo, status) VALUES (?, ?, ?, ?, ?, ?)");
        $stmt->execute([$nome, $email, $senhaHash, $nivel, $cargo, $status]);

        registrar_log('Criar Usuário', "Usuário {$nome} ({$email}) criado como {$nivel}");
        set_toast('success', "Usuário '{$nome}' cadastrado com sucesso!");
    } catch (Exception $e) {
        set_toast('error', 'Erro ao cadastrar usuário: ' . $e->getMessage());
    }

    header('Location: ../usuarios.php');
    exit;
}

// 2. EDITAR USUÁRIO
if ($action === 'editar') {
    $id = (int)($_POST['id'] ?? 0);
    $nome = trim($_POST['nome'] ?? '');
    $email = trim($_POST['email'] ?? '');
    $cargo = trim($_POST['cargo'] ?? 'Colaborador');
    $nivel = $_POST['nivel_acesso'] ?? 'Operador';
    $status = $_POST['status'] ?? 'Ativo';
    $novaSenha = $_POST['nova_senha'] ?? '';

    if ($id <= 0 || empty($nome) || empty($email)) {
        set_toast('warning', 'Dados incompletos para atualização.');
        header('Location: ../usuarios.php');
        exit;
    }

    try {
        // Verificar e-mail duplicado em outro id
        $stmtChk = $pdo->prepare("SELECT id FROM usuarios WHERE email = ? AND id != ? LIMIT 1");
        $stmtChk->execute([$email, $id]);
        if ($stmtChk->fetch()) {
            set_toast('error', 'Este e-mail já pertence a outro usuário.');
            header('Location: ../usuarios.php');
            exit;
        }

        if (!empty($novaSenha)) {
            if (strlen($novaSenha) < 6) {
                set_toast('error', 'A nova senha deve ter no mínimo 6 caracteres.');
                header('Location: ../usuarios.php');
                exit;
            }
            $hash = password_hash($novaSenha, PASSWORD_DEFAULT);
            $stmt = $pdo->prepare("UPDATE usuarios SET nome = ?, email = ?, cargo = ?, nivel_acesso = ?, status = ?, senha = ? WHERE id = ?");
            $stmt->execute([$nome, $email, $cargo, $nivel, $status, $hash, $id]);
        } else {
            $stmt = $pdo->prepare("UPDATE usuarios SET nome = ?, email = ?, cargo = ?, nivel_acesso = ?, status = ? WHERE id = ?");
            $stmt->execute([$nome, $email, $cargo, $nivel, $status, $id]);
        }

        // Se o próprio usuário editou seus dados, atualiza a sessão
        if ($id === (int)$_SESSION['usuario_id']) {
            $_SESSION['usuario_nome'] = $nome;
            $_SESSION['usuario_email'] = $email;
            $_SESSION['usuario_nivel'] = $nivel;
            $_SESSION['usuario_cargo'] = $cargo;
        }

        registrar_log('Editar Usuário', "Dados do usuário ID #{$id} ({$nome}) atualizados");
        set_toast('success', "Usuário '{$nome}' atualizado com sucesso!");
    } catch (Exception $e) {
        set_toast('error', 'Erro ao atualizar usuário: ' . $e->getMessage());
    }

    header('Location: ../usuarios.php');
    exit;
}

// 3. EXCLUIR USUÁRIO
if ($action === 'excluir') {
    $id = (int)($_POST['id'] ?? $_GET['id'] ?? 0);

    if ($id <= 0) {
        set_toast('error', 'ID de usuário inválido.');
        header('Location: ../usuarios.php');
        exit;
    }

    // Não permitir que o usuário exclua a si próprio
    if ($id === (int)$_SESSION['usuario_id']) {
        set_toast('error', 'Você não pode excluir sua própria conta conectada.');
        header('Location: ../usuarios.php');
        exit;
    }

    try {
        $stmtNome = $pdo->prepare("SELECT nome FROM usuarios WHERE id = ?");
        $stmtNome->execute([$id]);
        $nomeExcluido = $stmtNome->fetchColumn();

        $stmt = $pdo->prepare("DELETE FROM usuarios WHERE id = ?");
        $stmt->execute([$id]);

        registrar_log('Excluir Usuário', "Usuário {$nomeExcluido} (ID #{$id}) removido");
        set_toast('success', "Usuário removido com sucesso!");
    } catch (Exception $e) {
        set_toast('error', 'Erro ao excluir usuário: ' . $e->getMessage());
    }

    header('Location: ../usuarios.php');
    exit;
}

header('Location: ../usuarios.php');
exit;
