-- Script SQL simplificado para agregar estado_id a users
-- Ejecutar este script en la base de datos profac_app

USE profac_app;

-- 1. Asegurarse de que los estados básicos existan
INSERT IGNORE INTO `estado` (`id`, `descripcion`, `created_at`, `updated_at`) VALUES
(1, 'Activo', NOW(), NOW()),
(2, 'Inactivo', NOW(), NOW());

-- 2. Agregar columna estado_id a users
ALTER TABLE `users` 
ADD COLUMN `estado_id` INT NOT NULL DEFAULT 1 AFTER `rol_id`;

-- 3. Actualizar todos los usuarios a estado Activo
UPDATE `users` SET `estado_id` = 1;

-- 4. Agregar índice para mejorar rendimiento
ALTER TABLE `users` 
ADD INDEX `fk_users_estado1_idx` (`estado_id` ASC);

-- 5. Agregar foreign key
ALTER TABLE `users`
ADD CONSTRAINT `fk_users_estado1`
  FOREIGN KEY (`estado_id`)
  REFERENCES `estado` (`id`)
  ON DELETE RESTRICT
  ON UPDATE CASCADE;

-- Verificar
SELECT 'Script ejecutado correctamente' as resultado;
SELECT 'Estados en sistema:' as info;
SELECT * FROM estado;
SELECT 'Total de usuarios:' as info, COUNT(*) as total FROM users;
