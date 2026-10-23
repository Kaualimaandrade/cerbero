<?php
/**
 * Ações de Gestão de Solicitações de Acesso
 * Sistema: Cerbero
 */

require_once __DIR__ . '/../includes/auth_check.php';

// Apenas Administradores ou Operadores podem gerenciar solicitações
checarNivelAcesso(['Administrador', 'Operador']);

$action = $_POST['action'] ?? $_GET['action'] ?? '';
$pdo = getConexao();

// 1. APROVAR SOLICITAÇÃO
if ($action === 'aprovar') {
    $id = (int)($_POST['id'] ?? 0);
    $nivel_atribuido = $_POST['nivel_acesso'] ?? 'Operador';
    $criar_usuario = isset($_POST['criar_usuario']); // checkbox
    $observacao = trim($_POST['observacao'] ?? 'Solicitação aprovada pelo gestor.');

    if ($id <= 0) {
        set_toast('error', 'Identificador de solicitação inválido.');
        header('Location: ../solicitacoes.php');
        exit;
    }

    try {
        // Obter dados da solicitação
        $stmt = $pdo->prepare("SELECT * FROM solicitacoes_acesso WHERE id = ?");
        $stmt->execute([$id]);
        $solicitacao = $stmt->fetch();

        if (!$solicitacao) {
            set_toast('error', 'Solicitação não encontrada.');
            header('Location: ../solicitacoes.php');
            exit;
        }

        // Se for criar conta de usuário automaticamente
        if ($criar_usuario) {
            // Checar se já existe conta com este e-mail
            $stmtUser = $pdo->prepare("SELECT id FROM usuarios WHERE email = ?");
            $stmtUser->execute([$solicitacao['email']]);
            $userExistente = $stmtUser->fetch();

            if ($userExistente) {
                // Apenas reativa/atualiza
                $updUser = $pdo->prepare("UPDATE usuarios SET nivel_acesso = ?, status = 'Ativo' WHERE id = ?");
                $updUser->execute([$nivel_atribuido, $userExistente['id']]);
            } else {
                // Cria novo usuário com senha padrão inicial
                $senhaInicial = 'mudar123';
                $hash = password_hash($senhaInicial, PASSWORD_DEFAULT);
                $insUser = $pdo->prepare("INSERT INTO usuarios (nome, email, senha, nivel_acesso, cargo, status) VALUES (?, ?, ?, ?, ?, 'Ativo')");
                $insUser->execute([$solicitacao['nome'], $solicitacao['email'], $hash, $nivel_atribuido, $solicitacao['cargo']]);
            }
        }

        // Atualizar a solicitação
        $agora = date('Y-m-d H:i:s');
        $updSol = $pdo->prepare("UPDATE solicitacoes_acesso SET status = 'Aprovado', observacao_resposta = ?, respondido_por = ?, data_resposta = ? WHERE id = ?");
        $updSol->execute([$observacao, $_SESSION['usuario_id'], $agora, $id]);

        registrar_log('Aprovar Solicitação', "Solicitação #{$id} de {$solicitacao['nome']} ({$solicitacao['email']}) foi aprovada");
        set_toast('success', "Solicitação de {$solicitacao['nome']} aprovada com sucesso!" . ($criar_usuario ? " (Usuário criado com senha temporária 'mudar123')" : ""));

    } catch (Exception $e) {
        set_toast('error', 'Erro ao aprovar solicitação: ' . $e->getMessage());
    }

    header('Location: ../solicitacoes.php');
    exit;
}

// 2. REJEITAR SOLICITAÇÃO
if ($action === 'rejeitar') {
    $id = (int)($_POST['id'] ?? 0);
    $motivo_rejeicao = trim($_POST['motivo_rejeicao'] ?? 'Rejeitado por incompatibilidade de perfil.');

    if ($id <= 0) {
        set_toast('error', 'Identificador de solicitação inválido.');
        header('Location: ../solicitacoes.php');
        exit;
    }

    try {
        $agora = date('Y-m-d H:i:s');
        $stmt = $pdo->prepare("UPDATE solicitacoes_acesso SET status = 'Rejeitado', observacao_resposta = ?, respondido_por = ?, data_resposta = ? WHERE id = ?");
        $stmt->execute([$motivo_rejeicao, $_SESSION['usuario_id'], $agora, $id]);

        registrar_log('Rejeitar Solicitação', "Solicitação #{$id} rejeitada. Motivo: {$motivo_rejeicao}");
        set_toast('info', 'Solicitação rejeitada com justificativa registrada.');
    } catch (Exception $e) {
        set_toast('error', 'Erro ao rejeitar solicitação: ' . $e->getMessage());
    }

    header('Location: ../solicitacoes.php');
    exit;
}

header('Location: ../solicitacoes.php');
exit;
