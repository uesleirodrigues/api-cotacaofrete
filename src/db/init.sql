CREATE DATABASE IF NOT EXISTS api_cotacao_frete;
USE api_cotacao_frete;

CREATE TABLE IF NOT EXISTS quotes (
    id INT AUTO_INCREMENT PRIMARY KEY,
    origin_cep VARCHAR(9),
    destination_cep VARCHAR(9),
    price DECIMAL(10,2),
    delivery_time INT,
    carrier VARCHAR(255),
    service VARCHAR(255),
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP
);

GRANT ALL PRIVILEGES ON api_cotacao_frete.* TO 'admin'@'%' IDENTIFIED BY 'admin';
FLUSH PRIVILEGES;
