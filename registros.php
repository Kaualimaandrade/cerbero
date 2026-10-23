<?php
/**
 * Gerenciamento de Registros do Sistema (registros.php)
 * Sistema: Cerbero
 */

require_once __DIR__ . '/includes/auth_check.php';

$pdo = getConexao();

$busca = trim($_GET['busca'] ?? '');
$categoria = trim($_GET['categoria'] ?? '');
$prioridade = trim($_GET['prioridade'] ?? '');

$sql = "
    SELECT r.*, u.nome AS autor_nome 
    FROM registros r 
    LEFT JOIN usuarios u ON r.usuario_id = u.id 
    WHERE 1=1
";
$params = [];

if (!empty($busca)) {
    $sql .= " AND (r.titulo LIKE :busca OR r.descricao LIKE :busca)";
    $params[':busca'] = "%{$busca}%";
}

if (!empty($categoria)) {
    $sql .= " AND r.categoria = :categoria";
    $params[':categoria'] = $categoria;
}

if (!empty($prioridade)) {
    $sql .= " AND r.prioridade = :prioridade";
    $params[':prioridade'] = $prioridade;
}

$sql .= " ORDER BY r.id DESC";

$stmt = $pdo->prepare($sql);
$stmt->execute($params);
$registros = $stmt->fetchAll();

// Categorias distintas para filtro
$categorias = $pdo->query("SELECT DISTINCT categoria FROM registros ORDER BY categoria")->fetchAll(PDO::FETCH_COLUMN);

$pageTitle = "Registros do Sistema";
require_once __DIR__ . '/includes/header.php';
require_once __DIR__ . '/includes/sidebar.php';
?>

