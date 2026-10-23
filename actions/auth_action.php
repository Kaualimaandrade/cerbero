<?php
/**
 * Ações de Autenticação e Solicitação Pública de Acesso
 * Sistema: Cerbero
 */

require_once __DIR__ . '/../config/bd.php';

$action = $_POST['action'] ?? $_GET['action'] ?? '';

if ($action === 'login') {
    $email = trim($_POST['email'] ?? '');
    $senha = $_POST['senha'] ?? '';

    // Validações no backend
    if (empty($email) || empty($senha)) {
        set_toast('warning', 'Preencha todos os campos para autenticar.');
        header('Location: ../login.php');
        exit;
    }

    if (!filter_var($email, FILTER_VALIDATE_EMAIL)) {
        set_toast('error', 'Formato de e-mail inválido.');
        header('Location: ../login.php');
        exit;
    }

    try {
        $pdo = getConexao();
        $stmt = $pdo->prepare("SELECT id, nome, email, senha, nivel_acesso, cargo, status FROM usuarios WHERE email = :email LIMIT 1");
        $stmt->execute([':email' => $email]);
        $usuario = $stmt->fetch();

        if ($usuario) {
            // Verificar status ativo
            if ($usuario['status'] !== 'Ativo') {
                set_toast('error', 'Esta conta de usuário está desativada. Contate o administrador.');
                header('Location: ../login.php');
                exit;
            }

            // Verificação de senha com suporte a bcrypt e auto-atualização de hash
            $senhaValida = password_verify($senha, $usuario['senha']);
            if (!$senhaValida && ($usuario['senha'] === $senha)) {
                $senhaValida = true;
                // Atualiza hash para bcrypt moderno
                $novoHash = password_hash($senha, PASSWORD_DEFAULT);
                $upd = $pdo->prepare("UPDATE usuarios SET senha = ? WHERE id = ?");
                $upd->execute([$novoHash, $usuario['id']]);
            }

            if ($senhaValida) {
                // Sessão nativa regenerada para prevenir session fixation
                session_regenerate_id(true);

                $_SESSION['usuario_id'] = $usuario['id'];
                $_SESSION['usuario_nome'] = $usuario['nome'];
                $_SESSION['usuario_email'] = $usuario['email'];
                $_SESSION['usuario_nivel'] = $usuario['nivel_acesso'];
                $_SESSION['usuario_cargo'] = $usuario['cargo'] ?? 'Colaborador';

                registrar_log('Login', 'Usuário efetuou login no sistema', $usuario['id']);
                set_toast('success', 'Bem-vindo de volta, ' . $usuario['nome'] . '!');

                header('Location: ../dashboard.php');
                exit;
            }
        }

        // Credenciais inválidas
        registrar_log('Falha de Login', "Tentativa de login malsucedida para o e-mail: $email");
        set_toast('error', 'E-mail ou senha incorretos.');
        header('Location: ../login.php');
        exit;

    } catch (Exception $e) {
        set_toast('error', 'Erro interno ao processar autenticação.');
        header('Location: ../login.php');
        exit;
    }
}

if ($action === 'solicitar_acesso') {
    $nome = trim($_POST['nome'] ?? '');
    $email = trim($_POST['email'] ?? '');
    $cargo = trim($_POST['cargo'] ?? '');
    $departamento = trim($_POST['departamento'] ?? 'Operações');
    $motivo = trim($_POST['motivo'] ?? '');
    $nivel_solicitado = $_POST['nivel_solicitado'] ?? 'Operador';

    // Validações rigorosas no backend
    if (empty($nome) || empty($email) || empty($cargo) || empty($motivo)) {
        set_toast('warning', 'Todos os campos da solicitação são obrigatórios.');
        header('Location: ../login.php?tab=solicitar');
        exit;
    }

    if (!filter_var($email, FILTER_VALIDATE_EMAIL)) {
        set_toast('error', 'Informe um endereço de e-mail corporativo válido.');
        header('Location: ../login.php?tab=solicitar');
        exit;
    }

    $niveisPermitidos = ['Administrador', 'Operador', 'Visualizador'];
    if (!in_array($nivel_solicitado, $niveisPermitidos)) {
        $nivel_solicitado = 'Operador';
    }

    try {
        $pdo = getConexao();

        // Verificar se já existe solicitação pendente para este e-mail
        $stmtChk = $pdo->prepare("SELECT id FROM solicitacoes_acesso WHERE email = :email AND status = 'Pendente' LIMIT 1");
        $stmtChk->execute([':email' => $email]);
        if ($stmtChk->fetch()) {
            set_toast('warning', 'Já existe uma solicitação de acesso pendente para este e-mail. Aguarde a avaliação da equipe.');
            header('Location: ../login.php');
            exit;
        }

        // Inserir solicitação
        $agora = date('Y-m-d H:i:s');
        $stmt = $pdo->prepare("
            INSERT INTO solicitacoes_acesso 
            (nome, email, cargo, departamento, motivo, nivel_solicitado, status, data_solicitacao) 
            VALUES 
            (:nome, :email, :cargo, :departamento, :motivo, :nivel, 'Pendente', :data_solicitacao)
        ");
        $stmt->execute([
            ':nome' => $nome,
            ':email' => $email,
            ':cargo' => $cargo,
            ':departamento' => $departamento,
            ':motivo' => $motivo,
            ':nivel' => $nivel_solicitado,
            ':data_solicitacao' => $agora
        ]);

        registrar_log('Solicitação de Acesso', "Nova solicitação criada por {$nome} ({$email})");
        set_toast('success', 'Solicitação enviada com sucesso! Você receberá resposta assim que for aprovada.');
        header('Location: ../login.php');
        exit;

    } catch (Exception $e) {
        set_toast('error', 'Erro ao registrar solicitação: ' . $e->getMessage());
        header('Location: ../login.php?tab=solicitar');
        exit;
    }
}

// Redirecionamento padrão caso ação não seja reconhecida
header('Location: ../login.php');
exit;
