<?php
/**
 * Conexão com o Banco de Dados (MySQL PDO com fallback SQLite para testes locais)
 * Sistema: Cerbero
 */

if (session_status() === PHP_SESSION_NONE) {
    session_start();
}

// Configurações do Banco de Dados MySQL
define('DB_HOST', getenv('DB_HOST') ?: 'localhost');
define('DB_NAME', getenv('DB_NAME') ?: 'cerbero');
define('DB_USER', getenv('DB_USER') ?: 'root');
define('DB_PASS', getenv('DB_PASS') !== false ? getenv('DB_PASS') : '');
define('DB_PORT', getenv('DB_PORT') ?: '3306');
define('DB_CHARSET', 'utf8mb4');

/**
 * Retorna a instância única do PDO (Singleton)
 * Conecta ao MySQL; se o MySQL não estiver disponível localmente,
 * ativa automaticamente a base local SQLite para emulação imediata.
 * 
 * @return PDO
 */
function getConexao() {
    static $pdo = null;

    if ($pdo === null) {
        $tentarMysql = true;

        if ($tentarMysql) {
            try {
                $dsn = "mysql:host=" . DB_HOST . ";port=" . DB_PORT . ";dbname=" . DB_NAME . ";charset=" . DB_CHARSET;
                $opcoes = [
                    PDO::ATTR_ERRMODE            => PDO::ERRMODE_EXCEPTION,
                    PDO::ATTR_DEFAULT_FETCH_MODE => PDO::FETCH_ASSOC,
                    PDO::ATTR_EMULATE_PREPARES   => false,
                    PDO::ATTR_TIMEOUT            => 2,
                ];
                $pdo = new PDO($dsn, DB_USER, DB_PASS, $opcoes);
                return $pdo;
            } catch (Exception $e) {
                // MySQL não está em execução nesta máquina (ex: emulação local).
                // Ativando SQLite de desenvolvimento e testes.
            }
        }

        // Fallback SQLite para ambiente local
        $sqlitePath = dirname(__DIR__) . '/database.sqlite';
        $criarEstrutura = !file_exists($sqlitePath) || (filesize($sqlitePath) === 0);

        $pdo = new PDO("sqlite:" . $sqlitePath, null, null, [
            PDO::ATTR_ERRMODE            => PDO::ERRMODE_EXCEPTION,
            PDO::ATTR_DEFAULT_FETCH_MODE => PDO::FETCH_ASSOC,
        ]);

        if ($criarEstrutura) {
            inicializarSQLite($pdo);
        }
    }

    return $pdo;
}

/**
 * Inicializa a estrutura e seed data no SQLite para testes imediatos
 */
