<?php
if (session_status() === PHP_SESSION_NONE) {
    session_start();
}
$nomeUsuario = $_SESSION['usuario_nome'] ?? 'Colaborador';
$nivelUsuario = $_SESSION['usuario_nivel'] ?? 'Visitante';
$cargoUsuario = $_SESSION['usuario_cargo'] ?? 'Operações';

// Cor da badge de acordo com nível
$badgeColor = 'bg-slate-700/60 text-slate-300 border-slate-600';
if ($nivelUsuario === 'Administrador') {
    $badgeColor = 'bg-indigo-500/15 text-indigo-300 border-indigo-500/30';
} elseif ($nivelUsuario === 'Operador') {
    $badgeColor = 'bg-emerald-500/15 text-emerald-300 border-emerald-500/30';
} elseif ($nivelUsuario === 'Visualizador') {
    $badgeColor = 'bg-amber-500/15 text-amber-300 border-amber-500/30';
}
?>

<header class="h-16 bg-slate-900/80 backdrop-blur-md border-b border-slate-800 px-4 md:px-8 flex items-center justify-between shrink-0 z-30">
    <div class="flex items-center space-x-4">
        <!-- Botão Abrir Menu Mobile -->
        <button id="toggle-sidebar-btn" type="button" class="md:hidden p-2 rounded-xl text-slate-400 hover:text-white hover:bg-slate-800 focus:outline-none">
            <svg class="w-6 h-6" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M4 6h16M4 12h16M4 18h16"/></svg>
        </button>

        <!-- Mensagem de Boas-vindas -->
        <div class="hidden sm:block">
            <h2 class="text-sm md:text-base font-semibold text-white flex items-center space-x-2">
                <span>Olá, <span class="text-indigo-400"><?php echo htmlspecialchars($nomeUsuario); ?></span></span>
                <span class="inline-block animate-wave">👋</span>
            </h2>
            <p class="text-xs text-slate-400">Painel de gerenciamento e controle operacional</p>
        </div>
    </div>

    <!-- Indicador de Status e Perfil -->
    <div class="flex items-center space-x-3 md:space-x-4">
        <!-- Status Operacional Pill -->
        <div class="hidden md:flex items-center space-x-2 px-3 py-1.5 rounded-full bg-slate-800/80 border border-slate-700 text-xs">
            <span class="relative flex h-2 w-2">
                <span class="animate-ping absolute inline-flex h-full w-full rounded-full bg-emerald-400 opacity-75"></span>
                <span class="relative inline-flex rounded-full h-2 w-2 bg-emerald-500"></span>
            </span>
            <span class="text-slate-300 font-medium">Sistema Online</span>
        </div>

        <!-- Badge Nível de Acesso -->
        <div class="flex items-center space-x-2 border-l border-slate-800 pl-3 md:pl-4">
            <div class="text-right hidden sm:block">
                <p class="text-xs font-semibold text-white leading-tight"><?php echo htmlspecialchars($nomeUsuario); ?></p>
                <span class="inline-flex items-center px-2 py-0.5 rounded text-[10px] font-bold border <?php echo $badgeColor; ?> uppercase tracking-wider mt-0.5">
                    <?php echo htmlspecialchars($nivelUsuario); ?>
                </span>
            </div>

            <!-- Avatar -->
            <div class="w-9 h-9 rounded-xl bg-gradient-to-tr from-indigo-600 to-violet-600 flex items-center justify-center text-white font-bold text-sm shadow-md shadow-indigo-600/20">
                <?php echo strtoupper(substr($nomeUsuario, 0, 1)); ?>
            </div>
        </div>
    </div>
</header>
