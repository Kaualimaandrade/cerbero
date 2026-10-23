<?php
/**
 * Gerenciamento de Usuários (usuarios.php)
 * Sistema: Cerbero
 */

require_once __DIR__ . '/includes/auth_check.php';

$pdo = getConexao();

// Busca e Filtros
$busca = trim($_GET['busca'] ?? '');
$filtroNivel = trim($_GET['nivel'] ?? '');

$sql = "SELECT * FROM usuarios WHERE 1=1";
$params = [];

if (!empty($busca)) {
    $sql .= " AND (nome LIKE :busca OR email LIKE :busca OR cargo LIKE :busca)";
    $params[':busca'] = "%{$busca}%";
}

if (!empty($filtroNivel)) {
    $sql .= " AND nivel_acesso = :nivel";
    $params[':nivel'] = $filtroNivel;
}

$sql .= " ORDER BY id DESC";

$stmt = $pdo->prepare($sql);
$stmt->execute($params);
$usuarios = $stmt->fetchAll();

$pageTitle = "Gestão de Usuários";
require_once __DIR__ . '/includes/header.php';
require_once __DIR__ . '/includes/sidebar.php';
?>

<div class="flex-1 flex flex-col min-w-0 overflow-y-auto">
    <?php require_once __DIR__ . '/includes/navbar.php'; ?>

    <main class="flex-1 p-4 md:p-8 space-y-6 max-w-7xl w-full mx-auto">
        <!-- Cabeçalho da Página -->
        <div class="flex flex-col sm:flex-row sm:items-center justify-between gap-4">
            <div>
                <h1 class="text-2xl font-extrabold text-white tracking-tight">Usuários do Sistema</h1>
                <p class="text-xs text-slate-400">Controle de acessos, privilégios e credenciais do Cerbero</p>
            </div>
            
            <?php if (($_SESSION['usuario_nivel'] ?? '') === 'Administrador'): ?>
                <button type="button" onclick="openModal('modal-novo-usuario')" class="inline-flex items-center space-x-2 px-4 py-2.5 rounded-xl bg-gradient-to-r from-indigo-600 to-violet-600 hover:from-indigo-500 hover:to-violet-500 text-white text-xs font-semibold shadow-lg shadow-indigo-600/30 transition self-start sm:self-auto">
                    <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 4v16m8-8H4"/></svg>
                    <span>Novo Usuário</span>
                </button>
            <?php endif; ?>
        </div>

        <!-- Filtros e Barra de Pesquisa -->
        <div class="glass-card rounded-2xl p-4 border border-slate-800 shadow-md">
            <form method="GET" class="flex flex-col sm:flex-row gap-3">
                <div class="flex-1 relative">
                    <div class="absolute inset-y-0 left-0 pl-3.5 flex items-center pointer-events-none text-slate-400">
                        <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M21 21l-6-6m2-5a7 7 0 11-14 0 7 7 0 0114 0z"/></svg>
                    </div>
                    <input type="text" name="busca" value="<?php echo htmlspecialchars($busca); ?>" placeholder="Buscar por nome, e-mail ou cargo..." class="w-full bg-slate-950/70 border border-slate-700 rounded-xl pl-10 pr-4 py-2 text-xs md:text-sm text-white focus:outline-none focus:border-indigo-500 placeholder-slate-500">
                </div>

                <div class="w-full sm:w-48">
                    <select name="nivel" onchange="this.form.submit()" class="w-full bg-slate-950/70 border border-slate-700 rounded-xl px-3 py-2 text-xs md:text-sm text-white focus:outline-none focus:border-indigo-500">
                        <option value="">Todos os Níveis</option>
                        <option value="Administrador" <?php echo $filtroNivel === 'Administrador' ? 'selected' : ''; ?>>Administrador</option>
                        <option value="Operador" <?php echo $filtroNivel === 'Operador' ? 'selected' : ''; ?>>Operador</option>
                        <option value="Visualizador" <?php echo $filtroNivel === 'Visualizador' ? 'selected' : ''; ?>>Visualizador</option>
                    </select>
                </div>

                <button type="submit" class="px-4 py-2 bg-slate-800 hover:bg-slate-700 text-white rounded-xl text-xs font-semibold border border-slate-700 transition">
                    Filtrar
                </button>
                <?php if (!empty($busca) || !empty($filtroNivel)): ?>
                    <a href="usuarios.php" class="px-3 py-2 bg-rose-500/10 hover:bg-rose-500/20 text-rose-300 rounded-xl text-xs font-semibold border border-rose-500/20 transition flex items-center justify-center">
                        Limpar
                    </a>
                <?php endif; ?>
            </form>
        </div>

        <!-- Tabela de Usuários -->
        <div class="glass-card rounded-2xl border border-slate-800 shadow-xl overflow-hidden">
            <div class="overflow-x-auto">
                <table class="w-full text-left text-sm">
                    <thead>
                        <tr class="bg-slate-900/90 text-slate-400 text-xs uppercase tracking-wider border-b border-slate-800">
                            <th class="py-3.5 px-4 font-semibold">Usuário</th>
                            <th class="py-3.5 px-4 font-semibold">Cargo</th>
                            <th class="py-3.5 px-4 font-semibold">Nível de Acesso</th>
                            <th class="py-3.5 px-4 font-semibold">Status</th>
                            <th class="py-3.5 px-4 font-semibold">Data Cadastro</th>
                            <th class="py-3.5 px-4 font-semibold text-right">Ações</th>
                        </tr>
                    </thead>
                    <tbody class="divide-y divide-slate-800/60">
                        <?php if (empty($usuarios)): ?>
                            <tr>
                                <td colspan="6" class="py-10 text-center text-slate-500">
                                    Nenhum usuário encontrado para os critérios informados.
                                </td>
                            </tr>
                        <?php else: ?>
                            <?php foreach ($usuarios as $u): ?>
                                <tr class="hover:bg-slate-800/40 transition">
                                    <td class="py-3.5 px-4">
                                        <div class="flex items-center space-x-3">
                                            <div class="w-9 h-9 rounded-xl bg-gradient-to-tr from-indigo-600 to-indigo-800 flex items-center justify-center text-white font-bold text-xs shrink-0">
                                                <?php echo strtoupper(substr($u['nome'], 0, 1)); ?>
                                            </div>
                                            <div>
                                                <div class="font-medium text-white"><?php echo htmlspecialchars($u['nome']); ?></div>
                                                <div class="text-xs text-slate-400"><?php echo htmlspecialchars($u['email']); ?></div>
                                            </div>
                                        </div>
                                    </td>
                                    <td class="py-3.5 px-4 text-xs text-slate-300">
                                        <?php echo htmlspecialchars($u['cargo'] ?? 'Colaborador'); ?>
                                    </td>
                                    <td class="py-3.5 px-4">
                                        <?php 
                                        $corNivel = 'bg-slate-700 text-slate-300 border-slate-600';
                                        if ($u['nivel_acesso'] === 'Administrador') $corNivel = 'bg-indigo-500/15 text-indigo-300 border-indigo-500/30';
                                        elseif ($u['nivel_acesso'] === 'Operador') $corNivel = 'bg-emerald-500/15 text-emerald-300 border-emerald-500/30';
                                        elseif ($u['nivel_acesso'] === 'Visualizador') $corNivel = 'bg-amber-500/15 text-amber-300 border-amber-500/30';
                                        ?>
                                        <span class="inline-flex items-center px-2.5 py-0.5 rounded text-xs font-semibold border <?php echo $corNivel; ?>">
                                            <?php echo htmlspecialchars($u['nivel_acesso']); ?>
                                        </span>
                                    </td>
                                    <td class="py-3.5 px-4">
                                        <?php if ($u['status'] === 'Ativo'): ?>
                                            <span class="inline-flex items-center space-x-1.5 px-2 py-0.5 rounded text-xs font-semibold bg-emerald-500/10 text-emerald-400 border border-emerald-500/20">
                                                <span class="w-1.5 h-1.5 rounded-full bg-emerald-400"></span>
                                                <span>Ativo</span>
                                            </span>
                                        <?php else: ?>
                                            <span class="inline-flex items-center space-x-1.5 px-2 py-0.5 rounded text-xs font-semibold bg-rose-500/10 text-rose-400 border border-rose-500/20">
                                                <span class="w-1.5 h-1.5 rounded-full bg-rose-400"></span>
                                                <span>Inativo</span>
                                            </span>
                                        <?php endif; ?>
                                    </td>
                                    <td class="py-3.5 px-4 text-xs text-slate-400">
                                        <?php echo date('d/m/Y H:i', strtotime($u['criado_em'])); ?>
                                    </td>
                                    <td class="py-3.5 px-4 text-right space-x-2 whitespace-nowrap">
                                        <?php if (($_SESSION['usuario_nivel'] ?? '') === 'Administrador'): ?>
                                            <button type="button" onclick="abrirModalEditar(<?php echo htmlspecialchars(json_encode($u)); ?>)" class="p-1.5 rounded-lg text-slate-400 hover:text-white hover:bg-slate-800 transition" title="Editar">
                                                <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M11 5H6a2 2 0 00-2 2v11a2 2 0 002 2h11a2 2 0 002-2v-5m-1.414-9.414a2 2 0 112.828 2.828L11.828 15H9v-2.828l8.586-8.586z"/></svg>
                                            </button>
                                            <?php if ((int)$u['id'] !== (int)$_SESSION['usuario_id']): ?>
                                                <button type="button" onclick="confirmarExcluirUsuario(<?php echo $u['id']; ?>, '<?php echo htmlspecialchars(addslashes($u['nome'])); ?>')" class="p-1.5 rounded-lg text-rose-400 hover:text-rose-300 hover:bg-rose-500/10 transition" title="Excluir">
                                                    <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 7l-.867 12.142A2 2 0 0116.138 21H7.862a2 2 0 01-1.995-1.858L5 7m5 4v6m4-6v6m1-10V4a1 1 0 00-1-1h-4a1 1 0 00-1 1v3M4 7h16"/></svg>
                                                </button>
                                            <?php endif; ?>
                                        <?php else: ?>
                                            <span class="text-xs text-slate-500 italic">Somente leitura</span>
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

    <!-- MODAL: NOVO USUÁRIO -->
    <div id="modal-novo-usuario" class="modal-overlay fixed inset-0 z-50 flex items-center justify-center p-4 bg-slate-950/80 backdrop-blur-sm hidden transition-opacity">
        <div class="modal-box bg-slate-900 border border-slate-700/80 rounded-2xl max-w-md w-full p-6 shadow-2xl transform scale-95 opacity-0 transition-all text-slate-100">
            <div class="flex items-center justify-between mb-4">
                <h3 class="text-lg font-bold text-white flex items-center space-x-2">
                    <span>Novo Usuário</span>
                </h3>
                <button type="button" onclick="closeModal('modal-novo-usuario')" class="text-slate-400 hover:text-white">
                    <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M6 18L18 6M6 6l12 12"/></svg>
                </button>
            </div>

            <form action="actions/usuario_action.php" method="POST" class="space-y-4">
                <input type="hidden" name="action" value="criar">

                <div>
                    <label class="block text-xs font-semibold text-slate-300 uppercase mb-1">Nome Completo</label>
                    <input type="text" name="nome" required placeholder="Ex: Lucas Ferreira" class="w-full bg-slate-950 border border-slate-700 rounded-xl px-3.5 py-2 text-sm text-white focus:outline-none focus:border-indigo-500">
                </div>

                <div>
                    <label class="block text-xs font-semibold text-slate-300 uppercase mb-1">E-mail</label>
                    <input type="email" name="email" required placeholder="lucas@cerbero.com" class="w-full bg-slate-950 border border-slate-700 rounded-xl px-3.5 py-2 text-sm text-white focus:outline-none focus:border-indigo-500">
                </div>

                <div class="grid grid-cols-2 gap-3">
                    <div>
                        <label class="block text-xs font-semibold text-slate-300 uppercase mb-1">Cargo</label>
                        <input type="text" name="cargo" placeholder="Ex: Analista" class="w-full bg-slate-950 border border-slate-700 rounded-xl px-3.5 py-2 text-sm text-white focus:outline-none focus:border-indigo-500">
                    </div>
                    <div>
                        <label class="block text-xs font-semibold text-slate-300 uppercase mb-1">Nível de Acesso</label>
                        <select name="nivel_acesso" class="w-full bg-slate-950 border border-slate-700 rounded-xl px-3 py-2 text-sm text-white focus:outline-none focus:border-indigo-500">
                            <option value="Operador">Operador</option>
                            <option value="Administrador">Administrador</option>
                            <option value="Visualizador">Visualizador</option>
                        </select>
                    </div>
                </div>

                <div class="grid grid-cols-2 gap-3">
                    <div>
                        <label class="block text-xs font-semibold text-slate-300 uppercase mb-1">Status</label>
                        <select name="status" class="w-full bg-slate-950 border border-slate-700 rounded-xl px-3 py-2 text-sm text-white focus:outline-none focus:border-indigo-500">
                            <option value="Ativo">Ativo</option>
                            <option value="Inativo">Inativo</option>
                        </select>
                    </div>
                    <div>
                        <label class="block text-xs font-semibold text-slate-300 uppercase mb-1">Senha Inicial</label>
                        <input type="password" name="senha" required placeholder="Mínimo 6 chars" class="w-full bg-slate-950 border border-slate-700 rounded-xl px-3.5 py-2 text-sm text-white focus:outline-none focus:border-indigo-500">
                    </div>
                </div>

                <div class="flex items-center justify-end space-x-3 pt-3">
                    <button type="button" onclick="closeModal('modal-novo-usuario')" class="px-4 py-2 text-sm font-medium rounded-xl text-slate-300 bg-slate-800 hover:bg-slate-700/80 border border-slate-700 transition">
                        Cancelar
                    </button>
                    <button type="submit" class="px-4 py-2 text-sm font-semibold rounded-xl text-white bg-indigo-600 hover:bg-indigo-500 shadow-lg shadow-indigo-600/30 transition">
                        Cadastrar Usuário
                    </button>
                </div>
            </form>
        </div>
    </div>

    <!-- MODAL: EDITAR USUÁRIO -->
    <div id="modal-editar-usuario" class="modal-overlay fixed inset-0 z-50 flex items-center justify-center p-4 bg-slate-950/80 backdrop-blur-sm hidden transition-opacity">
        <div class="modal-box bg-slate-900 border border-slate-700/80 rounded-2xl max-w-md w-full p-6 shadow-2xl transform scale-95 opacity-0 transition-all text-slate-100">
            <div class="flex items-center justify-between mb-4">
                <h3 class="text-lg font-bold text-white flex items-center space-x-2">
                    <span>Editar Usuário</span>
                </h3>
                <button type="button" onclick="closeModal('modal-editar-usuario')" class="text-slate-400 hover:text-white">
                    <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M6 18L18 6M6 6l12 12"/></svg>
                </button>
            </div>

            <form action="actions/usuario_action.php" method="POST" class="space-y-4">
                <input type="hidden" name="action" value="editar">
                <input type="hidden" name="id" id="edit-id" value="">

                <div>
                    <label class="block text-xs font-semibold text-slate-300 uppercase mb-1">Nome Completo</label>
                    <input type="text" name="nome" id="edit-nome" required class="w-full bg-slate-950 border border-slate-700 rounded-xl px-3.5 py-2 text-sm text-white focus:outline-none focus:border-indigo-500">
                </div>

                <div>
                    <label class="block text-xs font-semibold text-slate-300 uppercase mb-1">E-mail</label>
                    <input type="email" name="email" id="edit-email" required class="w-full bg-slate-950 border border-slate-700 rounded-xl px-3.5 py-2 text-sm text-white focus:outline-none focus:border-indigo-500">
                </div>

                <div class="grid grid-cols-2 gap-3">
                    <div>
                        <label class="block text-xs font-semibold text-slate-300 uppercase mb-1">Cargo</label>
                        <input type="text" name="cargo" id="edit-cargo" class="w-full bg-slate-950 border border-slate-700 rounded-xl px-3.5 py-2 text-sm text-white focus:outline-none focus:border-indigo-500">
                    </div>
                    <div>
                        <label class="block text-xs font-semibold text-slate-300 uppercase mb-1">Nível de Acesso</label>
                        <select name="nivel_acesso" id="edit-nivel" class="w-full bg-slate-950 border border-slate-700 rounded-xl px-3 py-2 text-sm text-white focus:outline-none focus:border-indigo-500">
                            <option value="Operador">Operador</option>
                            <option value="Administrador">Administrador</option>
                            <option value="Visualizador">Visualizador</option>
                        </select>
                    </div>
                </div>

                <div class="grid grid-cols-2 gap-3">
                    <div>
                        <label class="block text-xs font-semibold text-slate-300 uppercase mb-1">Status</label>
                        <select name="status" id="edit-status" class="w-full bg-slate-950 border border-slate-700 rounded-xl px-3 py-2 text-sm text-white focus:outline-none focus:border-indigo-500">
                            <option value="Ativo">Ativo</option>
                            <option value="Inativo">Inativo</option>
                        </select>
                    </div>
                    <div>
                        <label class="block text-xs font-semibold text-slate-300 uppercase mb-1">Nova Senha (Opcional)</label>
                        <input type="password" name="nova_senha" placeholder="Deixe vazio para manter" class="w-full bg-slate-950 border border-slate-700 rounded-xl px-3.5 py-2 text-sm text-white focus:outline-none focus:border-indigo-500">
                    </div>
                </div>

                <div class="flex items-center justify-end space-x-3 pt-3">
                    <button type="button" onclick="closeModal('modal-editar-usuario')" class="px-4 py-2 text-sm font-medium rounded-xl text-slate-300 bg-slate-800 hover:bg-slate-700/80 border border-slate-700 transition">
                        Cancelar
                    </button>
                    <button type="submit" class="px-4 py-2 text-sm font-semibold rounded-xl text-white bg-indigo-600 hover:bg-indigo-500 shadow-lg shadow-indigo-600/30 transition">
                        Salvar Alterações
                    </button>
                </div>
            </form>
        </div>
    </div>

    <!-- Form invisível para submissão de exclusão -->
    <form id="form-excluir-usuario" action="actions/usuario_action.php" method="POST" class="hidden">
        <input type="hidden" name="action" value="excluir">
        <input type="hidden" name="id" id="excluir-id" value="">
    </form>

    <script>
        function abrirModalEditar(u) {
            document.getElementById('edit-id').value = u.id;
            document.getElementById('edit-nome').value = u.nome;
            document.getElementById('edit-email').value = u.email;
            document.getElementById('edit-cargo').value = u.cargo || '';
            document.getElementById('edit-nivel').value = u.nivel_acesso;
            document.getElementById('edit-status').value = u.status;
            openModal('modal-editar-usuario');
        }

        // Substituição do confirm() nativo por modal personalizado!
        function confirmarExcluirUsuario(id, nome) {
            customConfirm(
                'Excluir Usuário',
                `Tem certeza que deseja remover o usuário "${nome}"? Esta operação não pode ser desfeita.`,
                function() {
                    document.getElementById('excluir-id').value = id;
                    document.getElementById('form-excluir-usuario').submit();
                },
                'Sim, Excluir',
                true
            );
        }
    </script>

<?php require_once __DIR__ . '/includes/footer.php'; ?>
