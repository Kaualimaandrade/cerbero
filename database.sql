-- ========================================================
-- SISTEMA CERBERO - BANCO DE DADOS OFICIAL
-- ========================================================

CREATE DATABASE IF NOT EXISTS `cerbero` CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci;
USE `cerbero`;

-- 1. Tabela de Usuários
CREATE TABLE IF NOT EXISTS `usuarios` (
  `id` INT AUTO_INCREMENT PRIMARY KEY,
  `nome` VARCHAR(100) NOT NULL,
  `email` VARCHAR(150) NOT NULL UNIQUE,
  `senha` VARCHAR(255) NOT NULL,
  `nivel_acesso` ENUM('Administrador', 'Operador', 'Visualizador') NOT NULL DEFAULT 'Operador',
  `cargo` VARCHAR(100) DEFAULT 'Colaborador',
  `status` ENUM('Ativo', 'Inativo') NOT NULL DEFAULT 'Ativo',
  `criado_em` DATETIME DEFAULT CURRENT_TIMESTAMP,
  `atualizado_em` DATETIME DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;

-- 2. Tabela de Solicitações de Acesso
CREATE TABLE IF NOT EXISTS `solicitacoes_acesso` (
  `id` INT AUTO_INCREMENT PRIMARY KEY,
  `nome` VARCHAR(100) NOT NULL,
  `email` VARCHAR(150) NOT NULL,
  `cargo` VARCHAR(100) NOT NULL,
  `departamento` VARCHAR(100) NOT NULL DEFAULT 'Geral',
  `motivo` TEXT NOT NULL,
  `nivel_solicitado` ENUM('Administrador', 'Operador', 'Visualizador') NOT NULL DEFAULT 'Operador',
  `status` ENUM('Pendente', 'Aprovado', 'Rejeitado') NOT NULL DEFAULT 'Pendente',
  `observacao_resposta` TEXT DEFAULT NULL,
  `respondido_por` INT DEFAULT NULL,
  `data_solicitacao` DATETIME DEFAULT CURRENT_TIMESTAMP,
  `data_resposta` DATETIME DEFAULT NULL,
  FOREIGN KEY (`respondido_por`) REFERENCES `usuarios`(`id`) ON DELETE SET NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;

-- 3. Tabela de Registros do Sistema
CREATE TABLE IF NOT EXISTS `registros` (
  `id` INT AUTO_INCREMENT PRIMARY KEY,
  `titulo` VARCHAR(150) NOT NULL,
  `categoria` VARCHAR(80) NOT NULL,
  `descricao` TEXT NULL,
  `prioridade` ENUM('Baixa', 'Média', 'Alta', 'Crítica') NOT NULL DEFAULT 'Média',
  `status` ENUM('Pendente', 'Em Andamento', 'Concluído', 'Cancelado') NOT NULL DEFAULT 'Pendente',
  `usuario_id` INT NOT NULL,
  `criado_em` DATETIME DEFAULT CURRENT_TIMESTAMP,
  `atualizado_em` DATETIME DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
  FOREIGN KEY (`usuario_id`) REFERENCES `usuarios`(`id`) ON DELETE CASCADE
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;

-- 4. Tabela de Logs e Auditoria
CREATE TABLE IF NOT EXISTS `logs_sistema` (
  `id` INT AUTO_INCREMENT PRIMARY KEY,
  `usuario_id` INT DEFAULT NULL,
  `acao` VARCHAR(120) NOT NULL,
  `detalhes` TEXT DEFAULT NULL,
  `ip` VARCHAR(45) DEFAULT NULL,
  `criado_em` DATETIME DEFAULT CURRENT_TIMESTAMP,
  FOREIGN KEY (`usuario_id`) REFERENCES `usuarios`(`id`) ON DELETE SET NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;

-- ========================================================
-- SEED DATA (DADOS INICIAIS DE DEMONSTRAÇÃO)
-- Senha padrão para todos: admin123 (hash compatível com password_hash)
-- ========================================================

-- Usuários Iniciais
INSERT INTO `usuarios` (`id`, `nome`, `email`, `senha`, `nivel_acesso`, `cargo`, `status`) VALUES
(1, 'Administrador Cerbero', 'admin@cerbero.com', '$2y$10$4y9pB82FhU2KqN00r5kCne95U7/EcmF1GvI7z7v.1uAeqNq8ZJkvy', 'Administrador', 'Gerente de TI & Segurança', 'Ativo'),
(2, 'Kauan Andrade', 'kauan@cerbero.com', '$2y$10$4y9pB82FhU2KqN00r5kCne95U7/EcmF1GvI7z7v.1uAeqNq8ZJkvy', 'Administrador', 'Líder Técnico', 'Ativo'),
(3, 'Mariana Costa', 'mariana@cerbero.com', '$2y$10$4y9pB82FhU2KqN00r5kCne95U7/EcmF1GvI7z7v.1uAeqNq8ZJkvy', 'Operador', 'Analista de Operações', 'Ativo'),
(4, 'Carlos Eduardo', 'carlos@cerbero.com', '$2y$10$4y9pB82FhU2KqN00r5kCne95U7/EcmF1GvI7z7v.1uAeqNq8ZJkvy', 'Visualizador', 'Auditor Interno', 'Ativo');

-- Solicitações de Acesso Pendentes e Histórico
INSERT INTO `solicitacoes_acesso` (`id`, `nome`, `email`, `cargo`, `departamento`, `motivo`, `nivel_solicitado`, `status`, `data_solicitacao`) VALUES
(1, 'Lucas Silveira', 'lucas.silveira@karhub.com', 'Comprador Júnior', 'Compras & Suprimentos', 'Necessito de acesso ao painel para conferir recebimento de lotes e notas fiscais de fornecedores.', 'Operador', 'Pendente', NOW() - INTERVAL 2 HOUR),
(2, 'Beatriz Mendes', 'beatriz.mendes@karhub.com', 'Coordenadora de Logística', 'Logística', 'Acompanhamento diário de status de mercadorias e relatórios de fluxo de estoque.', 'Operador', 'Pendente', NOW() - INTERVAL 5 HOUR),
(3, 'Rodrigo Tavares', 'rodrigo.tavares@karhub.com', 'Estagiário de TI', 'Tecnologia', 'Apoio no suporte de primeiro nível aos operadores do sistema.', 'Visualizador', 'Pendente', NOW() - INTERVAL 1 DAY),
(4, 'Fernanda Lima', 'fernanda.lima@karhub.com', 'Supervisora de Contratos', 'Financeiro', 'Auditoria de contratos de fornecimento.', 'Operador', 'Aprovado', NOW() - INTERVAL 3 DAY);

-- Registros Administrativos do Sistema
INSERT INTO `registros` (`id`, `titulo`, `categoria`, `descricao`, `prioridade`, `status`, `usuario_id`, `criado_em`) VALUES
(1, 'Auditoria de Acesso Q4', 'Segurança', 'Revisão periódica de credenciais e permissões ativas de colaboradores.', 'Alta', 'Em Andamento', 1, NOW() - INTERVAL 4 DAY),
(2, 'Inventário de Servidores de Aplicação', 'Infraestrutura', 'Catalogação e verificação de integridade dos nós de produção e backup.', 'Média', 'Concluído', 1, NOW() - INTERVAL 3 DAY),
(3, 'Integração API KarHub - Recebimento', 'Operações', 'Alinhamento do fluxo de entrada de notas fiscais com sistema de compras.', 'Crítica', 'Em Andamento', 2, NOW() - INTERVAL 2 DAY),
(4, 'Backup Periódico da Base de Dados', 'Manutenção', 'Execução de snapshot completo e verificação de integridade dos hashes.', 'Alta', 'Concluído', 2, NOW() - INTERVAL 1 DAY),
(5, 'Atualização de Políticas de Senhas', 'Segurança', 'Implementação de expiração de senhas a cada 90 dias e autenticação forte.', 'Média', 'Pendente', 3, NOW() - INTERVAL 6 HOUR);

-- Logs Iniciais
INSERT INTO `logs_sistema` (`usuario_id`, `acao`, `detalhes`, `ip`, `criado_em`) VALUES
(1, 'Inicialização do Sistema', 'Banco de dados criado e populado com sucesso.', '127.0.0.1', NOW() - INTERVAL 5 DAY),
(1, 'Login', 'Autenticação bem-sucedida do Administrador.', '127.0.0.1', NOW() - INTERVAL 1 DAY);
