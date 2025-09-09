SET NAMES utf8mb4;
SET time_zone = '+05:30';

CREATE TABLE IF NOT EXISTS users (
  id INT AUTO_INCREMENT PRIMARY KEY,
  name VARCHAR(150) NOT NULL,
  email VARCHAR(255) NOT NULL UNIQUE,
  password VARCHAR(255) NOT NULL,
  phone VARCHAR(30),
  role ENUM('customer','admin') DEFAULT 'customer',
  created_at DATETIME DEFAULT CURRENT_TIMESTAMP
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

CREATE TABLE IF NOT EXISTS categories (
  id INT AUTO_INCREMENT PRIMARY KEY,
  name VARCHAR(150) NOT NULL,
  slug VARCHAR(160) NOT NULL UNIQUE,
  description TEXT,
  created_at DATETIME DEFAULT CURRENT_TIMESTAMP,
  INDEX (slug)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

CREATE TABLE IF NOT EXISTS products (
  id INT AUTO_INCREMENT PRIMARY KEY,
  sku VARCHAR(64) UNIQUE,
  name VARCHAR(255) NOT NULL,
  slug VARCHAR(255) NOT NULL UNIQUE,
  description TEXT,
  price DECIMAL(10,2) NOT NULL,
  mrp DECIMAL(10,2) DEFAULT NULL,
  stock INT DEFAULT 0,
  category_id INT,
  image VARCHAR(255),
  scent_notes VARCHAR(255),
  is_bestseller TINYINT(1) DEFAULT 0,
  created_at DATETIME DEFAULT CURRENT_TIMESTAMP,
  FOREIGN KEY (category_id) REFERENCES categories(id) ON DELETE SET NULL,
  INDEX (slug), INDEX (name), INDEX (is_bestseller), INDEX (created_at)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

CREATE TABLE IF NOT EXISTS orders (
  id INT AUTO_INCREMENT PRIMARY KEY,
  user_id INT NULL,
  status ENUM('pending','paid','processing','shipped','delivered','canceled') DEFAULT 'pending',
  total_amount DECIMAL(10,2) NOT NULL DEFAULT 0,
  tax_amount DECIMAL(10,2) NOT NULL DEFAULT 0,
  shipping_amount DECIMAL(10,2) NOT NULL DEFAULT 0,
  razorpay_order_id VARCHAR(64),
  razorpay_payment_id VARCHAR(64),
  razorpay_signature VARCHAR(128),
  customer_name VARCHAR(150),
  customer_email VARCHAR(255),
  customer_phone VARCHAR(50),
  shipping_address TEXT,
  created_at DATETIME DEFAULT CURRENT_TIMESTAMP,
  updated_at DATETIME DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
  FOREIGN KEY (user_id) REFERENCES users(id) ON DELETE SET NULL,
  INDEX (status),
  INDEX (created_at)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

CREATE TABLE IF NOT EXISTS order_items (
  id INT AUTO_INCREMENT PRIMARY KEY,
  order_id INT NOT NULL,
  product_id INT NULL,
  product_name VARCHAR(255) NOT NULL,
  unit_price DECIMAL(10,2) NOT NULL,
  quantity INT NOT NULL DEFAULT 1,
  line_total DECIMAL(10,2) NOT NULL,
  FOREIGN KEY (order_id) REFERENCES orders(id) ON DELETE CASCADE,
  FOREIGN KEY (product_id) REFERENCES products(id) ON DELETE SET NULL,
  INDEX (order_id)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

CREATE TABLE IF NOT EXISTS reviews (
  id INT AUTO_INCREMENT PRIMARY KEY,
  product_id INT NOT NULL,
  user_id INT NULL,
  rating TINYINT NOT NULL,
  content TEXT,
  is_approved TINYINT(1) DEFAULT 0,
  created_at DATETIME DEFAULT CURRENT_TIMESTAMP,
  FOREIGN KEY (product_id) REFERENCES products(id) ON DELETE CASCADE,
  FOREIGN KEY (user_id) REFERENCES users(id) ON DELETE SET NULL,
  INDEX (product_id),
  INDEX (is_approved)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

CREATE TABLE IF NOT EXISTS testimonials (
  id INT AUTO_INCREMENT PRIMARY KEY,
  name VARCHAR(150) NOT NULL,
  content TEXT NOT NULL,
  rating TINYINT NOT NULL,
  is_approved TINYINT(1) DEFAULT 0,
  created_at DATETIME DEFAULT CURRENT_TIMESTAMP,
  INDEX (is_approved),
  INDEX (created_at)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

CREATE TABLE IF NOT EXISTS newsletter_subscribers (
  id INT AUTO_INCREMENT PRIMARY KEY,
  email VARCHAR(255) NOT NULL UNIQUE,
  created_at DATETIME DEFAULT CURRENT_TIMESTAMP,
  INDEX (email)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

CREATE TABLE IF NOT EXISTS settings (
  id INT AUTO_INCREMENT PRIMARY KEY,
  `key` VARCHAR(100) NOT NULL UNIQUE,
  `value` TEXT,
  updated_at DATETIME DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
  INDEX (`key`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

CREATE TABLE IF NOT EXISTS admin_logs (
  id INT AUTO_INCREMENT PRIMARY KEY,
  admin_id INT NULL,
  action VARCHAR(150) NOT NULL,
  meta LONGTEXT NULL,
  created_at DATETIME DEFAULT CURRENT_TIMESTAMP,
  FOREIGN KEY (admin_id) REFERENCES users(id) ON DELETE SET NULL,
  INDEX (created_at)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- New: webhook idempotency
CREATE TABLE IF NOT EXISTS webhook_events (
  id INT AUTO_INCREMENT PRIMARY KEY,
  event_id VARCHAR(128) NOT NULL UNIQUE,
  payload LONGTEXT,
  created_at DATETIME DEFAULT CURRENT_TIMESTAMP,
  INDEX (created_at)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- Seed categories
INSERT INTO categories (name, slug, description) VALUES
('Attar', 'attar', 'Alcohol-free attars'),
('Solid Perfume', 'solid-perfume', 'Travel-friendly solid fragrances')
ON DUPLICATE KEY UPDATE name=VALUES(name);

-- Seed products
INSERT INTO products (sku, name, slug, description, price, mrp, stock, category_id, image, scent_notes, is_bestseller)
VALUES
('PRD-001', 'Oud Royale', 'oud-royale', 'Rich resinous Oud with warm spice.', 2499.00, 2999.00, 50, (SELECT id FROM categories WHERE slug='attar'), 'oud-royale.webp', 'Oud, Amber, Spice', 1),
('PRD-002', 'Rose Saffron Attar', 'rose-saffron-attar', 'Delicate rose petals with threads of Kashmiri saffron.', 1799.00, 2199.00, 70, (SELECT id FROM categories WHERE slug='attar'), 'rose-saffron.webp', 'Rose, Saffron, Musk', 1),
('PRD-003', 'Saffron Musk', 'saffron-musk', 'Soft musk wrapped in saffron warmth.', 1999.00, 2299.00, 65, (SELECT id FROM categories WHERE slug='solid-perfume'), 'saffron-musk.webp', 'Saffron, Musk, Vanilla', 0),
('PRD-004', 'Floral Musk', 'floral-musk', 'Powdery florals balanced with smooth musk.', 1899.00, 2199.00, 60, (SELECT id FROM categories WHERE slug='solid-perfume'), 'floral-musk.webp', 'Jasmine, Musk, Powder', 0),
('PRD-005', 'Travel Solid Jar - Musk', 'travel-solid-jar-musk', 'Pocket-size solid jar for on-the-go.', 799.00, 999.00, 120, (SELECT id FROM categories WHERE slug='solid-perfume'), 'travel-jar-musk.webp', 'Musk, Vanilla', 1),
('PRD-006', 'Trial Pack 5x5ml', 'trial-pack-5x5ml', 'Try five signature scents before choosing.', 1299.00, 1499.00, 80, (SELECT id FROM categories WHERE slug='attar'), 'trial-pack.webp', 'Assorted', 1)
ON DUPLICATE KEY UPDATE name=VALUES(name), price=VALUES(price), stock=VALUES(stock), mrp=VALUES(mrp), image=VALUES(image), scent_notes=VALUES(scent_notes), is_bestseller=VALUES(is_bestseller), category_id=VALUES(category_id);

-- Seed testimonials
INSERT INTO testimonials (name, content, rating, is_approved) VALUES
('Aarav', 'Absolutely love the Oud Royale. Long lasting!', 5, 1),
('Diya', 'Rose Saffron is perfect for daily wear.', 4, 1)
ON DUPLICATE KEY UPDATE content=VALUES(content), rating=VALUES(rating), is_approved=VALUES(is_approved);

-- Seed settings (added webhook secret)
INSERT INTO settings (`key`, `value`) VALUES
('site_title', 'PerfumeStore'),
('site_url', 'https://yourdomain.com'),
('tax_percent', '5'),
('shipping_flat', '49'),
('razorpay_key_id', ''),
('razorpay_key_secret', ''),
('razorpay_webhook_secret', ''),
('smtp_host', 'smtp.hostinger.com'),
('smtp_user', 'no-reply@yourdomain.com'),
('smtp_pass', ''),
('smtp_port', '587')
ON DUPLICATE KEY UPDATE `value`=VALUES(`value`);

-- Seed admin user
INSERT INTO users (name, email, password, role) VALUES
('Administrator', 'admin@yourdomain.com', '', 'admin')
ON DUPLICATE KEY UPDATE name=VALUES(name), role=VALUES(role);