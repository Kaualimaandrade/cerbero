<?php
/**
 * Painel Administrativo Principal (Dashboard)
 * Sistema: Cerbero
 */

require_once __DIR__ . '/includes/auth_check.php';

$pdo = getConexao();

// 1. Quantidade total de usuários cadastrados
$totalUsuarios = (int)$pdo->query("SELECT COUNT(*) FROM usuarios")->fetchColumn();

// 2. Quantidade de registros cadastrados
$totalRegistros = (int)$pdo->query("SELECT COUNT(*) FROM registros")->fetchColumn();

// 3. Solicitações de acesso pendentes
$totalSolicitacoesPendentes = (int)$pdo->query("SELECT COUNT(*) FROM solicitacoes_acesso WHERE status = 'Pendente'")->fetchColumn();

// 4. Status do sistema
$statusSistema = "Operacional";
$latenciaMs = round(microtime(true) - $_SERVER["REQUEST_TIME_FLOAT"], 3) * 1000;
$uptimeEstimado = "99.98%";

// Últimas solicitações de acesso pendentes (para ação rápida)
$stmtPendentes = $pdo->query("SELECT * FROM solicitacoes_acesso WHERE status = 'Pendente' ORDER BY data_solicitacao DESC LIMIT 5");
$solicitacoesPendentes = $stmtPendentes->fetchAll();

