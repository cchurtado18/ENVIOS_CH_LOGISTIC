-- Script SQL para corregir la base de datos en producción
-- Ejecutar en el servidor: sudo mysql -u root -p tu_base_de_datos < fix_database_production.sql

-- Verificar y añadir columna metadata si no existe
SET @col_exists = (SELECT COUNT(*) FROM INFORMATION_SCHEMA.COLUMNS 
    WHERE TABLE_SCHEMA = DATABASE() 
    AND TABLE_NAME = 'shipments' 
    AND COLUMN_NAME = 'metadata');

SET @sql = IF(@col_exists = 0, 
    'ALTER TABLE shipments ADD COLUMN metadata JSON NULL AFTER tracking_events',
    'SELECT "Columna metadata ya existe" AS resultado');
PREPARE stmt FROM @sql;
EXECUTE stmt;
DEALLOCATE PREPARE stmt;

-- Verificar y añadir columna internal_status si no existe
SET @col_exists = (SELECT COUNT(*) FROM INFORMATION_SCHEMA.COLUMNS 
    WHERE TABLE_SCHEMA = DATABASE() 
    AND TABLE_NAME = 'shipments' 
    AND COLUMN_NAME = 'internal_status');

SET @sql = IF(@col_exists = 0, 
    'ALTER TABLE shipments ADD COLUMN internal_status VARCHAR(255) NULL AFTER status',
    'SELECT "Columna internal_status ya existe" AS resultado');
PREPARE stmt FROM @sql;
EXECUTE stmt;
DEALLOCATE PREPARE stmt;

-- Verificar y añadir columna user_id si no existe
SET @col_exists = (SELECT COUNT(*) FROM INFORMATION_SCHEMA.COLUMNS 
    WHERE TABLE_SCHEMA = DATABASE() 
    AND TABLE_NAME = 'shipments' 
    AND COLUMN_NAME = 'user_id');

SET @sql = IF(@col_exists = 0, 
    'ALTER TABLE shipments ADD COLUMN user_id BIGINT UNSIGNED NULL AFTER warehouse_id',
    'SELECT "Columna user_id ya existe" AS resultado');
PREPARE stmt FROM @sql;
EXECUTE stmt;
DEALLOCATE PREPARE stmt;

-- Verificar que la tabla pending_trackings existe
CREATE TABLE IF NOT EXISTS pending_trackings (
    id BIGINT UNSIGNED AUTO_INCREMENT PRIMARY KEY,
    user_id BIGINT UNSIGNED NOT NULL,
    tracking_number VARCHAR(255) NOT NULL,
    status VARCHAR(255) NOT NULL DEFAULT 'waiting',
    attempts INT DEFAULT 1,
    found_at TIMESTAMP NULL,
    error_message TEXT NULL,
    created_at TIMESTAMP NULL,
    updated_at TIMESTAMP NULL,
    FOREIGN KEY (user_id) REFERENCES users(id) ON DELETE CASCADE,
    INDEX idx_user_status (user_id, status)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- Verificar que la columna 'role' existe en users
SET @col_exists = (SELECT COUNT(*) FROM INFORMATION_SCHEMA.COLUMNS 
    WHERE TABLE_SCHEMA = DATABASE() 
    AND TABLE_NAME = 'users' 
    AND COLUMN_NAME = 'role');

SET @sql = IF(@col_exists = 0, 
    'ALTER TABLE users ADD COLUMN role VARCHAR(255) DEFAULT "client" AFTER address',
    'SELECT "Columna role ya existe" AS resultado');
PREPARE stmt FROM @sql;
EXECUTE stmt;
DEALLOCATE PREPARE stmt;

-- Verificar que las columnas phone, department, address existen en users
SET @col_exists = (SELECT COUNT(*) FROM INFORMATION_SCHEMA.COLUMNS 
    WHERE TABLE_SCHEMA = DATABASE() 
    AND TABLE_NAME = 'users' 
    AND COLUMN_NAME = 'phone');

SET @sql = IF(@col_exists = 0, 
    'ALTER TABLE users ADD COLUMN phone VARCHAR(20) NULL AFTER email',
    'SELECT "Columna phone ya existe" AS resultado');
PREPARE stmt FROM @sql;
EXECUTE stmt;
DEALLOCATE PREPARE stmt;

SET @col_exists = (SELECT COUNT(*) FROM INFORMATION_SCHEMA.COLUMNS 
    WHERE TABLE_SCHEMA = DATABASE() 
    AND TABLE_NAME = 'users' 
    AND COLUMN_NAME = 'department');

SET @sql = IF(@col_exists = 0, 
    'ALTER TABLE users ADD COLUMN department VARCHAR(255) NULL AFTER phone',
    'SELECT "Columna department ya existe" AS resultado');
PREPARE stmt FROM @sql;
EXECUTE stmt;
DEALLOCATE PREPARE stmt;

SET @col_exists = (SELECT COUNT(*) FROM INFORMATION_SCHEMA.COLUMNS 
    WHERE TABLE_SCHEMA = DATABASE() 
    AND TABLE_NAME = 'users' 
    AND COLUMN_NAME = 'address');

SET @sql = IF(@col_exists = 0, 
    'ALTER TABLE users ADD COLUMN address VARCHAR(500) NULL AFTER department',
    'SELECT "Columna address ya existe" AS resultado');
PREPARE stmt FROM @sql;
EXECUTE stmt;
DEALLOCATE PREPARE stmt;




