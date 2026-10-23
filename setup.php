<?php
/**
 * Assistente de Instalação e Inicialização do Banco de Dados
 * Sistema: Cerbero
 */

$msg = '';
$sucesso = false;

// Configurações padrão
$host = getenv('DB_HOST') ?: 'localhost';
$port = getenv('DB_PORT') ?: '3306';
$user = getenv('DB_USER') ?: 'root';
$pass = getenv('DB_PASS') !== false ? getenv('DB_PASS') : '';
$dbname = getenv('DB_NAME') ?: 'cerbero';

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $host = trim($_POST['host'] ?? 'localhost');
    $port = trim($_POST['port'] ?? '3306');
    $user = trim($_POST['user'] ?? 'root');
    $pass = $_POST['pass'] ?? '';
    $dbname = trim($_POST['dbname'] ?? 'cerbero');

    try {
        // Conexão inicial sem especificar banco para poder criá-lo
        $dsn = "mysql:host={$host};port={$port};charset=utf8mb4";
        $pdo = new PDO($dsn, $user, $pass, [
            PDO::ATTR_ERRMODE => PDO::ERRMODE_EXCEPTION
        ]);

        // Cria o banco se não existir
        $pdo->exec("CREATE DATABASE IF NOT EXISTS `{$dbname}` CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci;");
        $pdo->exec("USE `{$dbname}`;");

        // Criação das tabelas
        $pdo->exec("
            CREATE TABLE IF NOT EXISTS `usuarios` (
              `id` INT AUTO_INCREMENT PRIMARY KEY,
              `nome` VARCHAR(100) NOT NULL,
              `email` VARCHAR(150) NOT NULL UNIQUE,
              `senha` VARCHAR(255) NOT NULL,
              `nivel_acesso` ENUM('Administrador', 'Operador', 'Visualizador') NOT NULL DEFAULT 'Operador',
              `cargo` VARCHAR(100) DEFAULT 'Colaborador',
              `status` ENUM('Ativo', 'Inativo') NOT NULL DEFAULT 'Ativo',
              `criado_em` DATETIME DEFAULT CURRENT_TIMESTAMP,
              `atualizado_em` DATETIME DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP
            ) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;

            CREATE TABLE IF NOT EXISTS `solicitacoes_acesso` (
              `id` INT AUTO_INCREMENT PRIMARY KEY,
              `nome` VARCHAR(100) NOT NULL,
              `email` VARCHAR(150) NOT NULL,
              `cargo` VARCHAR(100) NOT NULL,
              `departamento` VARCHAR(100) NOT NULL DEFAULT 'Geral',
              `motivo` TEXT NOT NULL,
              `nivel_solicitado` ENUM('Administrador', 'Operador', 'Visualizador') NOT NULL DEFAULT 'Operador',
              `status` ENUM('Pendente', 'Aprovado', 'Rejeitado') NOT NULL DEFAULT 'Pendente',
              `observacao_resposta` TEXT DEFAULT NULL,
              `respondido_por` INT DEFAULT NULL,
              `data_solicitacao` DATETIME DEFAULT CURRENT_TIMESTAMP,
              `data_resposta` DATETIME DEFAULT NULL,
              FOREIGN KEY (`respondido_por`) REFERENCES `usuarios`(`id`) ON DELETE SET NULL
            ) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;

            CREATE TABLE IF NOT EXISTS `registros` (
              `id` INT AUTO_INCREMENT PRIMARY KEY,
              `titulo` VARCHAR(150) NOT NULL,
              `categoria` VARCHAR(80) NOT NULL,
              `descricao` TEXT NULL,
              `prioridade` ENUM('Baixa', 'Média', 'Alta', 'Crítica') NOT NULL DEFAULT 'Média',
              `status` ENUM('Pendente', 'Em Andamento', 'Concluído', 'Cancelado') NOT NULL DEFAULT 'Pendente',
              `usuario_id` INT NOT NULL,
              `criado_em` DATETIME DEFAULT CURRENT_TIMESTAMP,
              `atualizado_em` DATETIME DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
              FOREIGN KEY (`usuario_id`) REFERENCES `usuarios`(`id`) ON DELETE CASCADE
            ) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;

            CREATE TABLE IF NOT EXISTS `logs_sistema` (
              `id` INT AUTO_INCREMENT PRIMARY KEY,
              `usuario_id` INT DEFAULT NULL,
              `acao` VARCHAR(120) NOT NULL,
              `detalhes` TEXT DEFAULT NULL,
              `ip` VARCHAR(45) DEFAULT NULL,
              `criado_em` DATETIME DEFAULT CURRENT_TIMESTAMP,
              FOREIGN KEY (`usuario_id`) REFERENCES `usuarios`(`id`) ON DELETE SET NULL
            ) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;
        ");

        // Seed de usuários se tabela estiver vazia
        $chk = $pdo->query("SELECT COUNT(*) FROM usuarios")->fetchColumn();
        if ($chk == 0) {
            $senhaHashAdmin = password_hash('admin123', PASSWORD_DEFAULT);
            $senhaHashOperador = password_hash('operador123', PASSWORD_DEFAULT);
            $senhaHashUsuario = password_hash('usuario123', PASSWORD_DEFAULT);

            $stmt = $pdo->prepare("INSERT INTO usuarios (nome, email, senha, nivel_acesso, cargo, status) VALUES (?, ?, ?, ?, ?, ?)");
            $stmt->execute(['Administrador Cerbero', 'admin@cerbero.com', $senhaHashAdmin, 'Administrador', 'Gerente de TI & Segurança', 'Ativo']);
            $stmt->execute(['Kauan Andrade', 'kauan@cerbero.com', $senhaHashAdmin, 'Administrador', 'Líder Técnico', 'Ativo']);
            $stmt->execute(['Mariana Costa', 'mariana@cerbero.com', $senhaHashOperador, 'Operador', 'Analista de Operações', 'Ativo']);
            $stmt->execute(['Carlos Eduardo', 'carlos@cerbero.com', $senhaHashUsuario, 'Visualizador', 'Auditor Interno', 'Ativo']);

            // Solicitações iniciais
            $pdo->exec("
                INSERT INTO solicitacoes_acesso (nome, email, cargo, departamento, motivo, nivel_solicitado, status, data_solicitacao) VALUES
                ('Lucas Silveira', 'lucas.silveira@karhub.com', 'Comprador Júnior', 'Compras & Suprimentos', 'Necessito de acesso ao painel para conferir recebimento de lotes e notas fiscais de fornecedores.', 'Operador', 'Pendente', NOW() - INTERVAL 2 HOUR),
                ('Beatriz Mendes', 'beatriz.mendes@karhub.com', 'Coordenadora de Logística', 'Logística', 'Acompanhamento diário de status de mercadorias e relatórios de fluxo de estoque.', 'Operador', 'Pendente', NOW() - INTERVAL 5 HOUR),
                ('Rodrigo Tavares', 'rodrigo.tavares@karhub.com', 'Estagiário de TI', 'Tecnologia', 'Apoio no suporte de primeiro nível aos operadores do sistema.', 'Visualizador', 'Pendente', NOW() - INTERVAL 1 DAY),
                ('Fernanda Lima', 'fernanda.lima@karhub.com', 'Supervisora de Contratos', 'Financeiro', 'Auditoria de contratos de fornecimento.', 'Operador', 'Aprovado', NOW() - INTERVAL 3 DAY);
            ");

            // Registros iniciais
            $pdo->exec("
                INSERT INTO registros (titulo, categoria, descricao, prioridade, status, usuario_id, criado_em) VALUES
                ('Auditoria de Acesso Q4', 'Segurança', 'Revisão periódica de credenciais e permissões ativas de colaboradores.', 'Alta', 'Em Andamento', 1, NOW() - INTERVAL 4 DAY),
                ('Inventário de Servidores de Aplicação', 'Infraestrutura', 'Catalogação e verificação de integridade dos nós de produção e backup.', 'Média', 'Concluído', 1, NOW() - INTERVAL 3 DAY),
                ('Integração API KarHub - Recebimento', 'Operações', 'Alinhamento do fluxo de entrada de notas fiscais com sistema de compras.', 'Crítica', 'Em Andamento', 2, NOW() - INTERVAL 2 DAY),
                ('Backup Periódico da Base de Dados', 'Manutenção', 'Execução de snapshot completo e verificação de integridade dos hashes.', 'Alta', 'Concluído', 2, NOW() - INTERVAL 1 DAY),
                ('Atualização de Políticas de Senhas', 'Segurança', 'Implementação de expiração de senhas a cada 90 dias e autenticação forte.', 'Média', 'Pendente', 3, NOW() - INTERVAL 6 HOUR);
            ");

            // Log
            $pdo->exec("INSERT INTO logs_sistema (usuario_id, acao, detalhes, ip) VALUES (1, 'Instalação do Sistema', 'Banco de dados criado e populado via assistente setup.php', '127.0.0.1')");
        }

        // Salvar configuração atualizada em config/bd.php
        $configContent = "<?php\n"
            . "/**\n"
            . " * Conexão com o Banco de Dados MySQL utilizando PDO\n"
            . " * Sistema: Cerbero\n"
            . " */\n\n"
            . "if (session_status() === PHP_SESSION_NONE) {\n"
            . "    session_start();\n"
            . "}\n\n"
            . "define('DB_HOST', '" . addslashes($host) . "');\n"
            . "define('DB_NAME', '" . addslashes($dbname) . "');\n"
            . "define('DB_USER', '" . addslashes($user) . "');\n"
            . "define('DB_PASS', '" . addslashes($pass) . "');\n"
            . "define('DB_PORT', '" . addslashes($port) . "');\n"
            . "define('DB_CHARSET', 'utf8mb4');\n\n"
            . "function getConexao() {\n"
            . "    static \$pdo = null;\n"
            . "    if (\$pdo === null) {\n"
            . "        \$dsn = \"mysql:host=\" . DB_HOST . \";port=\" . DB_PORT . \";dbname=\" . DB_NAME . \";charset=\" . DB_CHARSET;\n"
            . "        \$opcoes = [\n"
            . "            PDO::ATTR_ERRMODE            => PDO::ERRMODE_EXCEPTION,\n"
            . "            PDO::ATTR_DEFAULT_FETCH_MODE => PDO::FETCH_ASSOC,\n"
            . "            PDO::ATTR_EMULATE_PREPARES   => false,\n"
            . "            PDO::MYSQL_ATTR_INIT_COMMAND => \"SET NAMES \" . DB_CHARSET\n"
            . "        ];\n"
            . "        try {\n"
            . "            \$pdo = new PDO(\$dsn, DB_USER, DB_PASS, \$opcoes);\n"
            . "        } catch (PDOException \$e) {\n"
            . "            error_log(\"Erro de conexão PDO: \" . \$e->getMessage());\n"
            . "            die(\"<div style='font-family: Arial, sans-serif; max-width: 650px; margin: 40px auto; padding: 24px; border: 1px solid #e2e8f0; border-radius: 12px; background: #fff5f5; color: #9b2c2c;'><h2>⚠️ Falha na Conexão com o Banco de Dados</h2><p>\" . htmlspecialchars(\$e->getMessage()) . \"</p><p><a href='setup.php'>Acessar assistente de instalação</a></p></div>\");\n"
            . "        }\n"
            . "    }\n"
            . "    return \$pdo;\n"
            . "}\n\n"
            . "function set_toast(\$tipo, \$mensagem) {\n"
            . "    if (session_status() === PHP_SESSION_NONE) { session_start(); }\n"
            . "    \$_SESSION['toast'] = ['tipo' => \$tipo, 'mensagem' => \$mensagem];\n"
            . "}\n\n"
            . "function registrar_log(\$acao, \$detalhes = null, \$usuario_id = null) {\n"
            . "    try {\n"
            . "        \$pdo = getConexao();\n"
            . "        if (\$usuario_id === null && isset(\$_SESSION['usuario_id'])) {\n"
            . "            \$usuario_id = \$_SESSION['usuario_id'];\n"
            . "        }\n"
            . "        \$ip = \$_SERVER['REMOTE_ADDR'] ?? 'UNKNOWN';\n"
            . "        \$stmt = \$pdo->prepare(\"INSERT INTO logs_sistema (usuario_id, acao, detalhes, ip) VALUES (:usuario_id, :acao, :detalhes, :ip)\");\n"
            . "        \$stmt->execute([':usuario_id' => \$usuario_id, ':acao' => \$acao, ':detalhes' => \$detalhes, ':ip' => \$ip]);\n"
            . "    } catch (Exception \$e) {\n"
            . "        error_log(\"Erro ao salvar log: \" . \$e->getMessage());\n"
            . "    }\n"
            . "}\n";

        file_put_contents(__DIR__ . '/config/bd.php', $configContent);

        $sucesso = true;
        $msg = "Banco de dados e tabelas configurados com sucesso!";
    } catch (Exception $e) {
        $msg = "Erro durante a instalação: " . $e->getMessage();
    }
}
?>
<!DOCTYPE html>
<html lang="pt-BR">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Cerbero - Instalação do Sistema</title>
    <script src="https://cdn.tailwindcss.com"></script>
    <link href="https://fonts.googleapis.com/css2?family=Plus+Jakarta+Sans:wght@300;400;500;600;700;800&display=swap" rel="stylesheet">
    <style>
        body { font-family: 'Plus Jakarta Sans', sans-serif; }
    </style>
</head>
<body class="bg-slate-900 text-slate-100 min-h-screen flex items-center justify-center p-4">
    <div class="max-w-lg w-full bg-slate-800/80 backdrop-blur-md border border-slate-700/60 rounded-2xl p-8 shadow-2xl">
        <div class="flex items-center space-x-3 mb-6">
            <div class="w-12 h-12 rounded-xl bg-gradient-to-tr from-indigo-600 to-violet-500 flex items-center justify-center shadow-lg shadow-indigo-500/30">
                <svg class="w-7 h-7 text-white" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 15v2m-6 4h12a2 2 0 002-2v-6a2 2 0 00-2-2H6a2 2 0 00-2 2v6a2 2 0 002 2zm10-10V7a4 4 0 00-8 0v4h8z"/>
                </svg>
            </div>
            <div>
                <h1 class="text-2xl font-bold tracking-tight text-white">Cerbero Setup</h1>
                <p class="text-xs text-slate-400">Instalação e configuração do banco de dados MySQL</p>
            </div>
        </div>

        <?php if ($msg): ?>
            <div class="p-4 mb-6 rounded-xl border text-sm <?php echo $sucesso ? 'bg-emerald-950/60 border-emerald-800 text-emerald-300' : 'bg-rose-950/60 border-rose-800 text-rose-300'; ?>">
                <div class="flex items-center space-x-2">
                    <?php if ($sucesso): ?>
                        <svg class="w-5 h-5 text-emerald-400 shrink-0" fill="currentColor" viewBox="0 0 20 20"><path fill-rule="evenodd" d="M10 18a8 8 0 100-16 8 0 000 16zm3.707-9.293a1 1 0 00-1.414-1.414L9 10.586 7.707 9.293a1 1 0 00-1.414 1.414l2 2a1 1 0 001.414 0l4-4z" clip-rule="evenodd"/></svg>
                    <?php else: ?>
                        <svg class="w-5 h-5 text-rose-400 shrink-0" fill="currentColor" viewBox="0 0 20 20"><path fill-rule="evenodd" d="M10 18a8 8 0 100-16 8 0 000 16zM8.707 7.293a1 1 0 00-1.414 1.414L8.586 10l-1.293 1.293a1 1 0 101.414 1.414L10 11.414l1.293 1.293a1 1 0 001.414-1.414L11.414 10l1.293-1.293a1 1 0 00-1.414-1.414L10 8.586 8.707 7.293z" clip-rule="evenodd"/></svg>
                    <?php endif; ?>
                    <span><?php echo htmlspecialchars($msg); ?></span>
                </div>
            </div>
        <?php endif; ?>

        <?php if ($sucesso): ?>
            <div class="space-y-4">
                <div class="bg-slate-900/60 p-4 rounded-xl border border-slate-700/50 space-y-2 text-sm text-slate-300">
                    <p class="font-semibold text-white">Credenciais padrão para primeiro acesso:</p>
                    <p>• <strong>E-mail:</strong> <code class="text-indigo-400 bg-slate-800 px-2 py-0.5 rounded">admin@cerbero.com</code></p>
                    <p>• <strong>Senha:</strong> <code class="text-indigo-400 bg-slate-800 px-2 py-0.5 rounded">admin123</code></p>
                    <p>• <strong>Nível:</strong> Administrador</p>
                </div>
                <a href="login.php" class="w-full inline-flex items-center justify-center px-5 py-3 rounded-xl text-white font-medium bg-gradient-to-r from-indigo-600 to-violet-600 hover:from-indigo-500 hover:to-violet-500 transition shadow-lg shadow-indigo-600/30">
                    Ir para o Login do Cerbero →
                </a>
            </div>
        <?php else: ?>
            <form method="POST" class="space-y-4">
                <div class="grid grid-cols-2 gap-4">
                    <div>
                        <label class="block text-xs font-medium text-slate-400 mb-1">Host MySQL</label>
                        <input type="text" name="host" value="<?php echo htmlspecialchars($host); ?>" required class="w-full bg-slate-900/70 border border-slate-700 rounded-xl px-3.5 py-2.5 text-sm text-white focus:outline-none focus:border-indigo-500">
                    </div>
                    <div>
                        <label class="block text-xs font-medium text-slate-400 mb-1">Porta</label>
                        <input type="text" name="port" value="<?php echo htmlspecialchars($port); ?>" required class="w-full bg-slate-900/70 border border-slate-700 rounded-xl px-3.5 py-2.5 text-sm text-white focus:outline-none focus:border-indigo-500">
                    </div>
                </div>

                <div>
                    <label class="block text-xs font-medium text-slate-400 mb-1">Nome do Banco de Dados</label>
                    <input type="text" name="dbname" value="<?php echo htmlspecialchars($dbname); ?>" required class="w-full bg-slate-900/70 border border-slate-700 rounded-xl px-3.5 py-2.5 text-sm text-white focus:outline-none focus:border-indigo-500">
                </div>

                <div class="grid grid-cols-2 gap-4">
                    <div>
                        <label class="block text-xs font-medium text-slate-400 mb-1">Usuário MySQL</label>
                        <input type="text" name="user" value="<?php echo htmlspecialchars($user); ?>" required class="w-full bg-slate-900/70 border border-slate-700 rounded-xl px-3.5 py-2.5 text-sm text-white focus:outline-none focus:border-indigo-500">
                    </div>
                    <div>
                        <label class="block text-xs font-medium text-slate-400 mb-1">Senha MySQL</label>
                        <input type="password" name="pass" value="<?php echo htmlspecialchars($pass); ?>" placeholder="(em branco no XAMPP)" class="w-full bg-slate-900/70 border border-slate-700 rounded-xl px-3.5 py-2.5 text-sm text-white focus:outline-none focus:border-indigo-500">
                    </div>
                </div>

                <button type="submit" class="w-full mt-2 py-3 px-4 rounded-xl bg-gradient-to-r from-indigo-600 to-violet-600 hover:from-indigo-500 hover:to-violet-500 font-semibold text-white shadow-lg shadow-indigo-600/30 transition">
                    Criar Banco e Instalar Cerbero
                </button>
            </form>
        <?php endif; ?>
    </div>
</body>
</html>