<div class="flex-1 flex flex-col min-w-0 overflow-y-auto">
    <?php require_once __DIR__ . '/includes/navbar.php'; ?>

    <main class="flex-1 p-4 md:p-8 space-y-6 max-w-7xl w-full mx-auto">
        <!-- Cabeçalho -->
        <div class="flex flex-col sm:flex-row sm:items-center justify-between gap-4">
            <div>
                <h1 class="text-2xl font-extrabold text-white tracking-tight">Registros e Operações</h1>
                <p class="text-xs text-slate-400">Controle de tarefas, operações de compras e manutenções ativas</p>
            </div>
            
            <?php if (in_array($_SESSION['usuario_nivel'] ?? '', ['Administrador', 'Operador'])): ?>
                <button type="button" onclick="openModal('modal-novo-registro')" class="inline-flex items-center space-x-2 px-4 py-2.5 rounded-xl bg-gradient-to-r from-indigo-600 to-violet-600 hover:from-indigo-500 hover:to-violet-500 text-white text-xs font-semibold shadow-lg shadow-indigo-600/30 transition self-start sm:self-auto">
                    <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 4v16m8-8H4"/></svg>
                    <span>Novo Registro</span>
                </button>
            <?php endif; ?>
        </div>

        <!-- Filtros -->
        <div class="glass-card rounded-2xl p-4 border border-slate-800 shadow-md">
            <form method="GET" class="flex flex-col sm:flex-row gap-3">
                <div class="flex-1 relative">
                    <div class="absolute inset-y-0 left-0 pl-3.5 flex items-center pointer-events-none text-slate-400">
                        <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M21 21l-6-6m2-5a7 7 0 11-14 0 7 7 0 0114 0z"/></svg>
                    </div>
                    <input type="text" name="busca" value="<?php echo htmlspecialchars($busca); ?>" placeholder="Buscar por título ou descrição..." class="w-full bg-slate-950/70 border border-slate-700 rounded-xl pl-10 pr-4 py-2 text-xs md:text-sm text-white focus:outline-none focus:border-indigo-500 placeholder-slate-500">
                </div>

                <div class="w-full sm:w-44">
                    <select name="categoria" onchange="this.form.submit()" class="w-full bg-slate-950/70 border border-slate-700 rounded-xl px-3 py-2 text-xs md:text-sm text-white focus:outline-none focus:border-indigo-500">
                        <option value="">Todas Categorias</option>
                        <?php foreach ($categorias as $cat): ?>
                            <option value="<?php echo htmlspecialchars($cat); ?>" <?php echo $categoria === $cat ? 'selected' : ''; ?>>
                                <?php echo htmlspecialchars($cat); ?>
                            </option>
                        <?php endforeach; ?>
                    </select>
                </div>

                <div class="w-full sm:w-44">
                    <select name="prioridade" onchange="this.form.submit()" class="w-full bg-slate-950/70 border border-slate-700 rounded-xl px-3 py-2 text-xs md:text-sm text-white focus:outline-none focus:border-indigo-500">
                        <option value="">Todas Prioridades</option>
                        <option value="Baixa" <?php echo $prioridade === 'Baixa' ? 'selected' : ''; ?>>Baixa</option>
                        <option value="Média" <?php echo $prioridade === 'Média' ? 'selected' : ''; ?>>Média</option>
                        <option value="Alta" <?php echo $prioridade === 'Alta' ? 'selected' : ''; ?>>Alta</option>
                        <option value="Crítica" <?php echo $prioridade === 'Crítica' ? 'selected' : ''; ?>>Crítica</option>
                    </select>
                </div>

                <button type="submit" class="px-4 py-2 bg-slate-800 hover:bg-slate-700 text-white rounded-xl text-xs font-semibold border border-slate-700 transition">
                    Filtrar
                </button>
            </form>
        </div>

        <!-- Tabela de Registros -->
        <div class="glass-card rounded-2xl border border-slate-800 shadow-xl overflow-hidden">
            <div class="overflow-x-auto">
                <table class="w-full text-left text-sm">
                    <thead>
                        <tr class="bg-slate-900/90 text-slate-400 text-xs uppercase tracking-wider border-b border-slate-800">
                            <th class="py-3.5 px-4 font-semibold">Registro / Título</th>
                            <th class="py-3.5 px-4 font-semibold">Categoria</th>
                            <th class="py-3.5 px-4 font-semibold">Prioridade</th>
                            <th class="py-3.5 px-4 font-semibold">Status</th>
                            <th class="py-3.5 px-4 font-semibold">Criado Por</th>
                            <th class="py-3.5 px-4 font-semibold">Data</th>
                            <th class="py-3.5 px-4 font-semibold text-right">Ações</th>
                        </tr>
                    </thead>
                    <tbody class="divide-y divide-slate-800/60">
                        <?php if (empty($registros)): ?>
                            <tr>
                                <td colspan="7" class="py-10 text-center text-slate-500">
                                    Nenhum registro encontrado.
                                </td>
                            </tr>
                        <?php else: ?>
                            <?php foreach ($registros as $r): ?>
                                <tr class="hover:bg-slate-800/40 transition">
                                    <td class="py-3.5 px-4">
                                        <div class="font-medium text-white"><?php echo htmlspecialchars($r['titulo']); ?></div>
                                        <?php if (!empty($r['descricao'])): ?>
                                            <div class="text-xs text-slate-400 truncate max-w-sm"><?php echo htmlspecialchars($r['descricao']); ?></div>
                                        <?php endif; ?>
                                    </td>
                                    <td class="py-3.5 px-4 text-xs text-slate-300">
                                        <span class="px-2 py-0.5 rounded bg-slate-800 text-slate-300 border border-slate-700">
                                            <?php echo htmlspecialchars($r['categoria']); ?>
                                        </span>
                                    </td>
                                    <td class="py-3.5 px-4">
                                        <?php 
                                        $corPri = 'bg-slate-700 text-slate-300';
                                        if ($r['prioridade'] === 'Crítica') $corPri = 'bg-rose-500/20 text-rose-300 border border-rose-500/30';
                                        elseif ($r['prioridade'] === 'Alta') $corPri = 'bg-amber-500/20 text-amber-300 border border-amber-500/30';
                                        elseif ($r['prioridade'] === 'Média') $corPri = 'bg-indigo-500/20 text-indigo-300 border border-indigo-500/30';
                                        elseif ($r['prioridade'] === 'Baixa') $corPri = 'bg-slate-800 text-slate-400 border border-slate-700';
                                        ?>
                                        <span class="inline-flex items-center px-2 py-0.5 rounded text-xs font-semibold <?php echo $corPri; ?>">
                                            <?php echo htmlspecialchars($r['prioridade']); ?>
                                        </span>
                                    </td>
                                    <td class="py-3.5 px-4">
                                        <span class="text-xs font-semibold text-slate-300"><?php echo htmlspecialchars($r['status']); ?></span>
                                    </td>
                                    <td class="py-3.5 px-4 text-xs text-slate-400">
                                        <?php echo htmlspecialchars($r['autor_nome'] ?? 'Sistema'); ?>
                                    </td>
                                    <td class="py-3.5 px-4 text-xs text-slate-400 whitespace-nowrap">
                                        <?php echo date('d/m/Y', strtotime($r['criado_em'])); ?>
                                    </td>
                                    <td class="py-3.5 px-4 text-right space-x-2 whitespace-nowrap">
                                        <?php if (in_array($_SESSION['usuario_nivel'] ?? '', ['Administrador', 'Operador'])): ?>
                                            <button type="button" onclick="abrirModalEditarRegistro(<?php echo htmlspecialchars(json_encode($r)); ?>)" class="p-1.5 rounded-lg text-slate-400 hover:text-white hover:bg-slate-800 transition" title="Editar">
                                                <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M11 5H6a2 2 0 00-2 2v11a2 2 0 002 2h11a2 2 0 002-2v-5m-1.414-9.414a2 2 0 112.828 2.828L11.828 15H9v-2.828l8.586-8.586z"/></svg>
                                            </button>
                                            <button type="button" onclick="confirmarExcluirRegistro(<?php echo $r['id']; ?>, '<?php echo htmlspecialchars(addslashes($r['titulo'])); ?>')" class="p-1.5 rounded-lg text-rose-400 hover:text-rose-300 hover:bg-rose-500/10 transition" title="Excluir">
                                                <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 7l-.867 12.142A2 2 0 0116.138 21H7.862a2 2 0 01-1.995-1.858L5 7m5 4v6m4-6v6m1-10V4a1 1 0 00-1-1h-4a1 1 0 00-1 1v3M4 7h16"/></svg>
                                            </button>
                                        <?php else: ?>
                                            <span class="text-xs text-slate-500 italic">Consulta</span>
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

    <!-- MODAL NOVO REGISTRO -->
    <div id="modal-novo-registro" class="modal-overlay fixed inset-0 z-50 flex items-center justify-center p-4 bg-slate-950/80 backdrop-blur-sm hidden transition-opacity">
        <div class="modal-box bg-slate-900 border border-slate-700/80 rounded-2xl max-w-md w-full p-6 shadow-2xl transform scale-95 opacity-0 transition-all text-slate-100">
            <div class="flex items-center justify-between mb-4">
                <h3 class="text-lg font-bold text-white">Novo Registro</h3>
                <button type="button" onclick="closeModal('modal-novo-registro')" class="text-slate-400 hover:text-white">
                    <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M6 18L18 6M6 6l12 12"/></svg>
                </button>
            </div>

            <form action="actions/registro_action.php" method="POST" class="space-y-4">
                <input type="hidden" name="action" value="criar">

                <div>
                    <label class="block text-xs font-semibold text-slate-300 uppercase mb-1">Título do Registro</label>
                    <input type="text" name="titulo" required placeholder="Ex: Recebimento de Lote #8421" class="w-full bg-slate-950 border border-slate-700 rounded-xl px-3.5 py-2 text-sm text-white focus:outline-none focus:border-indigo-500">
                </div>

                <div class="grid grid-cols-2 gap-3">
                    <div>
                        <label class="block text-xs font-semibold text-slate-300 uppercase mb-1">Categoria</label>
                        <input type="text" name="categoria" required placeholder="Ex: Operações" class="w-full bg-slate-950 border border-slate-700 rounded-xl px-3.5 py-2 text-sm text-white focus:outline-none focus:border-indigo-500">
                    </div>
                    <div>
                        <label class="block text-xs font-semibold text-slate-300 uppercase mb-1">Prioridade</label>
                        <select name="prioridade" class="w-full bg-slate-950 border border-slate-700 rounded-xl px-3 py-2 text-sm text-white focus:outline-none focus:border-indigo-500">
                            <option value="Baixa">Baixa</option>
                            <option value="Média" selected>Média</option>
                            <option value="Alta">Alta</option>
                            <option value="Crítica">Crítica</option>
                        </select>
                    </div>
                </div>

                <div>
                    <label class="block text-xs font-semibold text-slate-300 uppercase mb-1">Status</label>
                    <select name="status" class="w-full bg-slate-950 border border-slate-700 rounded-xl px-3 py-2 text-sm text-white focus:outline-none focus:border-indigo-500">
                        <option value="Pendente">Pendente</option>
                        <option value="Em Andamento">Em Andamento</option>
                        <option value="Concluído">Concluído</option>
                        <option value="Cancelado">Cancelado</option>
                    </select>
                </div>

                <div>
                    <label class="block text-xs font-semibold text-slate-300 uppercase mb-1">Descrição</label>
                    <textarea name="descricao" rows="3" placeholder="Detalhes operacionais ou notas..." class="w-full bg-slate-950 border border-slate-700 rounded-xl px-3.5 py-2 text-sm text-white focus:outline-none focus:border-indigo-500"></textarea>
                </div>

                <div class="flex items-center justify-end space-x-3 pt-2">
                    <button type="button" onclick="closeModal('modal-novo-registro')" class="px-4 py-2 text-sm font-medium rounded-xl text-slate-300 bg-slate-800 hover:bg-slate-700/80 border border-slate-700 transition">
                        Cancelar
                    </button>
                    <button type="submit" class="px-4 py-2 text-sm font-semibold rounded-xl text-white bg-indigo-600 hover:bg-indigo-500 shadow-lg shadow-indigo-600/30 transition">
                        Salvar Registro
                    </button>
                </div>
            </form>
        </div>
    </div>

    <!-- MODAL EDITAR REGISTRO -->
    <div id="modal-editar-registro" class="modal-overlay fixed inset-0 z-50 flex items-center justify-center p-4 bg-slate-950/80 backdrop-blur-sm hidden transition-opacity">
        <div class="modal-box bg-slate-900 border border-slate-700/80 rounded-2xl max-w-md w-full p-6 shadow-2xl transform scale-95 opacity-0 transition-all text-slate-100">
            <div class="flex items-center justify-between mb-4">
                <h3 class="text-lg font-bold text-white">Editar Registro</h3>
                <button type="button" onclick="closeModal('modal-editar-registro')" class="text-slate-400 hover:text-white">
                    <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M6 18L18 6M6 6l12 12"/></svg>
                </button>
            </div>

            <form action="actions/registro_action.php" method="POST" class="space-y-4">
                <input type="hidden" name="action" value="editar">
                <input type="hidden" name="id" id="edit-reg-id" value="">

                <div>
                    <label class="block text-xs font-semibold text-slate-300 uppercase mb-1">Título do Registro</label>
                    <input type="text" name="titulo" id="edit-reg-titulo" required class="w-full bg-slate-950 border border-slate-700 rounded-xl px-3.5 py-2 text-sm text-white focus:outline-none focus:border-indigo-500">
                </div>

                <div class="grid grid-cols-2 gap-3">
                    <div>
                        <label class="block text-xs font-semibold text-slate-300 uppercase mb-1">Categoria</label>
                        <input type="text" name="categoria" id="edit-reg-categoria" required class="w-full bg-slate-950 border border-slate-700 rounded-xl px-3.5 py-2 text-sm text-white focus:outline-none focus:border-indigo-500">
                    </div>
                    <div>
                        <label class="block text-xs font-semibold text-slate-300 uppercase mb-1">Prioridade</label>
                        <select name="prioridade" id="edit-reg-prioridade" class="w-full bg-slate-950 border border-slate-700 rounded-xl px-3 py-2 text-sm text-white focus:outline-none focus:border-indigo-500">
                            <option value="Baixa">Baixa</option>
                            <option value="Média">Média</option>
                            <option value="Alta">Alta</option>
                            <option value="Crítica">Crítica</option>
                        </select>
                    </div>
                </div>

                <div>
                    <label class="block text-xs font-semibold text-slate-300 uppercase mb-1">Status</label>
                    <select name="status" id="edit-reg-status" class="w-full bg-slate-950 border border-slate-700 rounded-xl px-3 py-2 text-sm text-white focus:outline-none focus:border-indigo-500">
                        <option value="Pendente">Pendente</option>
                        <option value="Em Andamento">Em Andamento</option>
                        <option value="Concluído">Concluído</option>
                        <option value="Cancelado">Cancelado</option>
                    </select>
                </div>

                <div>
                    <label class="block text-xs font-semibold text-slate-300 uppercase mb-1">Descrição</label>
                    <textarea name="descricao" id="edit-reg-descricao" rows="3" class="w-full bg-slate-950 border border-slate-700 rounded-xl px-3.5 py-2 text-sm text-white focus:outline-none focus:border-indigo-500"></textarea>
                </div>

                <div class="flex items-center justify-end space-x-3 pt-2">
                    <button type="button" onclick="closeModal('modal-editar-registro')" class="px-4 py-2 text-sm font-medium rounded-xl text-slate-300 bg-slate-800 hover:bg-slate-700/80 border border-slate-700 transition">
                        Cancelar
                    </button>
                    <button type="submit" class="px-4 py-2 text-sm font-semibold rounded-xl text-white bg-indigo-600 hover:bg-indigo-500 shadow-lg shadow-indigo-600/30 transition">
                        Atualizar Registro
                    </button>
                </div>
            </form>
        </div>
    </div>

    <!-- Form de exclusão -->
    <form id="form-excluir-registro" action="actions/registro_action.php" method="POST" class="hidden">
        <input type="hidden" name="action" value="excluir">
        <input type="hidden" name="id" id="excluir-reg-id" value="">
    </form>

    <script>
        function abrirModalEditarRegistro(r) {
            document.getElementById('edit-reg-id').value = r.id;
            document.getElementById('edit-reg-titulo').value = r.titulo;
            document.getElementById('edit-reg-categoria').value = r.categoria;
            document.getElementById('edit-reg-prioridade').value = r.prioridade;
            document.getElementById('edit-reg-status').value = r.status;
            document.getElementById('edit-reg-descricao').value = r.descricao || '';
            openModal('modal-editar-registro');
        }

        function confirmarExcluirRegistro(id, titulo) {
            customConfirm(
                'Excluir Registro',
                `Deseja realmente remover o registro "${titulo}"?`,
                function() {
                    document.getElementById('excluir-reg-id').value = id;
                    document.getElementById('form-excluir-registro').submit();
                },
                'Excluir',
                true
            );
        }
    </script>

<?php require_once __DIR__ . '/includes/footer.php'; ?>
