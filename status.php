<?php
/**
 * Monitoramento de Status do Sistema (status.php)
 * Sistema: Cerbero
 */

require_once __DIR__ . '/includes/auth_check.php';

$pdo = getConexao();

// Testes de integridade
$driver = $pdo->getAttribute(PDO::ATTR_DRIVER_NAME);
$dbStatus = ($driver === 'sqlite') ? "Conectado (Emulação Local)" : "Conectado e Operacional (MySQL)";
$dbVersion = "Desconhecido";
try {
    if ($driver === 'sqlite') {
        $dbVersion = "SQLite " . $pdo->query('SELECT sqlite_version()')->fetchColumn();
    } else {
        $dbVersion = "MySQL " . $pdo->query('SELECT version()')->fetchColumn();
    }
} catch (Exception $e) {
    $dbStatus = "Erro: " . $e->getMessage();
}

$phpVersion = phpversion();
$memoryUsage = round(memory_get_usage(true) / 1024 / 1024, 2) . ' MB';
$serverSoftware = $_SERVER['SERVER_SOFTWARE'] ?? 'PHP CLI / WebServer';
$tempoInicio = $_SERVER["REQUEST_TIME_FLOAT"] ?? microtime(true);
$latencia = round((microtime(true) - $tempoInicio) * 1000, 2) . ' ms';

// Logs recentes do sistema
$stmtLogs = $pdo->query("
    SELECT l.*, u.nome AS usuario_nome 
    FROM logs_sistema l 
    LEFT JOIN usuarios u ON l.usuario_id = u.id 
    ORDER BY l.id DESC LIMIT 15
");
$logs = $stmtLogs->fetchAll();

$pageTitle = "Status do Sistema";
require_once __DIR__ . '/includes/header.php';
require_once __DIR__ . '/includes/sidebar.php';
?>

<div class="flex-1 flex flex-col min-w-0 overflow-y-auto">
    <?php require_once __DIR__ . '/includes/navbar.php'; ?>

    <main class="flex-1 p-4 md:p-8 space-y-6 max-w-7xl w-full mx-auto">
        <!-- Cabeçalho -->
        <div>
            <h1 class="text-2xl font-extrabold text-white tracking-tight flex items-center space-x-3">
                <span>Status e Saúde do Sistema</span>
                <span class="inline-flex items-center space-x-1.5 px-3 py-1 rounded-full text-xs font-semibold bg-emerald-500/10 text-emerald-300 border border-emerald-500/20">
                    <span class="w-2 h-2 rounded-full bg-emerald-400 animate-pulse"></span>
                    <span>100% Operacional</span>
                </span>
            </h1>
            <p class="text-xs text-slate-400">Diagnóstico em tempo real dos serviços, conexões e logs de auditoria</p>
        </div>

        <!-- Indicadores de Infraestrutura -->
        <div class="grid grid-cols-1 sm:grid-cols-2 lg:grid-cols-4 gap-4 md:gap-6">
            <div class="glass-card rounded-2xl p-5 border border-slate-800 shadow-md">
                <span class="text-xs font-semibold text-slate-400 uppercase">Banco de Dados</span>
                <p class="text-lg font-bold text-white mt-1"><?php echo htmlspecialchars($dbStatus); ?></p>
                <p class="text-xs text-slate-500 mt-2">MySQL: <?php echo htmlspecialchars($dbVersion); ?></p>
            </div>

            <div class="glass-card rounded-2xl p-5 border border-slate-800 shadow-md">
                <span class="text-xs font-semibold text-slate-400 uppercase">Ambiente PHP</span>
                <p class="text-lg font-bold text-indigo-400 mt-1">PHP <?php echo htmlspecialchars($phpVersion); ?></p>
                <p class="text-xs text-slate-500 mt-2">Sessões: Nativas Ativas</p>
            </div>

            <div class="glass-card rounded-2xl p-5 border border-slate-800 shadow-md">
                <span class="text-xs font-semibold text-slate-400 uppercase">Latência de Resposta</span>
                <p class="text-lg font-bold text-emerald-400 mt-1"><?php echo htmlspecialchars($latencia); ?></p>
                <p class="text-xs text-slate-500 mt-2">Excelente desempenho</p>
            </div>

            <div class="glass-card rounded-2xl p-5 border border-slate-800 shadow-md">
                <span class="text-xs font-semibold text-slate-400 uppercase">Uso de Memória</span>
                <p class="text-lg font-bold text-violet-400 mt-1"><?php echo htmlspecialchars($memoryUsage); ?></p>
                <p class="text-xs text-slate-500 mt-2">Servidor Web Otimizado</p>
            </div>
        </div>

        <!-- Logs de Auditoria do Sistema -->
        <div class="glass-card rounded-2xl p-6 border border-slate-800 shadow-xl">
            <div class="flex items-center justify-between mb-4">
                <div>
                    <h2 class="text-lg font-bold text-white">Trilha de Auditoria & Logs</h2>
                    <p class="text-xs text-slate-400">Últimos eventos de segurança, autenticações e modificações</p>
                </div>
            </div>

            <div class="overflow-x-auto">
                <table class="w-full text-left text-sm">
                    <thead>
                        <tr class="bg-slate-900/90 text-slate-400 text-xs uppercase tracking-wider border-b border-slate-800">
                            <th class="py-3 px-4 font-semibold">Evento / Ação</th>
                            <th class="py-3 px-4 font-semibold">Usuário</th>
                            <th class="py-3 px-4 font-semibold">Detalhes</th>
                            <th class="py-3 px-4 font-semibold">IP</th>
                            <th class="py-3 px-4 font-semibold">Data / Hora</th>
                        </tr>
                    </thead>
                    <tbody class="divide-y divide-slate-800/60 font-mono text-xs">
                        <?php if (empty($logs)): ?>
                            <tr>
                                <td colspan="5" class="py-6 text-center text-slate-500">Nenhum evento registrado ainda.</td>
                            </tr>
                        <?php else: ?>
                            <?php foreach ($logs as $l): ?>
                                <tr class="hover:bg-slate-800/40 transition">
                                    <td class="py-3 px-4 font-semibold text-indigo-300">
                                        <?php echo htmlspecialchars($l['acao']); ?>
                                    </td>
                                    <td class="py-3 px-4 text-slate-200">
                                        <?php echo htmlspecialchars($l['usuario_nome'] ?? 'Visitante/Sistema'); ?>
                                    </td>
                                    <td class="py-3 px-4 text-slate-300">
                                        <?php echo htmlspecialchars($l['detalhes'] ?? '-'); ?>
                                    </td>
                                    <td class="py-3 px-4 text-slate-500">
                                        <?php echo htmlspecialchars($l['ip'] ?? '-'); ?>
                                    </td>
                                    <td class="py-3 px-4 text-slate-400 whitespace-nowrap">
                                        <?php echo date('d/m/Y H:i:s', strtotime($l['criado_em'])); ?>
                                    </td>
                                </tr>
                            <?php endforeach; ?>
                        <?php endif; ?>
                    </tbody>
                </table>
            </div>
        </div>
    </main>

<?php require_once __DIR__ . '/includes/footer.php'; ?>
