CREATE DATABASE IF NOT EXISTS csit314_db;
USE csit314_db;

CREATE TABLE IF NOT EXISTS roles (
  role_id   INT AUTO_INCREMENT PRIMARY KEY,
  role_name VARCHAR(50) UNIQUE NOT NULL
);

CREATE TABLE IF NOT EXISTS user_accounts (
  user_id       INT AUTO_INCREMENT PRIMARY KEY,
  email         VARCHAR(100) UNIQUE NOT NULL,
  password_hash VARCHAR(255) NOT NULL,
  role_id       INT NOT NULL,
  is_active     TINYINT(1) DEFAULT 1,
  FOREIGN KEY (role_id) REFERENCES roles(role_id)
);

INSERT IGNORE INTO roles (role_name) VALUES
  ('User Admin'), ('Customer'), ('Interior Designer'), ('Platform Manager');
