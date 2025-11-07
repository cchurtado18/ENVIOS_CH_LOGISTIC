-- Script SQL directo para añadir columnas faltantes
-- Ejecutar: sudo mysql -u root -p tu_base_de_datos < fix_database_direct.sql

-- Añadir metadata si no existe
ALTER TABLE shipments 
ADD COLUMN IF NOT EXISTS metadata JSON NULL AFTER tracking_events;

-- Añadir internal_status si no existe  
ALTER TABLE shipments 
ADD COLUMN IF NOT EXISTS internal_status VARCHAR(255) NULL AFTER status;

-- Añadir user_id si no existe
ALTER TABLE shipments 
ADD COLUMN IF NOT EXISTS user_id BIGINT UNSIGNED NULL AFTER warehouse_id;

-- Añadir índice a user_id si no existe
CREATE INDEX IF NOT EXISTS shipments_user_id_index ON shipments(user_id);


