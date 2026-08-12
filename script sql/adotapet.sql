
CREATE DATABASE IF NOT EXISTS adotapet_db;
USE adotapet_db;

-- ==========================================
-- 1. TABELA: ONGs
-- ==========================================
CREATE TABLE ongs (
    id INT AUTO_INCREMENT PRIMARY KEY,
    email VARCHAR(255) NOT NULL UNIQUE,
    senha VARCHAR(255) NOT NULL, 
    nome_instituicao VARCHAR(255) NOT NULL,
    cnpj VARCHAR(18) NULL, 
    telefone VARCHAR(20) NOT NULL,
    data_cadastro TIMESTAMP DEFAULT CURRENT_TIMESTAMP
);

-- ==========================================
-- 2. TABELA: ADOTANTES
-- ==========================================
CREATE TABLE adotantes (
    id INT AUTO_INCREMENT PRIMARY KEY,
    email VARCHAR(255) NOT NULL UNIQUE,
    senha VARCHAR(255) NOT NULL,
    nome_completo VARCHAR(255) NOT NULL,
    telefone VARCHAR(20) NOT NULL,
    data_cadastro TIMESTAMP DEFAULT CURRENT_TIMESTAMP
);

-- ==========================================
-- 3. TABELA: ANIMAIS (Cadastro de Animal pela ONG)
-- ==========================================
CREATE TABLE animais (
    id INT AUTO_INCREMENT PRIMARY KEY,
    ong_id INT NOT NULL, -- Vincula o pet à ONG que o cadastrou
    nome VARCHAR(100) NOT NULL,
    especie_raca VARCHAR(150) NOT NULL,
    idade_estimada VARCHAR(50) NOT NULL,
    porte VARCHAR(50) NOT NULL, -- Recebe o valor selecionado no select do formulário
    carteira_vacinacao VARCHAR(100) NOT NULL, -- Situação das vacinas
    foto_url TEXT NOT NULL, -- Link da imagem enviado no input correspondente
    descricao TEXT NOT NULL, -- Breve descrição do temperamento/história
    data_cadastro TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    
    -- Se a ONG for excluída, os animais dela também serão limpos automaticamente
    FOREIGN KEY (ong_id) REFERENCES ongs(id) ON DELETE CASCADE
    
);

-- ==========================================
-- 4. TABELA: QUESTIONÁRIOS
-- ==========================================
CREATE TABLE questionarios (
    id INT AUTO_INCREMENT PRIMARY KEY,
    adotante_id INT NOT NULL, -- Vincula este questionário ao perfil do adotante
    
    
    onde_mora VARCHAR(50) NOT NULL,              
    tem_area_externa BOOLEAN NOT NULL,            
    horas_fora_casa INT NOT NULL,                 
    experiencia VARCHAR(50) NOT NULL,             
    tem_criancas BOOLEAN NOT NULL,                
    tem_outros_animais BOOLEAN NOT NULL,          
    nivel_atividade_fisica VARCHAR(30) NOT NULL,    
    
    data_resposta TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    FOREIGN KEY (adotante_id) REFERENCES adotantes(id) ON DELETE CASCADE
);

-- ==========================================
-- 5. TABELA: CANDIDATURAS
-- ==========================================
CREATE TABLE candidaturas (
    id INT AUTO_INCREMENT PRIMARY KEY,
    adotante_id INT NOT NULL,    -- Quem quer adotar
    animal_id INT NOT NULL,      -- Qual o pet desejado
    questionario_id INT NOT NULL, -- O questionário contendo o perfil desse adotante
    
    status_candidatura VARCHAR(50) DEFAULT 'Pendente', -- 'Pendente', 'Aprovado' ou 'Recusado'
    data_envio TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    
    -- Relacionamentos e chaves estrangeiras
    FOREIGN KEY (adotante_id) REFERENCES adotantes(id) ON DELETE CASCADE,
    FOREIGN KEY (animal_id) REFERENCES animais(id) ON DELETE CASCADE,
    FOREIGN KEY (questionario_id) REFERENCES questionarios(id) ON DELETE CASCADE
);


SET FOREIGN_KEY_CHECKS = 0;

-- 2. Apaga a tabela antiga de 'usuarios' que não é mais usada
DROP TABLE IF EXISTS usuarios;

-- 3. Recria a tabela 'candidaturas' limpa e sem a coluna 'usuario_id'
DROP TABLE IF EXISTS candidaturas;

CREATE TABLE candidaturas (
    id INT AUTO_INCREMENT PRIMARY KEY,
    adotante_id INT NOT NULL,
    animal_id INT NOT NULL,
    questionario_id INT NOT NULL,
    status_candidatura VARCHAR(50) DEFAULT 'Pendente',
    compatibilidade VARCHAR(10) DEFAULT '70%',
    data_envio TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    
    CONSTRAINT fk_candidaturas_adotante FOREIGN KEY (adotante_id) REFERENCES adotantes(id) ON DELETE CASCADE,
    CONSTRAINT fk_candidaturas_animal FOREIGN KEY (animal_id) REFERENCES animais(id) ON DELETE CASCADE,
    CONSTRAINT fk_candidaturas_questionario FOREIGN KEY (questionario_id) REFERENCES questionarios(id) ON DELETE CASCADE
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- 4. Reativa a checagem de chaves estrangeiras
SET FOREIGN_KEY_CHECKS = 1;