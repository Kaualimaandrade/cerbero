/**
 * Cerbero - Core JavaScript
 * Manipulação de Modais Personalizados, Toasts e Interatividade Mobile First
 */

document.addEventListener('DOMContentLoaded', () => {
    // Inicialização de Toasts presentes na página
    const toastDataEl = document.getElementById('toast-data');
    if (toastDataEl) {
        try {
            const data = JSON.parse(toastDataEl.textContent);
            if (data && data.mensagem) {
                showToast(data.tipo || 'info', data.mensagem);
            }
        } catch (e) {
            console.error('Erro ao ler toast inicial:', e);
        }
    }

    // Fechar modais ao pressionar ESC
    document.addEventListener('keydown', (e) => {
        if (e.key === 'Escape') {
            document.querySelectorAll('.modal-overlay:not(.hidden)').forEach(modal => {
                closeModal(modal.id);
            });
        }
    });

    // Fechar modal ao clicar fora do conteúdo
    document.querySelectorAll('.modal-overlay').forEach(overlay => {
        overlay.addEventListener('click', (e) => {
            if (e.target === overlay) {
                closeModal(overlay.id);
            }
        });
    });

    // Toggle Sidebar Mobile
    const toggleSidebarBtn = document.getElementById('toggle-sidebar-btn');
    const sidebar = document.getElementById('app-sidebar');
    const sidebarBackdrop = document.getElementById('sidebar-backdrop');

    if (toggleSidebarBtn && sidebar) {
        toggleSidebarBtn.addEventListener('click', () => {
            sidebar.classList.toggle('-translate-x-full');
            if (sidebarBackdrop) {
                sidebarBackdrop.classList.toggle('hidden');
            }
        });
    }

    if (sidebarBackdrop && sidebar) {
        sidebarBackdrop.addEventListener('click', () => {
            sidebar.classList.add('-translate-x-full');
            sidebarBackdrop.classList.add('hidden');
        });
    }
});

/**
 * Exibe um Toast moderno com ícone e auto-dismiss
 * 
 * @param {'success'|'error'|'warning'|'info'} type 
 * @param {string} message 
 * @param {number} duration 
 */
function showToast(type, message, duration = 4500) {
    let container = document.getElementById('toast-container');
    if (!container) {
        container = document.createElement('div');
        container.id = 'toast-container';
        container.className = 'fixed top-5 right-5 z-50 flex flex-col space-y-3 max-w-sm w-full pointer-events-none px-4 sm:px-0';
        document.body.appendChild(container);
    }

    const toast = document.createElement('div');
    toast.className = `pointer-events-auto flex items-start p-4 rounded-xl shadow-2xl border backdrop-blur-md transition-all duration-300 transform translate-y-[-20px] opacity-0 text-sm font-medium`;

    let bgBorder = 'bg-slate-900/95 border-slate-700 text-slate-100';
    let iconSvg = '';

    switch (type) {
        case 'success':
            bgBorder = 'bg-emerald-950/95 border-emerald-700/80 text-emerald-100 shadow-emerald-950/40';
            iconSvg = `<svg class="w-5 h-5 text-emerald-400 shrink-0" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 12l2 2 4-4m6 2a9 9 0 11-18 0 9 9 0 0118 0z"/></svg>`;
            break;
        case 'error':
            bgBorder = 'bg-rose-950/95 border-rose-700/80 text-rose-100 shadow-rose-950/40';
            iconSvg = `<svg class="w-5 h-5 text-rose-400 shrink-0" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 8v4m0 4h.01M21 12a9 9 0 11-18 0 9 9 0 0118 0z"/></svg>`;
            break;
        case 'warning':
            bgBorder = 'bg-amber-950/95 border-amber-700/80 text-amber-100 shadow-amber-950/40';
            iconSvg = `<svg class="w-5 h-5 text-amber-400 shrink-0" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 9v2m0 4h.01m-6.938 4h13.856c1.54 0 2.502-1.667 1.732-3L13.732 4c-.77-1.333-2.694-1.333-3.464 0L3.34 16c-.77 1.333.192 3 1.732 3z"/></svg>`;
            break;
        default:
            bgBorder = 'bg-indigo-950/95 border-indigo-700/80 text-indigo-100 shadow-indigo-950/40';
            iconSvg = `<svg class="w-5 h-5 text-indigo-400 shrink-0" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M13 16h-1v-4h-1m1-4h.01M21 12a9 9 0 11-18 0 9 9 0 0118 0z"/></svg>`;
    }

    toast.className += ` ${bgBorder}`;
    toast.innerHTML = `
        <div class="mr-3 mt-0.5">${iconSvg}</div>
        <div class="flex-1 pr-2">${message}</div>
        <button type="button" class="text-slate-400 hover:text-white transition ml-2 self-start" onclick="this.closest('.pointer-events-auto').remove()">
            <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M6 18L18 6M6 6l12 12"/></svg>
        </button>
    `;

    container.appendChild(toast);

    // Animar entrada
    requestAnimationFrame(() => {
        toast.classList.remove('translate-y-[-20px]', 'opacity-0');
        toast.classList.add('translate-y-0', 'opacity-100');
    });

    // Auto dismiss
    setTimeout(() => {
        toast.classList.add('opacity-0', 'translate-y-[-10px]');
        setTimeout(() => toast.remove(), 300);
    }, duration);
}

/**
 * Abre um modal com animação suave
 * @param {string} modalId 
 */
