-- Sistema de Gestão para Vendedores Autônomos de Alimentos
-- Banco de Dados: MySQL 8 / MariaDB

SET SQL_MODE = "NO_AUTO_VALUE_ON_ZERO";
START TRANSACTION;
SET time_zone = "+00:00";

-- ============================================
-- USUÁRIOS (Vendedores, Moderadores, Auxiliares)
-- ============================================
CREATE TABLE IF NOT EXISTS usuarios (
  id INT AUTO_INCREMENT PRIMARY KEY,
  nome VARCHAR(100) NOT NULL,
  email VARCHAR(150) UNIQUE NOT NULL,
  senha_hash VARCHAR(255) NULL,
  auth_provider ENUM('email','google') DEFAULT 'email',
  google_id VARCHAR(255) UNIQUE NULL,
  avatar_url VARCHAR(255) NULL,
  tipo_usuario ENUM('vendedor','moderador','auxiliar') DEFAULT 'vendedor',
  ativo TINYINT(1) DEFAULT 1,
  data_cadastro TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
  data_ultima_atividade TIMESTAMP NULL,
  INDEX idx_email (email)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- ============================================
-- PERFIS E PERMISSÕES (RBAC)
-- ============================================
CREATE TABLE IF NOT EXISTS perfis (
  id INT AUTO_INCREMENT PRIMARY KEY,
  nome_perfil VARCHAR(50) UNIQUE NOT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

CREATE TABLE IF NOT EXISTS permissoes (
  id INT AUTO_INCREMENT PRIMARY KEY,
  nome_permissao VARCHAR(50) UNIQUE NOT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

CREATE TABLE IF NOT EXISTS perfis_permissoes (
  id_perfil INT,
  id_permissao INT,
  PRIMARY KEY(id_perfil, id_permissao),
  FOREIGN KEY (id_perfil) REFERENCES perfis(id) ON DELETE CASCADE,
  FOREIGN KEY (id_permissao) REFERENCES permissoes(id) ON DELETE CASCADE
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

CREATE TABLE IF NOT EXISTS usuarios_perfis (
  id_usuario INT,
  id_perfil INT,
  PRIMARY KEY(id_usuario, id_perfil),
  FOREIGN KEY (id_usuario) REFERENCES usuarios(id) ON DELETE CASCADE,
  FOREIGN KEY (id_perfil) REFERENCES perfis(id) ON DELETE CASCADE
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- ============================================
-- ASSINATURAS (SaaS)
-- ============================================
CREATE TABLE IF NOT EXISTS assinaturas (
  id INT AUTO_INCREMENT PRIMARY KEY,
  usuario_id INT UNIQUE NOT NULL,
  status ENUM('pendente','ativo','cancelado','inativo') DEFAULT 'pendente',
  plano VARCHAR(50) DEFAULT 'basico',
  data_inicio DATE DEFAULT (CURDATE()),
  data_fim DATE NULL,
  ativado_por_moderador INT NULL,
  data_ativacao TIMESTAMP NULL,
  FOREIGN KEY (usuario_id) REFERENCES usuarios(id) ON DELETE CASCADE,
  FOREIGN KEY (ativado_por_moderador) REFERENCES usuarios(id) ON DELETE SET NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- ============================================
-- INSUMOS
-- ============================================
CREATE TABLE IF NOT EXISTS insumos (
  id INT AUTO_INCREMENT PRIMARY KEY,
  vendedor_id INT NOT NULL,
  nome VARCHAR(100) NOT NULL,
  unidade_medida VARCHAR(20),
  custo_unitario DECIMAL(10,2),
  estoque_atual DECIMAL(10,3) DEFAULT 0,
  estoque_minimo DECIMAL(10,3) DEFAULT 0,
  fornecedor VARCHAR(100) NULL,
  INDEX idx_vendedor (vendedor_id),
  FOREIGN KEY (vendedor_id) REFERENCES usuarios(id) ON DELETE CASCADE
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- ============================================
-- CATEGORIAS & PRODUTOS
-- ============================================
CREATE TABLE IF NOT EXISTS categorias (
  id INT AUTO_INCREMENT PRIMARY KEY,
  vendedor_id INT NOT NULL,
  nome VARCHAR(50) NOT NULL,
  INDEX idx_vendedor (vendedor_id),
  FOREIGN KEY (vendedor_id) REFERENCES usuarios(id) ON DELETE CASCADE
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

CREATE TABLE IF NOT EXISTS produtos (
  id INT AUTO_INCREMENT PRIMARY KEY,
  vendedor_id INT NOT NULL,
  id_categoria INT NULL,
  nome VARCHAR(100) NOT NULL,
  descricao TEXT NULL,
  preco_venda DECIMAL(10,2),
  custo_producao DECIMAL(10,2) DEFAULT 0,
  estoque_atual INT DEFAULT 0,
  estoque_minimo INT DEFAULT 5,
  url_foto VARCHAR(255) NULL,
  INDEX idx_vendedor (vendedor_id),
  FOREIGN KEY (vendedor_id) REFERENCES usuarios(id) ON DELETE CASCADE,
  FOREIGN KEY (id_categoria) REFERENCES categorias(id) ON DELETE SET NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- ============================================
-- FICHAS TÉCNICAS
-- ============================================
CREATE TABLE IF NOT EXISTS fichas_tecnicas (
  id INT AUTO_INCREMENT PRIMARY KEY,
  id_produto INT UNIQUE,
  data_criacao TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
  custo_total_producao DECIMAL(10,2),
  FOREIGN KEY (id_produto) REFERENCES produtos(id) ON DELETE CASCADE
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

CREATE TABLE IF NOT EXISTS fichas_tecnicas_insumos (
  id_ficha INT,
  id_insumo INT,
  quantidade_necessaria DECIMAL(10,3),
  PRIMARY KEY(id_ficha, id_insumo),
  FOREIGN KEY (id_ficha) REFERENCES fichas_tecnicas(id) ON DELETE CASCADE,
  FOREIGN KEY (id_insumo) REFERENCES insumos(id) ON DELETE CASCADE
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- ============================================
-- MOVIMENTAÇÕES DE ESTOQUE
-- ============================================
CREATE TABLE IF NOT EXISTS movimentacoes_estoque_produtos (
  id BIGINT AUTO_INCREMENT PRIMARY KEY,
  id_produto INT,
  tipo ENUM('entrada','saida','descarte','producao'),
  quantidade INT,
  data TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
  observacao TEXT,
  id_usuario INT,
  FOREIGN KEY (id_produto) REFERENCES produtos(id) ON DELETE CASCADE,
  FOREIGN KEY (id_usuario) REFERENCES usuarios(id) ON DELETE SET NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

CREATE TABLE IF NOT EXISTS movimentacoes_estoque_insumos (
  id BIGINT AUTO_INCREMENT PRIMARY KEY,
  id_insumo INT,
  tipo ENUM('entrada','saida','uso_producao','descarte'),
  quantidade DECIMAL(10,3),
  data TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
  observacao TEXT,
  id_usuario INT,
  FOREIGN KEY (id_insumo) REFERENCES insumos(id) ON DELETE CASCADE,
  FOREIGN KEY (id_usuario) REFERENCES usuarios(id) ON DELETE SET NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- ============================================
-- PEDIDOS & ITENS
-- ============================================
CREATE TABLE IF NOT EXISTS pedidos (
  id INT AUTO_INCREMENT PRIMARY KEY,
  vendedor_id INT NOT NULL,
  nome_cliente VARCHAR(100),
  contato_cliente VARCHAR(50),
  data_pedido TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
  status ENUM('pendente','preparo','enviado','entregue','cancelado') DEFAULT 'pendente',
  valor_total DECIMAL(10,2),
  forma_pagamento VARCHAR(50),
  pago_status TINYINT(1) DEFAULT 0,
  observacoes TEXT,
  INDEX idx_vendedor_status (vendedor_id, status),
  FOREIGN KEY (vendedor_id) REFERENCES usuarios(id) ON DELETE CASCADE
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

CREATE TABLE IF NOT EXISTS itens_pedido (
  id INT AUTO_INCREMENT PRIMARY KEY,
  pedido_id INT,
  produto_id INT,
  quantidade INT,
  preco_unitario DECIMAL(10,2),
  FOREIGN KEY(pedido_id) REFERENCES pedidos(id) ON DELETE CASCADE,
  FOREIGN KEY(produto_id) REFERENCES produtos(id) ON DELETE CASCADE
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- ============================================
-- CUPONS & USO
-- ============================================
CREATE TABLE IF NOT EXISTS cupons_desconto (
  id INT AUTO_INCREMENT PRIMARY KEY,
  vendedor_id INT,
  codigo VARCHAR(50) UNIQUE,
  tipo ENUM('fixo','percentual'),
  valor DECIMAL(10,2),
  data_validade_inicio DATE,
  data_validade_fim DATE,
  limite_uso INT,
  usos_atuais INT DEFAULT 0,
  ativo TINYINT(1) DEFAULT 1,
  FOREIGN KEY (vendedor_id) REFERENCES usuarios(id) ON DELETE CASCADE
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

CREATE TABLE IF NOT EXISTS pedidos_cupons (
  id_pedido INT,
  id_cupom INT,
  PRIMARY KEY(id_pedido, id_cupom),
  FOREIGN KEY (id_pedido) REFERENCES pedidos(id) ON DELETE CASCADE,
  FOREIGN KEY (id_cupom) REFERENCES cupons_desconto(id) ON DELETE CASCADE
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- ============================================
-- DESPESAS
-- ============================================
CREATE TABLE IF NOT EXISTS despesas (
  id INT AUTO_INCREMENT PRIMARY KEY,
  vendedor_id INT,
  descricao VARCHAR(150),
  valor DECIMAL(10,2),
  data_despesa DATE,
  categoria VARCHAR(50),
  FOREIGN KEY (vendedor_id) REFERENCES usuarios(id) ON DELETE CASCADE
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- ============================================
-- LOGS DE AUDITORIA
-- ============================================
CREATE TABLE IF NOT EXISTS logs_auditoria (
  id BIGINT AUTO_INCREMENT PRIMARY KEY,
  id_usuario INT,
  tipo_acao VARCHAR(100),
  objeto_afetado_tipo VARCHAR(50),
  objeto_afetado_id INT,
  detalhes_alteracao JSON,
  data_hora TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
  ip_origem VARCHAR(45),
  user_agent VARCHAR(255),
  FOREIGN KEY (id_usuario) REFERENCES usuarios(id) ON DELETE SET NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

COMMIT;

-- ============================================
-- DADOS INICIAIS (Seed)
-- ============================================

-- Perfis padrão
INSERT INTO perfis (nome_perfil) VALUES 
('administrador'),
('vendedor_completo'),
('vendedor_basico'),
('auxiliar');

-- Permissões padrão
INSERT INTO permissoes (nome_permissao) VALUES
('gerenciar_produtos'),
('gerenciar_insumos'),
('gerenciar_pedidos'),
('gerenciar_cupons'),
('gerenciar_financeiro'),
('gerenciar_fichas_tecnicas'),
('visualizar_relatorios'),
('gerenciar_usuarios'),
('gerenciar_assinaturas'),
('acesso_painel_admin');

-- Vincular perfil administrador a todas as permissões
INSERT INTO perfis_permissoes (id_perfil, id_permissao)
SELECT p.id, perm.id
FROM perfis p, permissoes perm
WHERE p.nome_perfil = 'administrador';

-- Vincular perfil vendedor_completo às permissões básicas
INSERT INTO perfis_permissoes (id_perfil, id_permissao)
SELECT p.id, perm.id
FROM perfis p, permissoes perm
WHERE p.nome_perfil = 'vendedor_completo'
AND perm.nome_permissao IN (
  'gerenciar_produtos', 'gerenciar_insumos', 'gerenciar_pedidos',
  'gerenciar_cupons', 'gerenciar_financeiro', 'gerenciar_fichas_tecnicas',
  'visualizar_relatorios'
);

-- Criar usuário moderador padrão (senha: admin123 - trocar após primeiro login)
INSERT INTO usuarios (nome, email, senha_hash, tipo_usuario, ativo) VALUES
('Administrador', 'admin@sistema.com', '$argon2id$v=19$m=65536,t=4,p=1$ZVdQYkJqRWRtQnBxYzNkNQ$qMhK8vLJXKLqvJKLqvJKLqvJKLqvJKLqvJKLqvJKLqv', 'moderador', 1);
