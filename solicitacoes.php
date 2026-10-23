<?php
/**
 * Gerenciamento de Solicitações de Acesso (solicitacoes.php)
 * Sistema: Cerbero
 */

require_once __DIR__ . '/includes/auth_check.php';

$pdo = getConexao();

$filtroStatus = trim($_GET['status'] ?? '');
$busca = trim($_GET['busca'] ?? '');

$sql = "
    SELECT s.*, u.nome AS respondente_nome 
    FROM solicitacoes_acesso s 
    LEFT JOIN usuarios u ON s.respondido_por = u.id 
    WHERE 1=1
";
$params = [];

if (!empty($filtroStatus)) {
    $sql .= " AND s.status = :status";
    $params[':status'] = $filtroStatus;
}

if (!empty($busca)) {
    $sql .= " AND (s.nome LIKE :busca OR s.email LIKE :busca OR s.departamento LIKE :busca)";
    $params[':busca'] = "%{$busca}%";
}

$sql .= " ORDER BY (s.status = 'Pendente') DESC, s.data_solicitacao DESC";

$stmt = $pdo->prepare($sql);
$stmt->execute($params);
$solicitacoes = $stmt->fetchAll();

// Contadores para as abas
$contPendente = (int)$pdo->query("SELECT COUNT(*) FROM solicitacoes_acesso WHERE status = 'Pendente'")->fetchColumn();
$contAprovado = (int)$pdo->query("SELECT COUNT(*) FROM solicitacoes_acesso WHERE status = 'Aprovado'")->fetchColumn();
$contRejeitado = (int)$pdo->query("SELECT COUNT(*) FROM solicitacoes_acesso WHERE status = 'Rejeitado'")->fetchColumn();

$pageTitle = "Solicitações de Acesso";
require_once __DIR__ . '/includes/header.php';
require_once __DIR__ . '/includes/sidebar.php';
?>