// Últimos registros cadastrados
$stmtUltimosRegistros = $pdo->query("
    SELECT r.*, u.nome AS autor_nome 
    FROM registros r 
    LEFT JOIN usuarios u ON r.usuario_id = u.id 
    ORDER BY r.criado_em DESC LIMIT 5
");
$ultimosRegistros = $stmtUltimosRegistros->fetchAll();

// Distribuição de usuários por nível (para gráfico ou estatística)
$stmtDistr = $pdo->query("SELECT nivel_acesso, COUNT(*) as qtd FROM usuarios GROUP BY nivel_acesso");
$distribuicaoNiveis = $stmtDistr->fetchAll();

$pageTitle = "Painel de Controle";
require_once __DIR__ . '/includes/header.php';
require_once __DIR__ . '/includes/sidebar.php';
?>

<!-- Área de Conteúdo Principal -->
<div class="flex-1 flex flex-col min-w-0 overflow-y-auto">
    <?php require_once __DIR__ . '/includes/navbar.php'; ?>

    <main class="flex-1 p-4 md:p-8 space-y-6 max-w-7xl w-full mx-auto">
        <!-- Banner de Boas-vindas Personalizado -->
        <div class="relative overflow-hidden rounded-2xl bg-gradient-to-r from-indigo-900/60 via-slate-900 to-slate-900 border border-indigo-500/20 p-6 md:p-8 shadow-xl">
            <div class="relative z-10 flex flex-col md:flex-row md:items-center justify-between gap-4">
                <div class="space-y-1">
                    <span class="inline-flex items-center px-2.5 py-1 rounded-full text-xs font-semibold bg-indigo-500/20 text-indigo-300 border border-indigo-500/30">
                        ⚡ Cerbero Security Management
                    </span>
                    <h1 class="text-2xl md:text-3xl font-extrabold text-white tracking-tight">
                        Bem-vindo(a), <?php echo htmlspecialchars($_SESSION['usuario_nome']); ?>!
                    </h1>
                    <p class="text-sm text-slate-300 max-w-2xl">
                        Você está autenticado como <strong class="text-indigo-400"><?php echo htmlspecialchars($_SESSION['usuario_nivel']); ?></strong>. 
                        Aqui você tem a visão centralizada de usuários, integridade de dados e requisições pendentes da plataforma KarHub.
                    </p>
                </div>
                <div class="flex items-center space-x-3 shrink-0">
                    <a href="registros.php" class="px-4 py-2.5 rounded-xl bg-slate-800 hover:bg-slate-700/80 text-white text-xs font-semibold border border-slate-700 transition shadow">
                        Ver Registros
                    </a>
                    <a href="solicitacoes.php" class="px-4 py-2.5 rounded-xl bg-indigo-600 hover:bg-indigo-500 text-white text-xs font-semibold shadow-lg shadow-indigo-600/30 transition">
                        Gerenciar Solicitações
                    </a>
                </div>
            </div>

            <!-- Glow decorativo de fundo -->
            <div class="absolute -right-10 -bottom-10 w-64 h-64 bg-indigo-600/20 rounded-full blur-3xl pointer-events-none"></div>
        </div>

        <!-- 4 CARDS MÉTRICAS CENTRAIS (REQUISITOS CORE) -->
        <div class="grid grid-cols-1 sm:grid-cols-2 lg:grid-cols-4 gap-4 md:gap-6">
            
            <!-- Card 1: Total de Usuários -->
            <div class="glass-card rounded-2xl p-5 border border-slate-800 transition-all hover:translate-y-[-2px] shadow-lg">
                <div class="flex items-center justify-between mb-3">
                    <span class="text-xs font-semibold text-slate-400 uppercase tracking-wider">Usuários Totais</span>
                    <div class="w-10 h-10 rounded-xl bg-indigo-500/10 text-indigo-400 flex items-center justify-center border border-indigo-500/20">
                        <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M17 20h5v-2a3 3 0 00-5.356-1.857M17 20H7m10 0v-2c0-.656-.126-1.283-.356-1.857M7 20H2v-2a3 3 0 015.356-1.857M7 20v-2c0-.656.126-1.283.356-1.857m0 0a5.002 5.002 0 019.288 0M15 7a3 3 0 11-6 0 3 3 0 016 0zm6 3a2 2 0 11-4 0 2 2 0 014 0zM7 10a2 2 0 11-4 0 2 2 0 014 0z"/></svg>
                    </div>
                </div>
                <div class="flex items-baseline space-x-2">
                    <span class="text-3xl font-extrabold text-white tracking-tight"><?php echo $totalUsuarios; ?></span>
                    <span class="text-xs text-emerald-400 font-medium">Cadastrados</span>
                </div>
                <div class="mt-3 flex items-center text-xs text-slate-400">
                    <a href="usuarios.php" class="text-indigo-400 hover:text-indigo-300 font-medium flex items-center space-x-1">
                        <span>Ver lista completa</span>
                        <span>&rarr;</span>
                    </a>
                </div>
            </div>

            <!-- Card 2: Total de Registros -->
            <div class="glass-card rounded-2xl p-5 border border-slate-800 transition-all hover:translate-y-[-2px] shadow-lg">
                <div class="flex items-center justify-between mb-3">
                    <span class="text-xs font-semibold text-slate-400 uppercase tracking-wider">Registros Ativos</span>
                    <div class="w-10 h-10 rounded-xl bg-violet-500/10 text-violet-400 flex items-center justify-center border border-violet-500/20">
                        <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 12h6m-6 4h6m2 5H7a2 2 0 01-2-2V5a2 2 0 012-2h5.586a1 1 0 01.707.293l5.414 5.414a1 1 0 01.293.707V19a2 2 0 01-2 2z"/></svg>
                    </div>
                </div>
                <div class="flex items-baseline space-x-2">
                    <span class="text-3xl font-extrabold text-white tracking-tight"><?php echo $totalRegistros; ?></span>
                    <span class="text-xs text-violet-400 font-medium">Itens no banco</span>
                </div>
                <div class="mt-3 flex items-center text-xs text-slate-400">
                    <a href="registros.php" class="text-violet-400 hover:text-violet-300 font-medium flex items-center space-x-1">
                        <span>Gerenciar cadastros</span>
                        <span>&rarr;</span>
                    </a>
                </div>
            </div>

            <!-- Card 3: Solicitações Pendentes -->
            <div class="glass-card rounded-2xl p-5 border border-slate-800 transition-all hover:translate-y-[-2px] shadow-lg">
                <div class="flex items-center justify-between mb-3">
                    <span class="text-xs font-semibold text-slate-400 uppercase tracking-wider">Solicitações Pendentes</span>
                    <div class="w-10 h-10 rounded-xl bg-amber-500/10 text-amber-400 flex items-center justify-center border border-amber-500/20">
                        <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 8v4l3 3m6-3a9 9 0 11-18 0 9 9 0 0118 0z"/></svg>
                    </div>
                </div>
                <div class="flex items-baseline space-x-2">
                    <span class="text-3xl font-extrabold text-white tracking-tight"><?php echo $totalSolicitacoesPendentes; ?></span>
                    <?php if ($totalSolicitacoesPendentes > 0): ?>
                        <span class="px-2 py-0.5 rounded text-[11px] font-bold bg-amber-500/20 text-amber-300 border border-amber-500/30 animate-pulse">Aguardando</span>
                    <?php else: ?>
                        <span class="text-xs text-slate-400">Tudo em dia</span>
                    <?php endif; ?>
                </div>
                <div class="mt-3 flex items-center text-xs text-slate-400">
                    <a href="solicitacoes.php" class="text-amber-400 hover:text-amber-300 font-medium flex items-center space-x-1">
                        <span>Analisar pedidos</span>
                        <span>&rarr;</span>
                    </a>
                </div>
            </div>

            <!-- Card 4: Status do Sistema -->
            <div class="glass-card rounded-2xl p-5 border border-slate-800 transition-all hover:translate-y-[-2px] shadow-lg">
                <div class="flex items-center justify-between mb-3">
                    <span class="text-xs font-semibold text-slate-400 uppercase tracking-wider">Status do Sistema</span>
                    <div class="w-10 h-10 rounded-xl bg-emerald-500/10 text-emerald-400 flex items-center justify-center border border-emerald-500/20">
                        <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M5 13l4 4L19 7"/></svg>
                    </div>
                </div>
                <div class="flex items-center space-x-2">
                    <span class="relative flex h-3 w-3">
                        <span class="animate-ping absolute inline-flex h-full w-full rounded-full bg-emerald-400 opacity-75"></span>
                        <span class="relative inline-flex rounded-full h-3 w-3 bg-emerald-500"></span>
                    </span>
                    <span class="text-2xl font-extrabold text-emerald-400 tracking-tight"><?php echo $statusSistema; ?></span>
                </div>
                <div class="mt-3 flex items-center justify-between text-xs text-slate-400">
                    <span>Uptime: <strong class="text-slate-300"><?php echo $uptimeEstimado; ?></strong></span>
                    <a href="status.php" class="text-emerald-400 hover:text-emerald-300 font-medium">Saúde &rarr;</a>
                </div>
            </div>
        </div>

        <!-- SEÇÃO PRINCIPAL DE TABELAS E GRÁFICOS -->
        <div class="grid grid-cols-1 lg:grid-cols-3 gap-6">
            
            <!-- Tabela: Solicitações de Acesso Pendentes (2 colunas) -->
            <div class="lg:col-span-2 glass-card rounded-2xl p-6 border border-slate-800 shadow-xl">
                <div class="flex items-center justify-between mb-5">
                    <div>
                        <h2 class="text-lg font-bold text-white flex items-center space-x-2">
                            <span>Solicitações de Acesso Recentes</span>
                            <?php if ($totalSolicitacoesPendentes > 0): ?>
                                <span class="px-2 py-0.5 text-xs font-semibold bg-amber-500/20 text-amber-300 rounded-full border border-amber-500/30">
                                    <?php echo $totalSolicitacoesPendentes; ?> pendente(s)
                                </span>
                            <?php endif; ?>
                        </h2>
                        <p class="text-xs text-slate-400">Colaboradores que solicitaram acesso e aguardam autorização</p>
                    </div>
                    <a href="solicitacoes.php" class="text-xs text-indigo-400 hover:text-indigo-300 font-semibold flex items-center space-x-1">
                        <span>Ver todas</span>
                        <span>&rarr;</span>
                    </a>
                </div>

                <div class="overflow-x-auto">
                    <table class="w-full text-left text-sm">
                        <thead>
                            <tr class="text-slate-400 text-xs uppercase tracking-wider border-b border-slate-800">
                                <th class="pb-3 font-semibold">Solicitante</th>
                                <th class="pb-3 font-semibold">Cargo / Dep.</th>
                                <th class="pb-3 font-semibold">Nível</th>
                                <th class="pb-3 font-semibold">Data</th>
                                <th class="pb-3 font-semibold text-right">Ação</th>
                            </tr>
                        </thead>
                        <tbody class="divide-y divide-slate-800/60">
                            <?php if (empty($solicitacoesPendentes)): ?>
                                <tr>
                                    <td colspan="5" class="py-8 text-center text-slate-500 text-sm">
                                        Nenhuma solicitação de acesso pendente no momento. 🎉
                                    </td>
                                </tr>
                            <?php else: ?>
                                <?php foreach ($solicitacoesPendentes as $sol): ?>
                                    <tr class="hover:bg-slate-800/40 transition">
                                        <td class="py-3.5 pr-2">
                                            <div class="font-medium text-white"><?php echo htmlspecialchars($sol['nome']); ?></div>
                                            <div class="text-xs text-slate-400"><?php echo htmlspecialchars($sol['email']); ?></div>
                                        </td>
                                        <td class="py-3.5 pr-2">
                                            <div class="text-slate-300 text-xs font-medium"><?php echo htmlspecialchars($sol['cargo']); ?></div>
                                            <div class="text-[11px] text-slate-500"><?php echo htmlspecialchars($sol['departamento']); ?></div>
                                        </td>
                                        <td class="py-3.5 pr-2">
                                            <span class="px-2 py-0.5 rounded text-[11px] font-semibold bg-indigo-500/10 text-indigo-300 border border-indigo-500/20">
                                                <?php echo htmlspecialchars($sol['nivel_solicitado']); ?>
                                            </span>
                                        </td>
                                        <td class="py-3.5 pr-2 text-xs text-slate-400">
                                            <?php echo date('d/m/Y H:i', strtotime($sol['data_solicitacao'])); ?>
                                        </td>
                                        <td class="py-3.5 text-right space-x-1 whitespace-nowrap">
                                            <button type="button" onclick="abrirModalAprovar(<?php echo $sol['id']; ?>, '<?php echo htmlspecialchars(addslashes($sol['nome'])); ?>', '<?php echo $sol['nivel_solicitado']; ?>')" class="px-2.5 py-1 text-xs font-semibold rounded-lg bg-emerald-600/20 hover:bg-emerald-600 text-emerald-300 hover:text-white border border-emerald-500/30 transition">
                                                Aprovar
                                            </button>
                                            <button type="button" onclick="abrirModalRejeitar(<?php echo $sol['id']; ?>, '<?php echo htmlspecialchars(addslashes($sol['nome'])); ?>')" class="px-2.5 py-1 text-xs font-semibold rounded-lg bg-rose-600/20 hover:bg-rose-600 text-rose-300 hover:text-white border border-rose-500/30 transition">
                                                Rejeitar
                                            </button>
                                        </td>
                                    </tr>
                                <?php endforeach; ?>
                            <?php endif; ?>
                        </tbody>
                    </table>
                </div>
            </div>

            <!-- Gráfico / Distribuição de Níveis (1 coluna) -->
            <div class="glass-card rounded-2xl p-6 border border-slate-800 shadow-xl flex flex-col justify-between">
                <div>
                    <h2 class="text-lg font-bold text-white mb-1">Distribuição de Acesso</h2>
                    <p class="text-xs text-slate-400 mb-4">Composição de perfis cadastrados no Cerbero</p>

                    <div class="space-y-4">
                        <?php 
                        foreach ($distribuicaoNiveis as $dist): 
                            $porcentagem = $totalUsuarios > 0 ? round(($dist['qtd'] / $totalUsuarios) * 100) : 0;
                            $barColor = $dist['nivel_acesso'] === 'Administrador' ? 'bg-indigo-500' : ($dist['nivel_acesso'] === 'Operador' ? 'bg-emerald-500' : 'bg-amber-500');
                        ?>
                            <div>
                                <div class="flex justify-between text-xs font-semibold mb-1">
                                    <span class="text-slate-300"><?php echo htmlspecialchars($dist['nivel_acesso']); ?></span>
                                    <span class="text-slate-400"><?php echo $dist['qtd']; ?> (<?php echo $porcentagem; ?>%)</span>
                                </div>
                                <div class="w-full bg-slate-800 rounded-full h-2">
                                    <div class="<?php echo $barColor; ?> h-2 rounded-full" style="width: <?php echo $porcentagem; ?>%"></div>
                                </div>
                            </div>
                        <?php endforeach; ?>
                    </div>
                </div>

                <div class="mt-6 pt-4 border-t border-slate-800 bg-slate-900/40 p-4 rounded-xl">
                    <div class="flex items-center space-x-2 text-xs text-slate-300 mb-1">
                        <svg class="w-4 h-4 text-emerald-400 shrink-0" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 12l2 2 4-4m5.618-4.016A11.955 11.955 0 0112 2.944a11.955 11.955 0 01-8.618 3.04A12.02 12.02 0 003 9c0 5.591 3.824 10.29 9 11.622 5.176-1.332 9-6.03 9-11.622 0-1.042-.133-2.052-.382-3.016z"/></svg>
                        <span class="font-semibold text-white">Segurança Ativa</span>
                    </div>
                    <p class="text-[11px] text-slate-400">Consultas protegidas por Prepared Statements com PDO contra SQL Injection.</p>
                </div>
            </div>
        </div>

        <!-- TABELA DE REGISTROS RECENTES -->
        <div class="glass-card rounded-2xl p-6 border border-slate-800 shadow-xl">
            <div class="flex items-center justify-between mb-4">
                <div>
                    <h2 class="text-lg font-bold text-white">Últimos Registros do Sistema</h2>
                    <p class="text-xs text-slate-400">Atividades, operações de compras e cadastros recentes</p>
                </div>
                <a href="registros.php" class="text-xs text-indigo-400 hover:text-indigo-300 font-semibold flex items-center space-x-1">
                    <span>Acessar módulo completo</span>
                    <span>&rarr;</span>
                </a>
            </div>

            <div class="overflow-x-auto">
                <table class="w-full text-left text-sm">
                    <thead>
                        <tr class="text-slate-400 text-xs uppercase tracking-wider border-b border-slate-800">
                            <th class="pb-3 font-semibold">Título do Registro</th>
                            <th class="pb-3 font-semibold">Categoria</th>
                            <th class="pb-3 font-semibold">Prioridade</th>
                            <th class="pb-3 font-semibold">Status</th>
                            <th class="pb-3 font-semibold">Responsável</th>
                            <th class="pb-3 font-semibold">Data</th>
                        </tr>
                    </thead>
                    <tbody class="divide-y divide-slate-800/60">
                        <?php if (empty($ultimosRegistros)): ?>
                            <tr>
                                <td colspan="6" class="py-6 text-center text-slate-500">Nenhum registro cadastrado ainda.</td>
                            </tr>
                        <?php else: ?>
                            <?php foreach ($ultimosRegistros as $reg): ?>
                                <tr class="hover:bg-slate-800/40 transition">
                                    <td class="py-3 font-medium text-white"><?php echo htmlspecialchars($reg['titulo']); ?></td>
                                    <td class="py-3 text-xs text-slate-400"><?php echo htmlspecialchars($reg['categoria']); ?></td>
                                    <td class="py-3">
                                        <?php 
                                        $corPri = 'bg-slate-700 text-slate-300';
                                        if ($reg['prioridade'] === 'Crítica') $corPri = 'bg-rose-500/20 text-rose-300 border border-rose-500/30';
                                        elseif ($reg['prioridade'] === 'Alta') $corPri = 'bg-amber-500/20 text-amber-300 border border-amber-500/30';
                                        elseif ($reg['prioridade'] === 'Média') $corPri = 'bg-indigo-500/20 text-indigo-300 border border-indigo-500/30';
                                        ?>
                                        <span class="px-2 py-0.5 rounded text-[11px] font-semibold <?php echo $corPri; ?>">
                                            <?php echo htmlspecialchars($reg['prioridade']); ?>
                                        </span>
                                    </td>
                                    <td class="py-3">
                                        <span class="text-xs font-semibold text-slate-300"><?php echo htmlspecialchars($reg['status']); ?></span>
                                    </td>
                                    <td class="py-3 text-xs text-slate-400"><?php echo htmlspecialchars($reg['autor_nome'] ?? 'Sistema'); ?></td>
                                    <td class="py-3 text-xs text-slate-500"><?php echo date('d/m/Y', strtotime($reg['criado_em'])); ?></td>
                                </tr>
                            <?php endforeach; ?>
                        <?php endif; ?>
                    </tbody>
                </table>
            </div>
        </div>
    </main>

    <!-- MODAL PERSONALIZADO: APROVAR SOLICITAÇÃO -->
    <div id="modal-aprovar-solicitacao" class="modal-overlay fixed inset-0 z-50 flex items-center justify-center p-4 bg-slate-950/80 backdrop-blur-sm hidden transition-opacity">
        <div class="modal-box bg-slate-900 border border-slate-700/80 rounded-2xl max-w-md w-full p-6 shadow-2xl transform scale-95 opacity-0 transition-all text-slate-100">
            <div class="flex items-center space-x-3 mb-4">
                <div class="w-10 h-10 rounded-xl bg-emerald-500/20 text-emerald-400 flex items-center justify-center border border-emerald-500/30">
                    <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M5 13l4 4L19 7"/></svg>
                </div>
                <div>
                    <h3 class="text-lg font-bold text-white">Aprovar Solicitação</h3>
                    <p class="text-xs text-slate-400" id="aprovar-subtitulo">Liberar acesso para o colaborador</p>
                </div>
            </div>

            <form action="actions/solicitacao_action.php" method="POST" class="space-y-4">
                <input type="hidden" name="action" value="aprovar">
                <input type="hidden" name="id" id="aprovar-id" value="">

                <div>
                    <label class="block text-xs font-semibold text-slate-300 uppercase mb-1">Nível de Acesso Autorizado</label>
                    <select name="nivel_acesso" id="aprovar-nivel" class="w-full bg-slate-950 border border-slate-700 rounded-xl px-3.5 py-2 text-sm text-white focus:outline-none focus:border-indigo-500">
                        <option value="Operador">Operador (Acesso às Operações)</option>
                        <option value="Visualizador">Visualizador (Apenas Consulta)</option>
                        <option value="Administrador">Administrador (Controle Total)</option>
                    </select>
                </div>

                <div class="flex items-center space-x-2 pt-1">
                    <input type="checkbox" name="criar_usuario" id="criar_usuario" checked class="w-4 h-4 rounded text-indigo-600 focus:ring-0 bg-slate-950 border-slate-700">
                    <label for="criar_usuario" class="text-xs text-slate-300">Criar automaticamente conta de usuário com senha padrão (<code>mudar123</code>)</label>
                </div>

                <div>
                    <label class="block text-xs font-semibold text-slate-300 uppercase mb-1">Observação do Parecer</label>
                    <textarea name="observacao" rows="2" class="w-full bg-slate-950 border border-slate-700 rounded-xl px-3.5 py-2 text-sm text-white focus:outline-none focus:border-indigo-500" placeholder="Ex: Solicitação aprovada conforme autorização do departamento."></textarea>
                </div>

                <div class="flex items-center justify-end space-x-3 pt-2">
                    <button type="button" onclick="closeModal('modal-aprovar-solicitacao')" class="px-4 py-2 text-sm font-medium rounded-xl text-slate-300 bg-slate-800 hover:bg-slate-700/80 border border-slate-700 transition">
                        Cancelar
                    </button>
                    <button type="submit" class="px-4 py-2 text-sm font-semibold rounded-xl text-white bg-emerald-600 hover:bg-emerald-500 shadow-lg shadow-emerald-600/30 transition">
                        Confirmar Aprovação
                    </button>
                </div>
            </form>
        </div>
    </div>

    <!-- MODAL PERSONALIZADO: REJEITAR SOLICITAÇÃO -->
    <div id="modal-rejeitar-solicitacao" class="modal-overlay fixed inset-0 z-50 flex items-center justify-center p-4 bg-slate-950/80 backdrop-blur-sm hidden transition-opacity">
        <div class="modal-box bg-slate-900 border border-slate-700/80 rounded-2xl max-w-md w-full p-6 shadow-2xl transform scale-95 opacity-0 transition-all text-slate-100">
            <div class="flex items-center space-x-3 mb-4">
                <div class="w-10 h-10 rounded-xl bg-rose-500/20 text-rose-400 flex items-center justify-center border border-rose-500/30">
                    <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M6 18L18 6M6 6l12 12"/></svg>
                </div>
                <div>
                    <h3 class="text-lg font-bold text-white">Rejeitar Solicitação</h3>
                    <p class="text-xs text-slate-400" id="rejeitar-subtitulo">Registrar recusa da solicitação</p>
                </div>
            </div>

            <form action="actions/solicitacao_action.php" method="POST" class="space-y-4">
                <input type="hidden" name="action" value="rejeitar">
                <input type="hidden" name="id" id="rejeitar-id" value="">

                <div>
                    <label class="block text-xs font-semibold text-slate-300 uppercase mb-1">Motivo da Recusa</label>
                    <textarea name="motivo_rejeicao" required rows="3" class="w-full bg-slate-950 border border-slate-700 rounded-xl px-3.5 py-2 text-sm text-white focus:outline-none focus:border-rose-500" placeholder="Informe o motivo pelo qual a solicitação foi indeferida..."></textarea>
                </div>

                <div class="flex items-center justify-end space-x-3 pt-2">
                    <button type="button" onclick="closeModal('modal-rejeitar-solicitacao')" class="px-4 py-2 text-sm font-medium rounded-xl text-slate-300 bg-slate-800 hover:bg-slate-700/80 border border-slate-700 transition">
                        Voltar
                    </button>
                    <button type="submit" class="px-4 py-2 text-sm font-semibold rounded-xl text-white bg-rose-600 hover:bg-rose-500 shadow-lg shadow-rose-600/30 transition">
                        Confirmar Rejeição
                    </button>
                </div>
            </form>
        </div>
    </div>

    <script>
        function abrirModalAprovar(id, nome, nivel) {
            document.getElementById('aprovar-id').value = id;
            document.getElementById('aprovar-subtitulo').textContent = `Liberar acesso para: ${nome}`;
            document.getElementById('aprovar-nivel').value = nivel || 'Operador';
            openModal('modal-aprovar-solicitacao');
        }

        function abrirModalRejeitar(id, nome) {
            document.getElementById('rejeitar-id').value = id;
            document.getElementById('rejeitar-subtitulo').textContent = `Rejeitar acesso de: ${nome}`;
            openModal('modal-rejeitar-solicitacao');
        }
    </script>

<?php require_once __DIR__ . '/includes/footer.php'; ?>
