CREATE DATABASE IF NOT EXISTS paty_costuras CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci;
USE paty_costuras;

CREATE TABLE IF NOT EXISTS servicos (
 id INT AUTO_INCREMENT PRIMARY KEY,
 nome VARCHAR(120) NOT NULL,
 descricao VARCHAR(255) NOT NULL,
 preco_inicial DECIMAL(10,2) NOT NULL DEFAULT 0,
 unidade VARCHAR(30) DEFAULT 'a partir de',
 ativo TINYINT(1) NOT NULL DEFAULT 1,
 ordem INT NOT NULL DEFAULT 0
);

INSERT INTO servicos (nome, descricao, preco_inicial, unidade, ordem) VALUES
('Barra de calça original','Preservando a barra original quando possível',35.00,'a partir de',1),
('Barra simples','Calças, saias e vestidos',20.00,'a partir de',2),
('Ajuste de cintura','Calças, saias e vestidos',30.00,'a partir de',3),
('Troca de zíper','Valor varia conforme peça e zíper',30.00,'a partir de',4),
('Ajuste de manga','Camisas, vestidos e peças sociais',25.00,'a partir de',5),
('Ajuste de camisa','Laterais e modelagem',35.00,'a partir de',6),
('Ajuste de terno','Calça, paletó e mangas',60.00,'a partir de',7),
('Confecção de vestido','Sob medida, conforme modelo e tecido',0.00,'sob orçamento',8)
ON DUPLICATE KEY UPDATE nome=VALUES(nome);

CREATE TABLE IF NOT EXISTS medidas (
 id INT AUTO_INCREMENT PRIMARY KEY,
 nome VARCHAR(150) NOT NULL,
 telefone VARCHAR(30) NOT NULL,
 tipo_peca VARCHAR(60),
 busto DECIMAL(6,2) NULL,
 cintura DECIMAL(6,2) NULL,
 quadril DECIMAL(6,2) NULL,
 ombro DECIMAL(6,2) NULL,
 manga DECIMAL(6,2) NULL,
 comprimento DECIMAL(6,2) NULL,
 observacoes TEXT,
 criado_em TIMESTAMP DEFAULT CURRENT_TIMESTAMP
);

CREATE TABLE IF NOT EXISTS mensagens_chat (
 id INT AUTO_INCREMENT PRIMARY KEY,
 mensagem TEXT NOT NULL,
 origem VARCHAR(30) DEFAULT 'site',
 criado_em TIMESTAMP DEFAULT CURRENT_TIMESTAMP
);