<div class="flex-1 flex flex-col min-w-0 overflow-y-auto">
    <?php require_once __DIR__ . '/includes/navbar.php'; ?>

    <main class="flex-1 p-4 md:p-8 space-y-6 max-w-7xl w-full mx-auto">
        <!-- Cabeçalho -->
        <div class="flex flex-col sm:flex-row sm:items-center justify-between gap-4">
            <div>
                <h1 class="text-2xl font-extrabold text-white tracking-tight">Solicitações de Acesso</h1>
                <p class="text-xs text-slate-400">Controle e aprovação de novos acessos à plataforma KarHub / Cerbero</p>
            </div>
            
            <?php if ($contPendente > 0): ?>
                <div class="flex items-center space-x-2 px-3.5 py-2 rounded-xl bg-amber-500/10 border border-amber-500/20 text-amber-300 text-xs font-semibold self-start sm:self-auto">
                    <span class="w-2 h-2 rounded-full bg-amber-400 animate-ping"></span>
                    <span><?php echo $contPendente; ?> solicitação(ões) aguardando análise</span>
                </div>
            <?php endif; ?>
        </div>

        <!-- Abas Rápidas de Status -->
        <div class="flex flex-wrap items-center gap-2 border-b border-slate-800 pb-3">
            <a href="solicitacoes.php" class="px-3.5 py-1.5 rounded-xl text-xs font-semibold transition <?php echo empty($filtroStatus) ? 'bg-indigo-600 text-white' : 'text-slate-400 hover:text-white bg-slate-800/60'; ?>">
                Todas (<?php echo ($contPendente + $contAprovado + $contRejeitado); ?>)
            </a>
            <a href="solicitacoes.php?status=Pendente" class="px-3.5 py-1.5 rounded-xl text-xs font-semibold transition <?php echo $filtroStatus === 'Pendente' ? 'bg-amber-600 text-white' : 'text-slate-400 hover:text-white bg-slate-800/60'; ?>">
                Pendentes (<?php echo $contPendente; ?>)
            </a>
            <a href="solicitacoes.php?status=Aprovado" class="px-3.5 py-1.5 rounded-xl text-xs font-semibold transition <?php echo $filtroStatus === 'Aprovado' ? 'bg-emerald-600 text-white' : 'text-slate-400 hover:text-white bg-slate-800/60'; ?>">
                Aprovadas (<?php echo $contAprovado; ?>)
            </a>
            <a href="solicitacoes.php?status=Rejeitado" class="px-3.5 py-1.5 rounded-xl text-xs font-semibold transition <?php echo $filtroStatus === 'Rejeitado' ? 'bg-rose-600 text-white' : 'text-slate-400 hover:text-white bg-slate-800/60'; ?>">
                Rejeitadas (<?php echo $contRejeitado; ?>)
            </a>
        </div>

        <!-- Tabela de Solicitações -->
        <div class="glass-card rounded-2xl border border-slate-800 shadow-xl overflow-hidden">
            <div class="overflow-x-auto">
                <table class="w-full text-left text-sm">
                    <thead>
                        <tr class="bg-slate-900/90 text-slate-400 text-xs uppercase tracking-wider border-b border-slate-800">
                            <th class="py-3.5 px-4 font-semibold">Solicitante</th>
                            <th class="py-3.5 px-4 font-semibold">Cargo / Setor</th>
                            <th class="py-3.5 px-4 font-semibold">Nível Desejado</th>
                            <th class="py-3.5 px-4 font-semibold">Justificativa</th>
                            <th class="py-3.5 px-4 font-semibold">Status</th>
                            <th class="py-3.5 px-4 font-semibold">Data</th>
                            <th class="py-3.5 px-4 font-semibold text-right">Ação</th>
                        </tr>
                    </thead>
                    <tbody class="divide-y divide-slate-800/60">
                        <?php if (empty($solicitacoes)): ?>
                            <tr>
                                <td colspan="7" class="py-10 text-center text-slate-500">
                                    Nenhuma solicitação encontrada nesta categoria.
                                </td>
                            </tr>
                        <?php else: ?>
                            <?php foreach ($solicitacoes as $s): ?>
                                <tr class="hover:bg-slate-800/40 transition">
                                    <td class="py-3.5 px-4">
                                        <div class="font-medium text-white"><?php echo htmlspecialchars($s['nome']); ?></div>
                                        <div class="text-xs text-slate-400"><?php echo htmlspecialchars($s['email']); ?></div>
                                    </td>
                                    <td class="py-3.5 px-4">
                                        <div class="text-xs text-slate-300 font-medium"><?php echo htmlspecialchars($s['cargo']); ?></div>
                                        <div class="text-[11px] text-slate-500"><?php echo htmlspecialchars($s['departamento']); ?></div>
                                    </td>
                                    <td class="py-3.5 px-4">
                                        <span class="inline-flex items-center px-2 py-0.5 rounded text-xs font-semibold bg-indigo-500/15 text-indigo-300 border border-indigo-500/30">
                                            <?php echo htmlspecialchars($s['nivel_solicitado']); ?>
                                        </span>
                                    </td>
                                    <td class="py-3.5 px-4 max-w-xs">
                                        <p class="text-xs text-slate-300 truncate" title="<?php echo htmlspecialchars($s['motivo']); ?>">
                                            <?php echo htmlspecialchars($s['motivo']); ?>
                                        </p>
                                    </td>
                                    <td class="py-3.5 px-4">
                                        <?php if ($s['status'] === 'Pendente'): ?>
                                            <span class="inline-flex items-center space-x-1 px-2.5 py-0.5 rounded-full text-xs font-semibold bg-amber-500/10 text-amber-300 border border-amber-500/20">
                                                <span class="w-1.5 h-1.5 rounded-full bg-amber-400"></span>
                                                <span>Pendente</span>
                                            </span>
                                        <?php elseif ($s['status'] === 'Aprovado'): ?>
                                            <span class="inline-flex items-center space-x-1 px-2.5 py-0.5 rounded-full text-xs font-semibold bg-emerald-500/10 text-emerald-300 border border-emerald-500/20">
                                                <span class="w-1.5 h-1.5 rounded-full bg-emerald-400"></span>
                                                <span>Aprovado</span>
                                            </span>
                                        <?php else: ?>
                                            <span class="inline-flex items-center space-x-1 px-2.5 py-0.5 rounded-full text-xs font-semibold bg-rose-500/10 text-rose-300 border border-rose-500/20">
                                                <span class="w-1.5 h-1.5 rounded-full bg-rose-400"></span>
                                                <span>Rejeitado</span>
                                            </span>
                                        <?php endif; ?>
                                    </td>
                                    <td class="py-3.5 px-4 text-xs text-slate-400 whitespace-nowrap">
                                        <?php echo date('d/m/Y H:i', strtotime($s['data_solicitacao'])); ?>
                                    </td>
                                    <td class="py-3.5 px-4 text-right space-x-1 whitespace-nowrap">
                                        <?php if ($s['status'] === 'Pendente'): ?>
                                            <button type="button" onclick="abrirModalAprovar(<?php echo $s['id']; ?>, '<?php echo htmlspecialchars(addslashes($s['nome'])); ?>', '<?php echo $s['nivel_solicitado']; ?>')" class="px-3 py-1 text-xs font-semibold rounded-lg bg-emerald-600 hover:bg-emerald-500 text-white shadow-sm transition">
                                                Aprovar
                                            </button>
                                            <button type="button" onclick="abrirModalRejeitar(<?php echo $s['id']; ?>, '<?php echo htmlspecialchars(addslashes($s['nome'])); ?>')" class="px-3 py-1 text-xs font-semibold rounded-lg bg-rose-600/20 hover:bg-rose-600 text-rose-300 hover:text-white border border-rose-500/30 transition">
                                                Rejeitar
                                            </button>
                                        <?php else: ?>
                                            <button type="button" onclick="visualizarDetalhes(<?php echo htmlspecialchars(json_encode($s)); ?>)" class="px-2.5 py-1 text-xs text-slate-400 hover:text-white hover:bg-slate-800 rounded-lg transition">
                                                Ver Detalhes
                                            </button>
                                        <?php endif; ?>
                                    </td>
                                </tr>
                            <?php endforeach; ?>
                        <?php endif; ?>
                    </tbody>
                </table>
            </div>
        </div>
    </main>

    <!-- MODAL APROVAR -->
    <div id="modal-aprovar-solicitacao" class="modal-overlay fixed inset-0 z-50 flex items-center justify-center p-4 bg-slate-950/80 backdrop-blur-sm hidden transition-opacity">
        <div class="modal-box bg-slate-900 border border-slate-700/80 rounded-2xl max-w-md w-full p-6 shadow-2xl transform scale-95 opacity-0 transition-all text-slate-100">
            <div class="flex items-center space-x-3 mb-4">
                <div class="w-10 h-10 rounded-xl bg-emerald-500/20 text-emerald-400 flex items-center justify-center border border-emerald-500/30">
                    <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M5 13l4 4L19 7"/></svg>
                </div>
                <div>
                    <h3 class="text-lg font-bold text-white">Aprovar Solicitação</h3>
                    <p class="text-xs text-slate-400" id="aprovar-subtitulo">Liberar acesso do colaborador</p>
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
                    <textarea name="observacao" rows="2" class="w-full bg-slate-950 border border-slate-700 rounded-xl px-3.5 py-2 text-sm text-white focus:outline-none focus:border-indigo-500" placeholder="Ex: Solicitação aprovada conforme demanda operacional."></textarea>
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

    <!-- MODAL REJEITAR -->
    <div id="modal-rejeitar-solicitacao" class="modal-overlay fixed inset-0 z-50 flex items-center justify-center p-4 bg-slate-950/80 backdrop-blur-sm hidden transition-opacity">
        <div class="modal-box bg-slate-900 border border-slate-700/80 rounded-2xl max-w-md w-full p-6 shadow-2xl transform scale-95 opacity-0 transition-all text-slate-100">
            <div class="flex items-center space-x-3 mb-4">
                <div class="w-10 h-10 rounded-xl bg-rose-500/20 text-rose-400 flex items-center justify-center border border-rose-500/30">
                    <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M6 18L18 6M6 6l12 12"/></svg>
                </div>
                <div>
                    <h3 class="text-lg font-bold text-white">Rejeitar Solicitação</h3>
                    <p class="text-xs text-slate-400" id="rejeitar-subtitulo">Registrar recusa</p>
                </div>
            </div>

            <form action="actions/solicitacao_action.php" method="POST" class="space-y-4">
                <input type="hidden" name="action" value="rejeitar">
                <input type="hidden" name="id" id="rejeitar-id" value="">

                <div>
                    <label class="block text-xs font-semibold text-slate-300 uppercase mb-1">Motivo da Recusa</label>
                    <textarea name="motivo_rejeicao" required rows="3" class="w-full bg-slate-950 border border-slate-700 rounded-xl px-3.5 py-2 text-sm text-white focus:outline-none focus:border-rose-500" placeholder="Informe o motivo da recusa..."></textarea>
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

    <!-- MODAL DETALHES -->
    <div id="modal-detalhes-solicitacao" class="modal-overlay fixed inset-0 z-50 flex items-center justify-center p-4 bg-slate-950/80 backdrop-blur-sm hidden transition-opacity">
        <div class="modal-box bg-slate-900 border border-slate-700/80 rounded-2xl max-w-md w-full p-6 shadow-2xl transform scale-95 opacity-0 transition-all text-slate-100 space-y-4">
            <div class="flex items-center justify-between border-b border-slate-800 pb-3">
                <h3 class="text-lg font-bold text-white" id="det-nome"></h3>
                <button type="button" onclick="closeModal('modal-detalhes-solicitacao')" class="text-slate-400 hover:text-white">
                    <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M6 18L18 6M6 6l12 12"/></svg>
                </button>
            </div>

            <div class="space-y-2 text-xs">
                <p><span class="text-slate-400">E-mail:</span> <strong id="det-email" class="text-white"></strong></p>
                <p><span class="text-slate-400">Cargo / Depto:</span> <span id="det-cargo" class="text-slate-200"></span></p>
                <p><span class="text-slate-400">Nível Solicitado:</span> <span id="det-nivel" class="text-indigo-400 font-semibold"></span></p>
                <p><span class="text-slate-400">Justificativa:</span> <span id="det-motivo" class="text-slate-300 block bg-slate-950 p-2.5 rounded-lg mt-1 border border-slate-800"></span></p>
                <p><span class="text-slate-400">Parecer / Resposta:</span> <span id="det-resposta" class="text-slate-300 block bg-slate-950 p-2.5 rounded-lg mt-1 border border-slate-800"></span></p>
                <p><span class="text-slate-400">Respondido Por:</span> <span id="det-respondente" class="text-white"></span></p>
            </div>

            <div class="flex justify-end pt-2">
                <button type="button" onclick="closeModal('modal-detalhes-solicitacao')" class="px-4 py-2 text-sm font-semibold rounded-xl text-white bg-slate-800 hover:bg-slate-700 transition">
                    Fechar
                </button>
            </div>
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

        function visualizarDetalhes(s) {
            document.getElementById('det-nome').textContent = s.nome;
            document.getElementById('det-email').textContent = s.email;
            document.getElementById('det-cargo').textContent = `${s.cargo} (${s.departamento})`;
            document.getElementById('det-nivel').textContent = s.nivel_solicitado;
            document.getElementById('det-motivo').textContent = s.motivo || 'Nenhum motivo informado.';
            document.getElementById('det-resposta').textContent = s.observacao_resposta || 'Sem observações adicionais.';
            document.getElementById('det-respondente').textContent = s.respondente_nome ? `${s.respondente_nome} em ${s.data_resposta}` : 'Ainda não avaliado';
            openModal('modal-detalhes-solicitacao');
        }
    </script>

<?php require_once __DIR__ . '/includes/footer.php'; ?>
