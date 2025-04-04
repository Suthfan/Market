-- Create Database
CREATE DATABASE IF NOT EXISTS online_marketplace;
USE online_marketplace;

CREATE TABLE users (
    user_id INT(11) NOT NULL AUTO_INCREMENT,
    username VARCHAR(50) NOT NULL UNIQUE,
    password VARCHAR(255) NOT NULL,
    balance DECIMAL(10,2) DEFAULT 0.00,
    PRIMARY KEY (user_id)
);

CREATE TABLE products (
    id INT(11) NOT NULL AUTO_INCREMENT,
    name VARCHAR(100) NOT NULL,
    description TEXT,
    price DECIMAL(10,2) NOT NULL,
    stock INT(11) NOT NULL,
    seller_id INT(11) NOT NULL,
    PRIMARY KEY (id),
    FOREIGN KEY (seller_id) REFERENCES users(user_id)
        ON DELETE CASCADE
        ON UPDATE CASCADE
);

CREATE TABLE cart (
    id INT(11) NOT NULL AUTO_INCREMENT,
    user_id INT(11) NOT NULL,
    product_id INT(11) NOT NULL,
    quantity INT(11) NOT NULL,
    seller_id INT(11) NOT NULL,
    PRIMARY KEY (id),
    FOREIGN KEY (user_id) REFERENCES users(user_id)
        ON DELETE CASCADE
        ON UPDATE CASCADE,
    FOREIGN KEY (product_id) REFERENCES products(id)
        ON DELETE CASCADE
        ON UPDATE CASCADE,
    FOREIGN KEY (seller_id) REFERENCES users(user_id)
        ON DELETE CASCADE
        ON UPDATE CASCADE
);

CREATE TABLE transactions (
    id INT(11) NOT NULL AUTO_INCREMENT,
    buyer_id INT(11) NOT NULL,
    seller_id INT(11) NOT NULL,
    product_id INT(11) NOT NULL,
    quantity INT(11) NOT NULL,
    total_amount DECIMAL(10,2) NOT NULL,
    transaction_time TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    PRIMARY KEY (id),
    FOREIGN KEY (buyer_id) REFERENCES users(user_id)
        ON DELETE CASCADE
        ON UPDATE CASCADE,
    FOREIGN KEY (seller_id) REFERENCES users(user_id)
        ON DELETE CASCADE
        ON UPDATE CASCADE,
    FOREIGN KEY (product_id) REFERENCES products(id)
        ON DELETE CASCADE
        ON UPDATE CASCADE
);

INSERT INTO users (username, password, balance) VALUES
('john_doe', '$2y$10$M6.NqIjdVnyzjmQChM2a0O9DA7.TA5iTZMIpJ0FZt3Ombycdv0Vqi',1000),  -- Password: 'password123'
('jane_smith', '$2y$10$7jtJwDpTDR8r1.9ONfRb/.Pph5Jw4MfrVgLMMzeH7i2mIFtn4vvhG',4000),  -- Password: 'jane2025'
('alice_williams', '$2y$10$6DkaqfJ1AWZlD7yH2vBq.yYCOuOd9htA9Z1HLmv9lR1I7bb0we7fC',5000),  -- Password: 'alice@123'
('bob_martin', '$2y$10$J8FqZTRKhqbpjxgx7of1jI6mLY5Vxyk7p4D7N5pADgHL6TezTg99G',1030),    -- Password: 'bob2025'
('carol_jones', '$2y$10$MyqX7ZTeTrzlmMdr2Xs76T1I2QU0tV7G4MwE1m7B0NkZvVFl3ueoq',1500); -- Password: 'carol789'

INSERT INTO products (name, description, price, stock) VALUES
('Laptop', '15-inch laptop with 8GB RAM and 512GB SSD', 899.99, 50),
('Smartphone', '6.5-inch display, 128GB storage, 6GB RAM', 599.99, 100),
('Headphones', 'Noise-cancelling over-ear headphones', 149.99, 75),
('Keyboard', 'Mechanical keyboard with RGB backlighting', 99.99, 120),
('Monitor', '27-inch 1080p IPS display', 179.99, 40);

INSERT INTO cart (user_id, product_id, quantity) VALUES
(1, 1, 2),  -- John Doe has 2 Laptops in the cart
(1, 3, 1),  -- John Doe has 1 Headphone in the cart
(2, 2, 1),  -- Jane Smith has 1 Smartphone in the cart
(3, 4, 3),  -- Alice Williams has 3 Keyboards in the cart
(4, 5, 1);  -- Bob Martin has 1 Monitor in the cart