function inicializarSQLite(PDO $pdo) {
    $pdo->exec("
        CREATE TABLE IF NOT EXISTS usuarios (
            id INTEGER PRIMARY KEY AUTOINCREMENT,
            nome TEXT NOT NULL,
            email TEXT NOT NULL UNIQUE,
            senha TEXT NOT NULL,
            nivel_acesso TEXT NOT NULL DEFAULT 'Operador',
            cargo TEXT DEFAULT 'Colaborador',
            status TEXT NOT NULL DEFAULT 'Ativo',
            criado_em TEXT DEFAULT CURRENT_TIMESTAMP,
            atualizado_em TEXT DEFAULT CURRENT_TIMESTAMP
        );

        CREATE TABLE IF NOT EXISTS solicitacoes_acesso (
            id INTEGER PRIMARY KEY AUTOINCREMENT,
            nome TEXT NOT NULL,
            email TEXT NOT NULL,
            cargo TEXT NOT NULL,
            departamento TEXT NOT NULL DEFAULT 'Geral',
            motivo TEXT NOT NULL,
            nivel_solicitado TEXT NOT NULL DEFAULT 'Operador',
            status TEXT NOT NULL DEFAULT 'Pendente',
            observacao_resposta TEXT DEFAULT NULL,
            respondido_por INTEGER DEFAULT NULL,
            data_solicitacao TEXT DEFAULT CURRENT_TIMESTAMP,
            data_resposta TEXT DEFAULT NULL
        );

        CREATE TABLE IF NOT EXISTS registros (
            id INTEGER PRIMARY KEY AUTOINCREMENT,
            titulo TEXT NOT NULL,
            categoria TEXT NOT NULL,
            descricao TEXT NULL,
            prioridade TEXT NOT NULL DEFAULT 'Média',
            status TEXT NOT NULL DEFAULT 'Pendente',
            usuario_id INTEGER NOT NULL,
            criado_em TEXT DEFAULT CURRENT_TIMESTAMP,
            atualizado_em TEXT DEFAULT CURRENT_TIMESTAMP
        );

        CREATE TABLE IF NOT EXISTS logs_sistema (
            id INTEGER PRIMARY KEY AUTOINCREMENT,
            usuario_id INTEGER DEFAULT NULL,
            acao TEXT NOT NULL,
            detalhes TEXT DEFAULT NULL,
            ip TEXT DEFAULT NULL,
            criado_em TEXT DEFAULT CURRENT_TIMESTAMP
        );
    ");

    $chk = $pdo->query("SELECT COUNT(*) FROM usuarios")->fetchColumn();
    if ($chk == 0) {
        $hashAdmin = password_hash('admin123', PASSWORD_DEFAULT);
        $hashOp = password_hash('operador123', PASSWORD_DEFAULT);
        $hashUser = password_hash('usuario123', PASSWORD_DEFAULT);
        $agora = date('Y-m-d H:i:s');

        $stmt = $pdo->prepare("INSERT INTO usuarios (nome, email, senha, nivel_acesso, cargo, status, criado_em) VALUES (?, ?, ?, ?, ?, ?, ?)");
        $stmt->execute(['Administrador Cerbero', 'admin@cerbero.com', $hashAdmin, 'Administrador', 'Gerente de TI & Segurança', 'Ativo', $agora]);
        $stmt->execute(['Kauan Andrade', 'kauan@cerbero.com', $hashAdmin, 'Administrador', 'Líder Técnico', 'Ativo', $agora]);
        $stmt->execute(['Mariana Costa', 'mariana@cerbero.com', $hashOp, 'Operador', 'Analista de Operações', 'Ativo', $agora]);
        $stmt->execute(['Carlos Eduardo', 'carlos@cerbero.com', $hashUser, 'Visualizador', 'Auditor Interno', 'Ativo', $agora]);

        $pdo->exec("
            INSERT INTO solicitacoes_acesso (nome, email, cargo, departamento, motivo, nivel_solicitado, status, data_solicitacao) VALUES
            ('Lucas Silveira', 'lucas.silveira@karhub.com', 'Comprador Júnior', 'Compras & Suprimentos', 'Necessito de acesso ao painel para conferir recebimento de lotes e notas fiscais de fornecedores.', 'Operador', 'Pendente', '{$agora}'),
            ('Beatriz Mendes', 'beatriz.mendes@karhub.com', 'Coordenadora de Logística', 'Logística', 'Acompanhamento diário de status de mercadorias e relatórios de fluxo de estoque.', 'Operador', 'Pendente', '{$agora}'),
            ('Rodrigo Tavares', 'rodrigo.tavares@karhub.com', 'Estagiário de TI', 'Tecnologia', 'Apoio no suporte de primeiro nível aos operadores do sistema.', 'Visualizador', 'Pendente', '{$agora}'),
            ('Fernanda Lima', 'fernanda.lima@karhub.com', 'Supervisora de Contratos', 'Financeiro', 'Auditoria de contratos de fornecimento.', 'Operador', 'Aprovado', '{$agora}');
        ");

        $pdo->exec("
            INSERT INTO registros (titulo, categoria, descricao, prioridade, status, usuario_id, criado_em) VALUES
            ('Auditoria de Acesso Q4', 'Segurança', 'Revisão periódica de credenciais e permissões ativas de colaboradores.', 'Alta', 'Em Andamento', 1, '{$agora}'),
            ('Inventário de Servidores de Aplicação', 'Infraestrutura', 'Catalogação e verificação de integridade dos nós de produção e backup.', 'Média', 'Concluído', 1, '{$agora}'),
            ('Integração API KarHub - Recebimento', 'Operações', 'Alinhamento do fluxo de entrada de notas fiscais com sistema de compras.', 'Crítica', 'Em Andamento', 2, '{$agora}'),
            ('Backup Periódico da Base de Dados', 'Manutenção', 'Execução de snapshot completo e verificação de integridade dos hashes.', 'Alta', 'Concluído', 2, '{$agora}'),
            ('Atualização de Políticas de Senhas', 'Segurança', 'Implementação de expiração de senhas a cada 90 dias e autenticação forte.', 'Média', 'Pendente', 3, '{$agora}');
        ");

        $pdo->exec("INSERT INTO logs_sistema (usuario_id, acao, detalhes, ip, criado_em) VALUES (1, 'Emulação Local Iniciada', 'Ambiente de teste e banco inicializados com sucesso', '127.0.0.1', '{$agora}')");
    }
}

/**
 * Helper para registrar mensagens Toast na sessão
 */
function set_toast($tipo, $mensagem) {
    if (session_status() === PHP_SESSION_NONE) {
        session_start();
    }
    $_SESSION['toast'] = [
        'tipo' => $tipo,
        'mensagem' => $mensagem
    ];
}

/**
 * Registra log de auditoria no sistema
 */
function registrar_log($acao, $detalhes = null, $usuario_id = null) {
    try {
        $pdo = getConexao();
        if ($usuario_id === null && isset($_SESSION['usuario_id'])) {
            $usuario_id = $_SESSION['usuario_id'];
        }
        $ip = $_SERVER['REMOTE_ADDR'] ?? '127.0.0.1';
        $agora = date('Y-m-d H:i:s');

        $stmt = $pdo->prepare("INSERT INTO logs_sistema (usuario_id, acao, detalhes, ip, criado_em) VALUES (:usuario_id, :acao, :detalhes, :ip, :criado_em)");
        $stmt->execute([
            ':usuario_id' => $usuario_id,
            ':acao' => $acao,
            ':detalhes' => $detalhes,
            ':ip' => $ip,
            ':criado_em' => $agora
        ]);
    } catch (Exception $e) {
        error_log("Erro ao salvar log: " . $e->getMessage());
    }
}
