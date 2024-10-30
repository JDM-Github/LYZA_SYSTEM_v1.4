DROP DATABASE IF EXISTS lyza_system;
CREATE DATABASE IF NOT EXISTS lyza_system;
USE lyza_system;

-- SET GLOBAL sql_mode=(SELECT REPLACE(@@sql_mode,'ONLY_FULL_GROUP_BY',''));

CREATE TABLE branch (
    id INT PRIMARY KEY AUTO_INCREMENT,
    branchImg VARCHAR(100) DEFAULT "",
    branchName VARCHAR(100) NOT NULL,
    coordinates VARCHAR(50) DEFAULT "NONE",
    addressLine VARCHAR(255) DEFAULT "",
    city VARCHAR(100) DEFAULT "", 
    province VARCHAR(100) DEFAULT "", 
    operatingHours VARCHAR(50) DEFAULT "",
    createdAt TIMESTAMP DEFAULT CURRENT_TIMESTAMP
);

CREATE TABLE users (
    id INT PRIMARY KEY AUTO_INCREMENT,
    userName VARCHAR(100) NOT NULL UNIQUE,
    firstName VARCHAR(100) NOT NULL,
    lastName VARCHAR(100) NOT NULL,
    email VARCHAR(100) NOT NULL UNIQUE,
    password VARCHAR(100) NOT NULL,
    isAdmin BOOLEAN DEFAULT FALSE,
    assignedBranch INT DEFAULT NULL,
    userStatus ENUM('active', 'disabled', 'removed') NOT NULL DEFAULT 'active',
    createdAt TIMESTAMP DEFAULT CURRENT_TIMESTAMP
);

CREATE TABLE staff (
    id INT PRIMARY KEY AUTO_INCREMENT,
    userId INT NOT NULL,
    createdAt TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    FOREIGN KEY (userId) REFERENCES users(id) ON DELETE CASCADE
);

CREATE TABLE products (
    id INT PRIMARY KEY AUTO_INCREMENT,
    branchId INT NOT NULL,
    barCode VARCHAR(100) DEFAULT "",
    productName VARCHAR(100) NOT NULL,
    productPrice DECIMAL(10, 2) NOT NULL,
    productStock INT NOT NULL DEFAULT 0,
    productCategory VARCHAR(100) NOT NULL,
    productImage VARCHAR(100) DEFAULT "",
    productDescription VARCHAR(100) DEFAULT "",
    isArchived BOOLEAN DEFAULT FALSE,
    createdAt TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    FOREIGN KEY (branchId) REFERENCES branch(id) ON DELETE CASCADE
);

CREATE TABLE productOrdered (
    id INT PRIMARY KEY AUTO_INCREMENT,
    productId INT NOT NULL,
    numberProduct INT NOT NULL,
    createdAt TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    FOREIGN KEY (productId) REFERENCES products(id) ON DELETE CASCADE
);

CREATE TABLE transactions (
    id INT PRIMARY KEY AUTO_INCREMENT,
    productOrderedIds JSON NOT NULL,

    branchId INT NOT NULL,
    staffId INT NOT NULL,
    totalPrice DECIMAL(10, 2) NOT NULL,
    cashPrice DECIMAL(10, 2) NOT NULL,
    changePrice DECIMAL(10, 2) NOT NULL,
    createdAt TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    FOREIGN KEY (branchId) REFERENCES branch(id) ON DELETE CASCADE,
    FOREIGN KEY (staffId) REFERENCES staff(id) ON DELETE CASCADE
);

CREATE TABLE stockHistory (
    id INT PRIMARY KEY AUTO_INCREMENT,
    branchId INT NOT NULL,
    staffId INT NOT NULL,
    productId INT NOT NULL,
    quantity INT NOT NULL,
    createdAt TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    FOREIGN KEY (staffId) REFERENCES users(id) ON DELETE CASCADE,
    FOREIGN KEY (branchId) REFERENCES branch(id) ON DELETE CASCADE,
    FOREIGN KEY (productId) REFERENCES products(id) ON DELETE CASCADE
);

INSERT INTO branch (branchName, branchImg, coordinates, addressLine, city, province, operatingHours) VALUES
('All Branch', 'branch1-view.jpg', 'COORDS1', 'Blk. 12/38 Mountainview Homes, Brgy. San Miguel', 'Sto. Tomas', 'Batangas', '8:00 AM - 8:00 PM'),
('Main Branch', 'branch1-view.jpg', 'COORDS1', 'Blk. 12/38 Mountainview Homes, Brgy. San Miguel', 'Sto. Tomas', 'Batangas', '8:00 AM - 8:00 PM'),
('Downtown Branch', 'branch2-view.jpg', 'COORDS2', '123 Downtown St., Brgy. Central', 'Sto. Tomas', 'Batangas', '9:00 AM - 7:00 PM'),
('Uptown Branch', 'branch3-view.jpg', 'COORDS3', '456 Uptown Ave., Brgy. Northern', 'Sto. Tomas', 'Batangas', '10:00 AM - 6:00 PM');


INSERT INTO users (firstName, lastName, userName, email, password, isAdmin, assignedBranch, userStatus) VALUES
('admin','admin','ADMIN', 'admin@example.com', '12345', TRUE, 1, 'active'),
('staff1','staff','staff1', 'staff1@example.com', '12345', FALSE, 2, 'active'),
('staff2','staff','staff2', 'staff2@example.com', '12345', FALSE, 3, 'disabled'),
('staff3','staff','staff3', 'staff3@example.com', '12345', FALSE, 4, 'removed');

INSERT INTO staff (userId) VALUES
(2), 
(3), 
(4);

INSERT INTO products (branchId, barCode, productName, productPrice, productStock, productCategory, productImage) VALUES
(1, '12345', 'Alaxan', 30, 50, 'Medicine', 'Alaxan.jpeg'),
(2, '23456', 'Biogesic', 50, 30, 'Supplement', 'Biogesic.png');
