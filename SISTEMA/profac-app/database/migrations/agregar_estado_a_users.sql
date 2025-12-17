-- Script SQL para agregar el campo estado_id a la tabla users
-- La tabla estado ya existe en la base de datos

-- 1. Insertar estados por defecto si no existen
INSERT IGNORE INTO `estado` (`id`, `descripcion`, `created_at`, `updated_at`) VALUES
(1, 'Activo', NOW(), NOW()),
(2, 'Inactivo', NOW(), NOW());

-- 2. Verificar si la columna estado_id ya existe
SET @col_exists = (SELECT COUNT(*) 
                   FROM INFORMATION_SCHEMA.COLUMNS 
                   WHERE TABLE_SCHEMA = 'profac_app' 
                   AND TABLE_NAME = 'users' 
                   AND COLUMN_NAME = 'estado_id');

-- 3. Agregar columna estado_id a la tabla users (si no existe)
SET @sql = IF(@col_exists = 0,
    'ALTER TABLE `users` ADD COLUMN `estado_id` INT NOT NULL DEFAULT 1 AFTER `rol_id`',
    'SELECT "La columna estado_id ya existe" as mensaje');
PREPARE stmt FROM @sql;
EXECUTE stmt;
DEALLOCATE PREPARE stmt;

-- 4. Actualizar todos los usuarios existentes a estado Activo (1)
UPDATE `users` SET `estado_id` = 1 WHERE `estado_id` IS NULL OR `estado_id` = 0;

-- 5. Verificar si la foreign key ya existe antes de crearla
SET @fk_exists = (SELECT COUNT(*) 
                  FROM INFORMATION_SCHEMA.TABLE_CONSTRAINTS 
                  WHERE TABLE_SCHEMA = 'profac_app' 
                  AND TABLE_NAME = 'users' 
                  AND CONSTRAINT_NAME = 'fk_users_estado1');

-- 6. Agregar foreign key si no existe
SET @sql = IF(@fk_exists = 0,
    'ALTER TABLE `users` ADD CONSTRAINT `fk_users_estado1`
      FOREIGN KEY (`estado_id`) REFERENCES `estado` (`id`)
      ON DELETE RESTRICT ON UPDATE CASCADE',
    'SELECT "La foreign key fk_users_estado1 ya existe" as mensaje');
PREPARE stmt FROM @sql;
EXECUTE stmt;
DEALLOCATE PREPARE stmt;

-- 7. Agregar índice para mejorar el rendimiento
SET @idx_exists = (SELECT COUNT(*) 
                   FROM INFORMATION_SCHEMA.STATISTICS 
                   WHERE TABLE_SCHEMA = 'profac_app' 
                   AND TABLE_NAME = 'users' 
                   AND INDEX_NAME = 'fk_users_estado1_idx');

SET @sql = IF(@idx_exists = 0,
    'ALTER TABLE `users` ADD INDEX `fk_users_estado1_idx` (`estado_id` ASC)',
    'SELECT "El índice fk_users_estado1_idx ya existe" as mensaje');
PREPARE stmt FROM @sql;
EXECUTE stmt;
DEALLOCATE PREPARE stmt;

-- Verificar cambios
SELECT 'Estados disponibles:' as mensaje;
SELECT * FROM estado;

SELECT 'Usuarios actualizados:' as mensaje;
SELECT COUNT(*) as total_usuarios, 
       SUM(CASE WHEN estado_id = 1 THEN 1 ELSE 0 END) as activos,
       SUM(CASE WHEN estado_id = 2 THEN 1 ELSE 0 END) as inactivos
FROM users;
