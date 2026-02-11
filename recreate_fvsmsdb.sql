-- DROP and recreate the fvsmsdb database and all tables used by the app
-- Run this in phpMyAdmin or mysql CLI (be careful: this will erase existing data)

DROP DATABASE IF EXISTS fvsmsdb;
CREATE DATABASE fvsmsdb CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci;
USE fvsmsdb;

-- users
CREATE TABLE users (
  id INT NOT NULL AUTO_INCREMENT PRIMARY KEY,
  username VARCHAR(100) NOT NULL UNIQUE,
  password VARCHAR(255) NOT NULL,
  email VARCHAR(150),
  created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;

-- admin table
CREATE TABLE tbladmin (
  id INT NOT NULL AUTO_INCREMENT PRIMARY KEY,
  username VARCHAR(100) NOT NULL UNIQUE,
  password VARCHAR(255) NOT NULL,
  created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;

-- categories & subcategories
CREATE TABLE category (
  id INT NOT NULL AUTO_INCREMENT PRIMARY KEY,
  categoryName VARCHAR(150) NOT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;

CREATE TABLE subcategory (
  id INT NOT NULL AUTO_INCREMENT PRIMARY KEY,
  subcategoryName VARCHAR(150) NOT NULL,
  category INT,
  FOREIGN KEY (category) REFERENCES category(id) ON DELETE SET NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;

-- products (Quantity stored in kg)
CREATE TABLE products (
  id INT NOT NULL AUTO_INCREMENT PRIMARY KEY,
  productName VARCHAR(255) NOT NULL,
  productDescription TEXT,
  productImage1 VARCHAR(255),
  Quantity DECIMAL(10,4) DEFAULT 0,
  productPriceBeforeDiscount DECIMAL(10,2) DEFAULT 0,
  productPrice DECIMAL(10,2) DEFAULT 0,
  productAvailability VARCHAR(50) DEFAULT 'In Stock',
  category INT,
  subCategory INT,
  created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
  FOREIGN KEY (category) REFERENCES category(id) ON DELETE SET NULL,
  FOREIGN KEY (subCategory) REFERENCES subcategory(id) ON DELETE SET NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;

-- addresses table (optional, keep structure compatible with includes)
CREATE TABLE addresses (
  id INT NOT NULL AUTO_INCREMENT PRIMARY KEY,
  userId INT NOT NULL,
  addressLine1 VARCHAR(255),
  addressLine2 VARCHAR(255),
  city VARCHAR(100),
  postcode VARCHAR(50),
  country VARCHAR(100),
  phone VARCHAR(50),
  created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
  FOREIGN KEY (userId) REFERENCES users(id) ON DELETE CASCADE
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;

-- cart table
CREATE TABLE cart (
  id INT NOT NULL AUTO_INCREMENT PRIMARY KEY,
  userId INT NOT NULL,
  productId INT NOT NULL,
  productQty DECIMAL(10,4) DEFAULT 1,
  created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
  FOREIGN KEY (userId) REFERENCES users(id) ON DELETE CASCADE,
  FOREIGN KEY (productId) REFERENCES products(id) ON DELETE CASCADE
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;

-- wishlist
CREATE TABLE wishlist (
  id INT NOT NULL AUTO_INCREMENT PRIMARY KEY,
  userId INT NOT NULL,
  productId INT NOT NULL,
  created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
  FOREIGN KEY (userId) REFERENCES users(id) ON DELETE CASCADE,
  FOREIGN KEY (productId) REFERENCES products(id) ON DELETE CASCADE
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;

-- NOTIFICATION (inventory notifications)
CREATE TABLE NOTIFICATION (
  id INT NOT NULL AUTO_INCREMENT PRIMARY KEY,
  userId INT NOT NULL,
  productId INT NOT NULL,
  qty DECIMAL(10,4) DEFAULT NULL,
  unit VARCHAR(10) DEFAULT 'kg',
  created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
  FOREIGN KEY (userId) REFERENCES users(id) ON DELETE CASCADE,
  FOREIGN KEY (productId) REFERENCES products(id) ON DELETE CASCADE
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;

-- orders and order details (kept minimal since your site uses orders tables)
CREATE TABLE orders (
  id INT NOT NULL AUTO_INCREMENT PRIMARY KEY,
  userId INT NOT NULL,
  paymentMethod VARCHAR(100),
  totalAmount DECIMAL(10,2) DEFAULT 0,
  orderDate TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
  status VARCHAR(100) DEFAULT 'Pending',
  addressId INT DEFAULT NULL,
  FOREIGN KEY (userId) REFERENCES users(id) ON DELETE CASCADE,
  FOREIGN KEY (addressId) REFERENCES addresses(id) ON DELETE SET NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;

CREATE TABLE orderdetails (
  id INT NOT NULL AUTO_INCREMENT PRIMARY KEY,
  orderId INT NOT NULL,
  productId INT NOT NULL,
  quantity DECIMAL(10,4) DEFAULT 0,
  unitPrice DECIMAL(10,2) DEFAULT 0,
  FOREIGN KEY (orderId) REFERENCES orders(id) ON DELETE CASCADE,
  FOREIGN KEY (productId) REFERENCES products(id) ON DELETE CASCADE
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;

CREATE TABLE ordertrackhistory (
  id INT NOT NULL AUTO_INCREMENT PRIMARY KEY,
  orderId INT NOT NULL,
  status VARCHAR(255),
  remark TEXT,
  postingDate TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
  FOREIGN KEY (orderId) REFERENCES orders(id) ON DELETE CASCADE
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;

-- sample inserts
INSERT INTO tbladmin (username, password) VALUES ('admin', MD5('password'));
INSERT INTO users (username, password, email) VALUES ('user1', MD5('password'), 'user1@example.com');

INSERT INTO category (categoryName) VALUES ('Bahan Basah'), ('Bahan Kering');
INSERT INTO subcategory (subcategoryName, category) VALUES ('Basah Sub', 1), ('Kering Sub', 2);

INSERT INTO products (productName, productDescription, productImage1, Quantity, productPriceBeforeDiscount, productPrice, productAvailability, category, subCategory)
VALUES
('Alphonso Mango', 'Sweet mango', 'alphonso.jpg', 1.0000, 420.00, 380.00, 'In Stock', 1, 1),
('Curry Leaves', 'Fresh leaves', 'curry.jpg', 0.1000, 100.00, 80.00, 'In Stock', 2, 2);

-- optional: populate cart/wishlist for the sample user
INSERT INTO cart (userId, productId, productQty) VALUES (1, 1, 0.5);
INSERT INTO wishlist (userId, productId) VALUES (1, 2);

-- final check selects (not required to run)
-- SELECT COUNT(*) FROM products; SELECT COUNT(*) FROM users;

COMMIT;
