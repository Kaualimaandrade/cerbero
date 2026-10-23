<?php
$currentPage = basename($_SERVER['PHP_SELF']);

// Buscar quantidade de solicitações pendentes para badge
$pendentesCount = 0;
try {
    $pdoSidebar = getConexao();
    $stmtCount = $pdoSidebar->query("SELECT COUNT(*) FROM solicitacoes_acesso WHERE status = 'Pendente'");
    $pendentesCount = (int)$stmtCount->fetchColumn();
} catch (Exception $e) {
    $pendentesCount = 0;
}
?>

<!-- Backdrop Mobile -->
<div id="sidebar-backdrop" class="fixed inset-0 bg-slate-950/80 backdrop-blur-sm z-40 hidden md:hidden transition-opacity"></div>

<!-- Sidebar Principal -->
<aside id="app-sidebar" class="fixed md:static inset-y-0 left-0 z-50 w-72 bg-slate-900 border-r border-slate-800 flex flex-col justify-between transform -translate-x-full md:translate-x-0 transition-transform duration-300 ease-in-out shrink-0">
    <div class="flex flex-col h-full">
        <!-- Logo e Cabeçalho -->
        <div class="h-16 flex items-center px-6 border-b border-slate-800/80 justify-between">
            <a href="dashboard.php" class="flex items-center space-x-3 group">
                <div class="w-10 h-10 rounded-xl bg-gradient-to-tr from-indigo-600 via-indigo-500 to-violet-500 flex items-center justify-center shadow-lg shadow-indigo-600/30 group-hover:scale-105 transition-transform">
                    <svg class="w-6 h-6 text-white" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 12l2 2 4-4m5.618-4.016A11.955 11.955 0 0112 2.944a11.955 11.955 0 01-8.618 3.04A12.02 12.02 0 003 9c0 5.591 3.824 10.29 9 11.622 5.176-1.332 9-6.03 9-11.622 0-1.042-.133-2.052-.382-3.016z"/>
                    </svg>
                </div>
                <div>
                    <span class="text-xl font-extrabold tracking-tight text-white flex items-center space-x-1">
                        <span>CERBERO</span>
                    </span>
                    <span class="block text-[10px] tracking-wider uppercase font-semibold text-indigo-400">Security & Ops</span>
                </div>
            </a>
            
            <!-- Botão fechar em mobile -->
            <button id="close-sidebar-btn" class="md:hidden text-slate-400 hover:text-white" onclick="document.getElementById('app-sidebar').classList.add('-translate-x-full'); document.getElementById('sidebar-backdrop').classList.add('hidden');">
                <svg class="w-6 h-6" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M6 18L18 6M6 6l12 12"/></svg>
            </button>
        </div>

        <!-- Links de Navegação -->
        <nav class="flex-1 px-4 py-6 space-y-1.5 overflow-y-auto">
            <p class="px-3 text-[11px] font-bold uppercase tracking-wider text-slate-500 mb-2">Menu Principal</p>

            <!-- Dashboard -->
            <a href="dashboard.php" class="flex items-center space-x-3 px-3 py-2.5 rounded-xl text-sm font-medium transition <?php echo ($currentPage == 'dashboard.php') ? 'bg-indigo-600/15 text-indigo-400 border border-indigo-500/20 shadow-sm' : 'text-slate-300 hover:bg-slate-800 hover:text-white'; ?>">
                <svg class="w-5 h-5 <?php echo ($currentPage == 'dashboard.php') ? 'text-indigo-400' : 'text-slate-400'; ?>" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M3 12l2-2m0 0l7-7 7 7M5 10v10a1 1 0 001 1h3m10-11l2 2m-2-2v10a1 1 0 01-1 1h-3m-6 0a1 1 0 001-1v-4a1 1 0 011-1h2a1 1 0 011 1v4a1 1 0 001 1m-6 0h6"/>
                </svg>
                <span>Dashboard</span>
            </a>

            <!-- Usuários -->
            <a href="usuarios.php" class="flex items-center space-x-3 px-3 py-2.5 rounded-xl text-sm font-medium transition <?php echo ($currentPage == 'usuarios.php') ? 'bg-indigo-600/15 text-indigo-400 border border-indigo-500/20 shadow-sm' : 'text-slate-300 hover:bg-slate-800 hover:text-white'; ?>">
                <svg class="w-5 h-5 <?php echo ($currentPage == 'usuarios.php') ? 'text-indigo-400' : 'text-slate-400'; ?>" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 4.354a4 4 0 110 5.292M15 21H3v-1a6 6 0 0112 0v1zm0 0h6v-1a6 6 0 00-9-5.197M13 7a4 4 0 11-8 0 4 4 0 018 0z"/>
                </svg>
                <span>Usuários</span>
            </a>

            <!-- Solicitações de Acesso -->
            <a href="solicitacoes.php" class="flex items-center justify-between px-3 py-2.5 rounded-xl text-sm font-medium transition <?php echo ($currentPage == 'solicitacoes.php') ? 'bg-indigo-600/15 text-indigo-400 border border-indigo-500/20 shadow-sm' : 'text-slate-300 hover:bg-slate-800 hover:text-white'; ?>">
                <div class="flex items-center space-x-3">
                    <svg class="w-5 h-5 <?php echo ($currentPage == 'solicitacoes.php') ? 'text-indigo-400' : 'text-slate-400'; ?>" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 5H7a2 2 0 00-2 2v12a2 2 0 002 2h10a2 2 0 002-2V7a2 2 0 00-2-2h-2M9 5a2 2 0 002 2h2a2 2 0 002-2M9 5a2 2 0 012-2h2a2 2 0 012 2m-6 9l2 2 4-4"/>
                    </svg>
                    <span>Solicitações</span>
                </div>
                <?php if ($pendentesCount > 0): ?>
                    <span class="px-2 py-0.5 text-xs font-bold rounded-full bg-amber-500/20 text-amber-300 border border-amber-500/30">
                        <?php echo $pendentesCount; ?>
                    </span>
                <?php endif; ?>
            </a>

            <!-- Registros -->
            <a href="registros.php" class="flex items-center space-x-3 px-3 py-2.5 rounded-xl text-sm font-medium transition <?php echo ($currentPage == 'registros.php') ? 'bg-indigo-600/15 text-indigo-400 border border-indigo-500/20 shadow-sm' : 'text-slate-300 hover:bg-slate-800 hover:text-white'; ?>">
                <svg class="w-5 h-5 <?php echo ($currentPage == 'registros.php') ? 'text-indigo-400' : 'text-slate-400'; ?>" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 11H5m14 0a2 2 0 012 2v6a2 2 0 01-2 2H5a2 2 0 01-2-2v-6a2 2 0 012-2m14 0V9a2 2 0 00-2-2M5 11V9a2 2 0 012-2m0 0V5a2 2 0 012-2h6a2 2 0 012 2v2M7 7h10"/>
                </svg>
                <span>Registros</span>
            </a>

            <!-- Status do Sistema -->
            <a href="status.php" class="flex items-center space-x-3 px-3 py-2.5 rounded-xl text-sm font-medium transition <?php echo ($currentPage == 'status.php') ? 'bg-indigo-600/15 text-indigo-400 border border-indigo-500/20 shadow-sm' : 'text-slate-300 hover:bg-slate-800 hover:text-white'; ?>">
                <svg class="w-5 h-5 <?php echo ($currentPage == 'status.php') ? 'text-indigo-400' : 'text-slate-400'; ?>" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 19v-6a2 2 0 00-2-2H5a2 2 0 00-2 2v6a2 2 0 002 2h2a2 2 0 002-2zm0 0V9a2 2 0 012-2h2a2 2 0 012 2v10m-6 0a2 2 0 002 2h2a2 2 0 002-2m0 0V5a2 2 0 012-2h2a2 2 0 012 2v14a2 2 0 01-2 2h-2a2 2 0 01-2-2z"/>
                </svg>
                <span>Status do Sistema</span>
            </a>
        </nav>

        <!-- Informações do Usuário no rodapé da Sidebar -->
        <div class="p-4 border-t border-slate-800 bg-slate-900/50">
            <div class="flex items-center space-x-3 mb-3">
                <div class="w-10 h-10 rounded-full bg-gradient-to-tr from-indigo-600 to-indigo-400 flex items-center justify-center text-white font-bold text-sm shadow-md">
                    <?php echo strtoupper(substr($_SESSION['usuario_nome'] ?? 'U', 0, 1)); ?>
                </div>
                <div class="flex-1 min-w-0">
                    <p class="text-sm font-semibold text-white truncate"><?php echo htmlspecialchars($_SESSION['usuario_nome'] ?? 'Usuário'); ?></p>
                    <div class="flex items-center space-x-1.5 mt-0.5">
                        <span class="w-2 h-2 rounded-full bg-emerald-400 animate-pulse"></span>
                        <span class="text-xs text-slate-400 font-medium truncate"><?php echo htmlspecialchars($_SESSION['usuario_nivel'] ?? 'Operador'); ?></span>
                    </div>
                </div>
            </div>

            <a href="logout.php" class="w-full flex items-center justify-center space-x-2 py-2 px-3 rounded-lg text-xs font-semibold text-rose-400 hover:text-rose-300 hover:bg-rose-500/10 border border-rose-500/20 transition">
                <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M17 16l4-4m0 0l-4-4m4 4H7m6 4v1a3 3 0 01-3 3H6a3 3 0 01-3-3V7a3 3 0 013-3h4a3 3 0 013 3v1"/></svg>
                <span>Encerrar Sessão</span>
            </a>
        </div>
    </div>
</aside>
