<?php
/**
 * Tela de Login e Solicitação de Acesso
 * Sistema: Cerbero
 */

if (session_status() === PHP_SESSION_NONE) {
    session_start();
}

// Redireciona se já estiver autenticado
if (isset($_SESSION['usuario_id'])) {
    header('Location: dashboard.php');
    exit;
}

$pageTitle = "Acesso ao Sistema";
$tab = $_GET['tab'] ?? 'login';
?>
<!DOCTYPE html>
<html lang="pt-BR" class="h-full bg-slate-950">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Cerbero - Acesso ao Sistema</title>
    <script src="https://cdn.tailwindcss.com"></script>
    <script>
        tailwind.config = {
            darkMode: 'class',
            theme: {
                extend: {
                    colors: {
                        slate: {
                            850: '#151f32',
                            950: '#070b14',
                        }
                    }
                }
            }
        }
    </script>
    <link href="https://fonts.googleapis.com/css2?family=Plus+Jakarta+Sans:wght@300;400;500;600;700;800&display=swap" rel="stylesheet">
    <link rel="stylesheet" href="assets/css/custom.css">
    <style>
        body { font-family: 'Plus Jakarta Sans', sans-serif; }
    </style>
</head>
<body class="min-h-full bg-slate-950 text-slate-100 flex flex-col justify-center py-12 px-4 sm:px-6 lg:px-8 relative overflow-x-hidden selection:bg-indigo-600 selection:text-white">

    <?php require_once __DIR__ . '/includes/toast.php'; ?>

    <!-- Background Glow Decorativo -->
    <div class="fixed top-1/4 left-1/2 -translate-x-1/2 -translate-y-1/2 w-[600px] h-[350px] bg-gradient-to-tr from-indigo-600/15 via-violet-600/10 to-transparent blur-3xl pointer-events-none rounded-full"></div>

    <div class="sm:mx-auto sm:w-full sm:max-w-md relative z-10">
        <!-- Logo do Sistema Cerbero -->
        <div class="flex flex-col items-center">
            <div class="w-14 h-14 rounded-2xl bg-gradient-to-tr from-indigo-600 via-indigo-500 to-violet-500 flex items-center justify-center shadow-xl shadow-indigo-600/30 mb-4 transform hover:scale-105 transition-transform duration-300">
                <svg class="w-8 h-8 text-white" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 12l2 2 4-4m5.618-4.016A11.955 11.955 0 0112 2.944a11.955 11.955 0 01-8.618 3.04A12.02 12.02 0 003 9c0 5.591 3.824 10.29 9 11.622 5.176-1.332 9-6.03 9-11.622 0-1.042-.133-2.052-.382-3.016z"/>
                </svg>
            </div>
            <h1 class="text-3xl font-extrabold tracking-tight text-white">CERBERO</h1>
            <p class="mt-1 text-sm text-slate-400">Sistema de Gerenciamento & Administração</p>
        </div>

        <!-- Seletor de Abas (Login / Solicitar Acesso) -->
        <div class="mt-8 flex rounded-xl bg-slate-900/90 p-1 border border-slate-800">
            <button type="button" id="tab-btn-login" onclick="switchAuthTab('login')" class="flex-1 py-2 text-xs md:text-sm font-semibold rounded-lg transition duration-200 <?php echo $tab !== 'solicitar' ? 'bg-indigo-600 text-white shadow-md' : 'text-slate-400 hover:text-white'; ?>">
                Entrar no Painel
            </button>
            <button type="button" id="tab-btn-solicitar" onclick="switchAuthTab('solicitar')" class="flex-1 py-2 text-xs md:text-sm font-semibold rounded-lg transition duration-200 <?php echo $tab === 'solicitar' ? 'bg-indigo-600 text-white shadow-md' : 'text-slate-400 hover:text-white'; ?>">
                Solicitar Acesso
            </button>
        </div>

        <!-- Card Principal -->
        <div class="mt-4 bg-slate-900/80 backdrop-blur-xl border border-slate-800/80 rounded-2xl p-6 sm:p-8 shadow-2xl">
            
            <!-- FORMULÁRIO DE LOGIN -->
            <div id="form-login-container" class="<?php echo $tab === 'solicitar' ? 'hidden' : ''; ?>">
                <form action="actions/auth_action.php" method="POST" class="space-y-5">
                    <input type="hidden" name="action" value="login">

                    <div>
                        <label for="email" class="block text-xs font-semibold text-slate-300 uppercase tracking-wider mb-1.5">E-mail Corporativo</label>
                        <div class="relative">
                            <div class="absolute inset-y-0 left-0 pl-3.5 flex items-center pointer-events-none text-slate-400">
                                <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M16 12a4 4 0 10-8 0 4 4 0 008 0zm0 0v1.5a2.5 2.5 0 005 0V12a9 9 0 10-9 9m4.5-1.206a8.959 8.959 0 01-4.5 1.207"/></svg>
                            </div>
                            <input id="email" name="email" type="email" required placeholder="kaualimaandrade321@gmail.com" class="w-full bg-slate-950/70 border border-slate-700/80 focus:border-indigo-500 focus:ring-1 focus:ring-indigo-500 rounded-xl pl-11 pr-4 py-2.5 text-sm text-white placeholder-slate-500 transition">
                        </div>
                    </div>

                    <div>
                        <div class="flex items-center justify-between mb-1.5">
                            <label for="senha" class="block text-xs font-semibold text-slate-300 uppercase tracking-wider">Senha de Acesso</label>
                        </div>
                        <div class="relative">
                            <div class="absolute inset-y-0 left-0 pl-3.5 flex items-center pointer-events-none text-slate-400">
                                <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 15v2m-6 4h12a2 2 0 002-2v-6a2 2 0 00-2-2H6a2 2 0 00-2 2v6a2 2 0 002 2zm10-10V7a4 4 0 00-8 0v4h8z"/></svg>
                            </div>
                            <input id="senha" name="senha" type="password" required placeholder="••••••••" class="w-full bg-slate-950/70 border border-slate-700/80 focus:border-indigo-500 focus:ring-1 focus:ring-indigo-500 rounded-xl pl-11 pr-4 py-2.5 text-sm text-white placeholder-slate-500 transition">
                        </div>
                    </div>

                    <button type="submit" class="w-full py-3 px-4 rounded-xl text-sm font-semibold text-white bg-gradient-to-r from-indigo-600 to-violet-600 hover:from-indigo-500 hover:to-violet-500 shadow-lg shadow-indigo-600/30 transition transform hover:-translate-y-0.5 active:translate-y-0">
                        Entrar no Sistema
                    </button>
                </form>

                <!-- Credenciais Rápidas de Teste -->
                <div class="mt-6 pt-5 border-t border-slate-800 text-xs text-slate-400">
                    <p class="font-medium text-slate-300 mb-2 flex items-center space-x-1.5">
                        <svg class="w-4 h-4 text-indigo-400" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M13 16h-1v-4h-1m1-4h.01M21 12a9 9 0 11-18 0 9 9 0 0118 0z"/></svg>
                        <span>Acesso Rápido para Avaliação:</span>
                    </p>
                    <div class="grid grid-cols-2 gap-2">
                        <button type="button" onclick="preencherLogin('kaualimaandrade321@gmail.com', 'parceiro246')" class="p-2 rounded-lg bg-slate-800/80 hover:bg-slate-700/80 text-left border border-indigo-500/30 transition group">
                            <span class="block font-semibold text-indigo-300 group-hover:text-indigo-200">Kauan (Admin)</span>
                            <span class="block text-[11px] text-slate-400 truncate">kaualimaandrade321...</span>
                        </button>
                        <button type="button" onclick="preencherLogin('admin@cerbero.com', 'parceiro246')" class="p-2 rounded-lg bg-slate-800/80 hover:bg-slate-700/80 text-left border border-slate-700/60 transition group">
                            <span class="block font-semibold text-emerald-300 group-hover:text-emerald-200">Administrador</span>
                            <span class="block text-[11px] text-slate-400">admin@cerbero.com</span>
                        </button>
                    </div>
                </div>
            </div>

            <!-- FORMULÁRIO DE SOLICITAÇÃO DE ACESSO -->
            <div id="form-solicitar-container" class="<?php echo $tab !== 'solicitar' ? 'hidden' : ''; ?>">
                <form action="actions/auth_action.php" method="POST" class="space-y-4">
                    <input type="hidden" name="action" value="solicitar_acesso">

                    <div>
                        <label class="block text-xs font-semibold text-slate-300 uppercase tracking-wider mb-1">Nome Completo</label>
                        <input name="nome" type="text" required placeholder="Ex: Roberto Silva" class="w-full bg-slate-950/70 border border-slate-700/80 focus:border-indigo-500 focus:ring-1 focus:ring-indigo-500 rounded-xl px-3.5 py-2.5 text-sm text-white placeholder-slate-500 transition">
                    </div>

                    <div>
                        <label class="block text-xs font-semibold text-slate-300 uppercase tracking-wider mb-1">E-mail Corporativo</label>
                        <input name="email" type="email" required placeholder="roberto@karhub.com" class="w-full bg-slate-950/70 border border-slate-700/80 focus:border-indigo-500 focus:ring-1 focus:ring-indigo-500 rounded-xl px-3.5 py-2.5 text-sm text-white placeholder-slate-500 transition">
                    </div>

                    <div class="grid grid-cols-2 gap-3">
                        <div>
                            <label class="block text-xs font-semibold text-slate-300 uppercase tracking-wider mb-1">Cargo</label>
                            <input name="cargo" type="text" required placeholder="Ex: Analista" class="w-full bg-slate-950/70 border border-slate-700/80 focus:border-indigo-500 focus:ring-1 focus:ring-indigo-500 rounded-xl px-3.5 py-2.5 text-sm text-white placeholder-slate-500 transition">
                        </div>
                        <div>
                            <label class="block text-xs font-semibold text-slate-300 uppercase tracking-wider mb-1">Departamento</label>
                            <input name="departamento" type="text" required placeholder="Ex: Compras" class="w-full bg-slate-950/70 border border-slate-700/80 focus:border-indigo-500 focus:ring-1 focus:ring-indigo-500 rounded-xl px-3.5 py-2.5 text-sm text-white placeholder-slate-500 transition">
                        </div>
                    </div>

                    <div>
                        <label class="block text-xs font-semibold text-slate-300 uppercase tracking-wider mb-1">Nível Solicitado</label>
                        <select name="nivel_solicitado" class="w-full bg-slate-950/70 border border-slate-700/80 focus:border-indigo-500 focus:ring-1 focus:ring-indigo-500 rounded-xl px-3.5 py-2.5 text-sm text-white transition">
                            <option value="Operador">Operador (Acesso a Operações e Registros)</option>
                            <option value="Visualizador">Visualizador (Apenas Leitura)</option>
                            <option value="Administrador">Administrador (Controle Total)</option>
                        </select>
                    </div>

                    <div>
                        <label class="block text-xs font-semibold text-slate-300 uppercase tracking-wider mb-1">Justificativa de Acesso</label>
                        <textarea name="motivo" rows="2" required placeholder="Explique brevemente por que você precisa acessar o sistema..." class="w-full bg-slate-950/70 border border-slate-700/80 focus:border-indigo-500 focus:ring-1 focus:ring-indigo-500 rounded-xl px-3.5 py-2 text-sm text-white placeholder-slate-500 transition"></textarea>
                    </div>

                    <button type="submit" class="w-full py-3 px-4 rounded-xl text-sm font-semibold text-white bg-gradient-to-r from-indigo-600 to-violet-600 hover:from-indigo-500 hover:to-violet-500 shadow-lg shadow-indigo-600/30 transition transform hover:-translate-y-0.5 active:translate-y-0">
                        Enviar Solicitação de Acesso
                    </button>
                </form>
            </div>
        </div>

        <p class="text-center text-xs text-slate-500 mt-6">
            Cerbero Security System &bull; KarHub Operações &bull; v1.0.0
        </p>
    </div>

    <!-- Scripts de Interação -->
    <script src="assets/js/app.js"></script>
    <script>
        function switchAuthTab(tab) {
            const btnLogin = document.getElementById('tab-btn-login');
            const btnSolicitar = document.getElementById('tab-btn-solicitar');
            const formLogin = document.getElementById('form-login-container');
            const formSolicitar = document.getElementById('form-solicitar-container');

            if (tab === 'login') {
                btnLogin.className = 'flex-1 py-2 text-xs md:text-sm font-semibold rounded-lg transition duration-200 bg-indigo-600 text-white shadow-md';
                btnSolicitar.className = 'flex-1 py-2 text-xs md:text-sm font-semibold rounded-lg transition duration-200 text-slate-400 hover:text-white';
                formLogin.classList.remove('hidden');
                formSolicitar.classList.add('hidden');
            } else {
                btnSolicitar.className = 'flex-1 py-2 text-xs md:text-sm font-semibold rounded-lg transition duration-200 bg-indigo-600 text-white shadow-md';
                btnLogin.className = 'flex-1 py-2 text-xs md:text-sm font-semibold rounded-lg transition duration-200 text-slate-400 hover:text-white';
                formSolicitar.classList.remove('hidden');
                formLogin.classList.add('hidden');
            }
        }

        function preencherLogin(email, senha) {
            document.getElementById('email').value = email;
            document.getElementById('senha').value = senha;
            showToast('info', 'Credenciais de demonstração preenchidas!');
        }
    </script>
</body>
</html>
