-- Criando o banco de dados
CREATE DATABASE IF NOT EXISTS adotapet;
USE adotapet;

-- ============================
-- TABELA ADOTANTES
-- ============================

CREATE TABLE adotantes (
    id_adotante INT AUTO_INCREMENT PRIMARY KEY,
    email VARCHAR(150) NOT NULL UNIQUE,
    senha VARCHAR(255) NOT NULL,
    telefone VARCHAR(20) NOT NULL,
    cidade VARCHAR(100) NOT NULL,
    estado VARCHAR(50) NOT NULL,
    rua VARCHAR(150) NOT NULL,
    numero_residencia VARCHAR(10) NOT NULL,
    data_cadastro TIMESTAMP DEFAULT CURRENT_TIMESTAMP
);

-- ============================
-- TABELA ONGS
-- ============================

CREATE TABLE ongs (
    id_ong INT AUTO_INCREMENT PRIMARY KEY,
    
    -- Dados da ONG
    razao_social VARCHAR(200) NOT NULL,
    nome_fantasia VARCHAR(200) NOT NULL,
    cnpj VARCHAR(18) NOT NULL UNIQUE,
    numero_registro VARCHAR(50) NOT NULL,
    
    -- Dados do responsável legal
    nome_responsavel VARCHAR(200) NOT NULL,
    email_responsavel VARCHAR(150) NOT NULL UNIQUE,
    senha_responsavel VARCHAR(255) NOT NULL,
    telefone_responsavel VARCHAR(20) NOT NULL,
    
    -- Termo de veracidade
    termo_veracidade BOOLEAN NOT NULL DEFAULT FALSE,
    
    data_cadastro TIMESTAMP DEFAULT CURRENT_TIMESTAMP
);