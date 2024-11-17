Usado PHP 7.4.6 (cli)<br>
Para rodar foi usado o servidor do imbutido do php usando php -S localhost:8000 <br>

Criação das tabelas do banco.<br>

CREATE DATABASE ordem_servico;<br>

USE ordem_servico;<br>

CREATE TABLE clientes (
    id INT AUTO_INCREMENT PRIMARY KEY,
    nome VARCHAR(255) NOT NULL,
    cpf VARCHAR(14) UNIQUE NOT NULL,
    endereco TEXT NOT NULL
);<br>

CREATE TABLE produtos (
    id INT AUTO_INCREMENT PRIMARY KEY,
    codigo VARCHAR(50) UNIQUE NOT NULL,
    descricao VARCHAR(255) NOT NULL,
    status TINYINT(1) DEFAULT 1,
    garantia INT NOT NULL
);<br>

CREATE TABLE ordens (
    id INT AUTO_INCREMENT PRIMARY KEY,
    numero_ordem VARCHAR(50) UNIQUE NOT NULL,
    data_abertura DATE NOT NULL,
    cliente_id INT NOT NULL,
    produto_id INT NOT NULL,
    FOREIGN KEY (cliente_id) REFERENCES clientes(id),
    FOREIGN KEY (produto_id) REFERENCES produtos(id));<br>

    
