CREATE DATABASE adotapet;
USE adotapet;

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

CREATE TABLE ongs (
    id_ong INT AUTO_INCREMENT PRIMARY KEY,
    
    razao_social VARCHAR(200) NOT NULL,
    nome_fantasia VARCHAR(200) NOT NULL,
    cnpj VARCHAR(18) NOT NULL UNIQUE,
    numero_registro VARCHAR(50) NOT NULL,
    
    nome_responsavel VARCHAR(200) NOT NULL,
    email_responsavel VARCHAR(150) NOT NULL UNIQUE,
    senha_responsavel VARCHAR(255) NOT NULL,
    telefone_responsavel VARCHAR(20) NOT NULL,
    
    termo_veracidade BOOLEAN NOT NULL DEFAULT FALSE,
    
    data_cadastro TIMESTAMP DEFAULT CURRENT_TIMESTAMP
);

CREATE TABLE cachorros (
    id_cachorro INT AUTO_INCREMENT PRIMARY KEY,
    id_ong INT NOT NULL,
    nome VARCHAR(100) NOT NULL,
    raca VARCHAR(100) NOT NULL,
    genero ENUM('Macho', 'Femea') NOT NULL,
    porte ENUM('Pequeno', 'Medio', 'Grande') NOT NULL,
    temperamento VARCHAR(200) NOT NULL,
    idade INT NOT NULL COMMENT 'Idade em meses',
    cidade VARCHAR(100) NOT NULL,
    estado VARCHAR(50) NOT NULL,
    rua VARCHAR(150) NOT NULL,
    numero VARCHAR(10) NOT NULL,
    descricao TEXT,
    data_cadastro TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    CONSTRAINT fk_cachorro_ong FOREIGN KEY (id_ong) REFERENCES ongs(id_ong)
        ON DELETE CASCADE ON UPDATE CASCADE
);

-- PASSO 6: criar a tabela gatos
CREATE TABLE gatos (
    id_gato INT AUTO_INCREMENT PRIMARY KEY,
    id_ong INT NOT NULL,
    nome VARCHAR(100) NOT NULL,
    raca VARCHAR(100) NOT NULL,
    genero ENUM('Macho', 'Femea') NOT NULL,
    porte ENUM('Pequeno', 'Medio', 'Grande') NOT NULL,
    temperamento VARCHAR(200) NOT NULL,
    idade INT NOT NULL COMMENT 'Idade em meses',
    cidade VARCHAR(100) NOT NULL,
    estado VARCHAR(50) NOT NULL,
    rua VARCHAR(150) NOT NULL,
    numero VARCHAR(10) NOT NULL,
    descricao TEXT,
    data_cadastro TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    CONSTRAINT fk_gato_ong FOREIGN KEY (id_ong) REFERENCES ongs(id_ong)
        ON DELETE CASCADE ON UPDATE CASCADE
);