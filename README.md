# Cerbero - Sistema de Gerenciamento e Administração

Sistema web de gerenciamento e administração desenvolvido para a KarHub para organizar operações, usuários, registros operacionais e solicitações de acesso.

---

### Links de Demonstração / Hospedagem
- https://cerbero.infinityfree.io/ (Kauan)
- https://systemcerbero.infinityfreeapp.com/ (Guilherme)
- https://pereixz022.infinityfree.me/ (Heitor)

---

## 🛠️ Stack Tecnológico
- **Backend:** PHP estruturado com sessões nativas (`session_start`).
- **Banco de Dados:** MySQL com PDO e Prepared Statements (proteção contra SQL Injection).
- **Frontend:** HTML5, CSS3, Tailwind CSS (Mobile First) e JavaScript moderno.
- **Componentes:** Notificações TOAST e Modais personalizados (sem alertas nativos do navegador).

---

## 🚀 Funcionalidades Principais (Core)
1. **Total de Usuários Cadastrados:** Métrica em tempo real na Dashboard.
2. **Total de Registros do Sistema:** Acompanhamento de operações e chamados.
3. **Status do Sistema:** Monitoramento de saúde, conexão MySQL, uptime e latência.
4. **Solicitações de Acesso Pendentes:** Fluxo completo para aprovação (com criação automática de credenciais) ou rejeição justificada.
5. **Mensagem de Boas-Vindas Personalizada:** Saudação dinâmica identificando o usuário logado.
6. **Identificação e Nível de Acesso:** Exibição do perfil (Administrador, Operador, Visualizador) com restrição de rotas e permissões.

---

## 🔑 Credenciais Padrão de Acesso

| Perfil | E-mail | Senha |
|---|---|---|
| **Administrador** | `admin@cerbero.com` | `admin123` |
| **Líder Técnico** | `kauan@cerbero.com` | `admin123` |
| **Operador** | `mariana@cerbero.com` | `operador123` |
| **Visualizador** | `carlos@cerbero.com` | `usuario123` |

---

## 📦 Como Instalar e Rodar

### Opção 1: Via Assistente de Instalação (Recomendado)
1. Coloque a pasta do projeto no seu servidor web (ex: `htdocs` no XAMPP ou diretório raiz no InfinityFree).
2. Acesse pelo navegador: `http://localhost/cerbero/setup.php`
3. Preencha os dados de conexão do MySQL e clique em **Criar Banco e Instalar Cerbero**.
4. Acesse `http://localhost/cerbero/` ou `http://localhost/cerbero/login.php`.

### Opção 2: Importação Manual do Script SQL
1. Crie o banco de dados `cerbero` no phpMyAdmin ou MySQL CLI.
2. Importe o arquivo `database.sql`.
3. Ajuste as credenciais no arquivo `config/bd.php` se necessário.
4. Acesse `index.php`.

---

## 📂 Estrutura de Arquivos

```
cerbero/
├── config/
│   └── bd.php                  # Conexão central PDO segura
├── bd.php                      # Arquivo ponte exigido nos requisitos
├── actions/
│   ├── auth_action.php         # Login e solicitação de acesso
│   ├── usuario_action.php      # CRUD de usuários com validações
│   ├── solicitacao_action.php  # Aprovação e recusa de solicitações
│   └── registro_action.php     # CRUD de registros do sistema
├── includes/
│   ├── auth_check.php          # Verificação de sessão e níveis de acesso
│   ├── header.php              # Cabeçalho HTML e Tailwind CSS
│   ├── sidebar.php             # Menu lateral com badge de pendências
│   ├── navbar.php              # Barra superior e boas-vindas
│   ├── footer.php              # Rodapé e fechamento de scripts
│   └── toast.php               # Notificações Toast nativas
├── assets/
│   ├── js/app.js               # Modais e Toasts customizados
│   └── css/custom.css          # Estilos e polimento visual
├── index.php                   # Ponto de entrada (redireciona para login ou dashboard)
├── login.php                   # Login e formulário de Solicitação de Acesso
├── dashboard.php               # Dashboard com todos os requisitos Core
├── usuarios.php                # Gerenciamento de usuários
├── solicitacoes.php            # Gerenciamento de solicitações de acesso
├── registros.php               # Gerenciamento de registros
├── status.php                  # Painel de integridade e logs
├── logout.php                  # Encerramento seguro de sessão
├── setup.php                   # Assistente visual de instalação
├── database.sql                # Script SQL completo com dados de teste
└── requisitos.md               # Especificação oficial do projeto
```
