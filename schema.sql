-- Run this once in Hostinger's phpMyAdmin (or the hPanel database's SQL tab)
-- against the database you created for this site.

CREATE TABLE IF NOT EXISTS users (
  id INT UNSIGNED AUTO_INCREMENT PRIMARY KEY,
  full_name VARCHAR(150) NOT NULL,
  email VARCHAR(190) NOT NULL UNIQUE,
  phone VARCHAR(30),
  password_hash VARCHAR(255) NOT NULL,
  failed_login_attempts INT UNSIGNED NOT NULL DEFAULT 0,
  locked_until DATETIME NULL,
  created_at DATETIME NOT NULL DEFAULT CURRENT_TIMESTAMP
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;

CREATE TABLE IF NOT EXISTS email_verifications (
  id INT UNSIGNED AUTO_INCREMENT PRIMARY KEY,
  email VARCHAR(190) NOT NULL UNIQUE,
  full_name VARCHAR(150) NOT NULL,
  phone VARCHAR(30),
  password_hash VARCHAR(255) NOT NULL,
  verification_code VARCHAR(10) NOT NULL,
  expires_at DATETIME NOT NULL,
  created_at DATETIME NOT NULL DEFAULT CURRENT_TIMESTAMP,
  verified_at DATETIME NULL,
  INDEX idx_email_verification_expiry (email, expires_at)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;

CREATE TABLE IF NOT EXISTS cart_items (
  id INT UNSIGNED AUTO_INCREMENT PRIMARY KEY,
  user_id INT UNSIGNED NOT NULL,
  item_id VARCHAR(50) NOT NULL,
  item_name VARCHAR(150) NOT NULL,
  price INT UNSIGNED NOT NULL,
  category VARCHAR(50),
  qty INT UNSIGNED NOT NULL DEFAULT 1,
  updated_at DATETIME NOT NULL DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
  UNIQUE KEY user_item (user_id, item_id),
  CONSTRAINT fk_cart_user FOREIGN KEY (user_id) REFERENCES users(id) ON DELETE CASCADE
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;

-- Existing installations already have users.is_admin. Do not run an ALTER
-- here: authenticated administrators are the existing users with is_admin = 1;
-- normal customer accounts remain is_admin = 0.

-- Unified log of every cart order, investment inquiry, event booking and
-- consultation request, for CSV/Excel export and business analysis.
CREATE TABLE IF NOT EXISTS purchase_log (
  id INT UNSIGNED AUTO_INCREMENT PRIMARY KEY,
  type ENUM('order','investment_inquiry','event_booking','consultation_request') NOT NULL,
  customer_name VARCHAR(150),
  customer_email VARCHAR(190),
  customer_phone VARCHAR(30),
  summary TEXT NOT NULL,
  amount INT UNSIGNED NULL,
  user_id INT UNSIGNED NULL,
  created_at DATETIME NOT NULL DEFAULT CURRENT_TIMESTAMP,
  INDEX idx_purchase_log_created (created_at),
  INDEX idx_purchase_log_type (type),
  CONSTRAINT fk_purchase_log_user FOREIGN KEY (user_id) REFERENCES users(id) ON DELETE SET NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;

-- The single source of truth for both online and manually entered orders.
CREATE TABLE IF NOT EXISTS orders (
  id INT UNSIGNED AUTO_INCREMENT PRIMARY KEY,
  reference VARCHAR(64) NOT NULL UNIQUE,
  user_id INT UNSIGNED NULL,
  customer_name VARCHAR(150) NOT NULL,
  customer_email VARCHAR(190) NULL,
  customer_phone VARCHAR(30) NULL,
  delivery_address TEXT NULL,
  subtotal INT UNSIGNED NOT NULL,
  discount INT UNSIGNED NOT NULL DEFAULT 0,
  total INT UNSIGNED NOT NULL,
  currency CHAR(3) NOT NULL DEFAULT 'NGN',
  order_source ENUM('online','phone','whatsapp','walk_in','face_to_face','other') NOT NULL DEFAULT 'online',
  payment_method ENUM('paystack','ussd','bank_transfer','cash','other') NULL,
  payment_reference VARCHAR(120) NULL UNIQUE,
  payment_status ENUM('pending','paid','failed','refunded','expired') NOT NULL DEFAULT 'pending',
  order_status ENUM('pending_payment','confirmed','processing','completed','cancelled') NOT NULL DEFAULT 'pending_payment',
  notes TEXT NULL,
  verified_by INT UNSIGNED NULL,
  verified_at DATETIME NULL,
  created_at DATETIME NOT NULL DEFAULT CURRENT_TIMESTAMP,
  updated_at DATETIME NOT NULL DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
  INDEX idx_orders_created (created_at), INDEX idx_orders_status (payment_status, order_status),
  CONSTRAINT fk_orders_user FOREIGN KEY (user_id) REFERENCES users(id) ON DELETE SET NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;

CREATE TABLE IF NOT EXISTS order_items (
  id INT UNSIGNED AUTO_INCREMENT PRIMARY KEY,
  order_id INT UNSIGNED NOT NULL,
  item_id VARCHAR(50) NOT NULL, item_name VARCHAR(150) NOT NULL,
  unit_price INT UNSIGNED NOT NULL, qty INT UNSIGNED NOT NULL, subtotal INT UNSIGNED NOT NULL,
  CONSTRAINT fk_order_items_order FOREIGN KEY (order_id) REFERENCES orders(id) ON DELETE CASCADE
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;