function openModal(modalId) {
    const modal = document.getElementById(modalId);
    if (!modal) return;

    modal.classList.remove('hidden');
    document.body.classList.add('overflow-hidden');

    const content = modal.querySelector('.modal-box');
    if (content) {
        requestAnimationFrame(() => {
            content.classList.remove('scale-95', 'opacity-0');
            content.classList.add('scale-100', 'opacity-100');
        });
    }
}

/**
 * Fecha um modal com animação suave
 * @param {string} modalId 
 */
function closeModal(modalId) {
    const modal = document.getElementById(modalId);
    if (!modal) return;

    const content = modal.querySelector('.modal-box');
    if (content) {
        content.classList.add('scale-95', 'opacity-0');
        content.classList.remove('scale-100', 'opacity-100');
        setTimeout(() => {
            modal.classList.add('hidden');
            if (document.querySelectorAll('.modal-overlay:not(.hidden)').length === 0) {
                document.body.classList.remove('overflow-hidden');
            }
        }, 200);
    } else {
        modal.classList.add('hidden');
        document.body.classList.remove('overflow-hidden');
    }
}

/**
 * Substitui o confirm() nativo do navegador por um MODAL elegante
 * 
 * @param {string} title Título da ação
 * @param {string} message Mensagem descritiva
 * @param {Function} onConfirm Callback a ser executado caso confirme
 * @param {string} confirmText Texto do botão (default: 'Confirmar')
 * @param {boolean} isDanger Se deve ter estilo perigoso (vermelho)
 */
function customConfirm(title, message, onConfirm, confirmText = 'Confirmar', isDanger = false) {
    let confirmModal = document.getElementById('global-confirm-modal');
    if (!confirmModal) {
        confirmModal = document.createElement('div');
        confirmModal.id = 'global-confirm-modal';
        confirmModal.className = 'modal-overlay fixed inset-0 z-50 flex items-center justify-center p-4 bg-slate-950/80 backdrop-blur-sm hidden transition-opacity duration-200';
        confirmModal.innerHTML = `
            <div class="modal-box bg-slate-850 border border-slate-700/80 rounded-2xl max-w-md w-full p-6 shadow-2xl transform scale-95 opacity-0 transition-all duration-200 bg-slate-900 text-slate-100">
                <div class="flex items-center space-x-3 mb-4">
                    <div id="confirm-modal-icon" class="w-10 h-10 rounded-xl flex items-center justify-center shrink-0"></div>
                    <h3 id="confirm-modal-title" class="text-lg font-bold text-white"></h3>
                </div>
                <p id="confirm-modal-message" class="text-sm text-slate-300 mb-6"></p>
                <div class="flex items-center justify-end space-x-3">
                    <button type="button" id="confirm-modal-cancel" class="px-4 py-2 text-sm font-medium rounded-xl text-slate-300 hover:text-white bg-slate-800 hover:bg-slate-700/80 border border-slate-700 transition">
                        Cancelar
                    </button>
                    <button type="button" id="confirm-modal-btn" class="px-4 py-2 text-sm font-semibold rounded-xl text-white transition shadow-lg">
                        Confirmar
                    </button>
                </div>
            </div>
        `;
        document.body.appendChild(confirmModal);
    }

    const titleEl = document.getElementById('confirm-modal-title');
    const msgEl = document.getElementById('confirm-modal-message');
    const iconEl = document.getElementById('confirm-modal-icon');
    const btnConfirm = document.getElementById('confirm-modal-btn');
    const btnCancel = document.getElementById('confirm-modal-cancel');

    titleEl.textContent = title;
    msgEl.textContent = message;
    btnConfirm.textContent = confirmText;

    if (isDanger) {
        iconEl.className = 'w-10 h-10 rounded-xl flex items-center justify-center shrink-0 bg-rose-500/20 text-rose-400 border border-rose-500/30';
        iconEl.innerHTML = `<svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 7l-.867 12.142A2 2 0 0116.138 21H7.862a2 2 0 01-1.995-1.858L5 7m5 4v6m4-6v6m1-10V4a1 1 0 00-1-1h-4a1 1 0 00-1 1v3M4 7h16"/></svg>`;
        btnConfirm.className = 'px-4 py-2 text-sm font-semibold rounded-xl text-white bg-rose-600 hover:bg-rose-500 transition shadow-lg shadow-rose-600/30';
    } else {
        iconEl.className = 'w-10 h-10 rounded-xl flex items-center justify-center shrink-0 bg-indigo-500/20 text-indigo-400 border border-indigo-500/30';
        iconEl.innerHTML = `<svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M13 16h-1v-4h-1m1-4h.01M21 12a9 9 0 11-18 0 9 9 0 0118 0z"/></svg>`;
        btnConfirm.className = 'px-4 py-2 text-sm font-semibold rounded-xl text-white bg-indigo-600 hover:bg-indigo-500 transition shadow-lg shadow-indigo-600/30';
    }

    const handleConfirm = () => {
        closeModal('global-confirm-modal');
        btnConfirm.removeEventListener('click', handleConfirm);
        if (typeof onConfirm === 'function') {
            onConfirm();
        }
    };

    const handleCancel = () => {
        closeModal('global-confirm-modal');
        btnConfirm.removeEventListener('click', handleConfirm);
    };

    btnConfirm.onclick = handleConfirm;
    btnCancel.onclick = handleCancel;

    openModal('global-confirm-modal');
}
