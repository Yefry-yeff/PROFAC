-- Actualiza el modulo Lista de asistencia para produccion.
-- Ejecutar en phpMyAdmin con la base de datos de PROFAC seleccionada.
-- Conserva todos los registros existentes y se puede ejecutar mas de una vez.

SET @base_datos = DATABASE();

SET @sql = IF(
    EXISTS(
        SELECT 1
        FROM information_schema.COLUMNS
        WHERE TABLE_SCHEMA = @base_datos
          AND TABLE_NAME = 'expo_asistencia'
          AND COLUMN_NAME = 'tickets'
    ),
    'SELECT ''La columna tickets ya existe'' AS resultado',
    'ALTER TABLE `expo_asistencia` ADD COLUMN `tickets` INT UNSIGNED NOT NULL DEFAULT 0 AFTER `registrado_por`'
);
PREPARE sentencia FROM @sql;
EXECUTE sentencia;
DEALLOCATE PREPARE sentencia;

SET @sql = IF(
    EXISTS(
        SELECT 1
        FROM information_schema.COLUMNS
        WHERE TABLE_SCHEMA = @base_datos
          AND TABLE_NAME = 'expo_asistencia'
          AND COLUMN_NAME = 'recibio_regalo'
    ),
    'SELECT ''La columna recibio_regalo ya existe'' AS resultado',
    'ALTER TABLE `expo_asistencia` ADD COLUMN `recibio_regalo` TINYINT(1) NOT NULL DEFAULT 0 AFTER `tickets`'
);
PREPARE sentencia FROM @sql;
EXECUTE sentencia;
DEALLOCATE PREPARE sentencia;

SET @sql = IF(
    EXISTS(
        SELECT 1
        FROM information_schema.COLUMNS
        WHERE TABLE_SCHEMA = @base_datos
          AND TABLE_NAME = 'expo_asistencia'
          AND COLUMN_NAME = 'comentario'
    ),
    'SELECT ''La columna comentario ya existe'' AS resultado',
    'ALTER TABLE `expo_asistencia` ADD COLUMN `comentario` VARCHAR(1000) NULL DEFAULT NULL AFTER `recibio_regalo`'
);
PREPARE sentencia FROM @sql;
EXECUTE sentencia;
DEALLOCATE PREPARE sentencia;

-- Evita que Laravel vuelva a ejecutar esta misma migracion al desplegar el codigo.
INSERT INTO `migrations` (`migration`, `batch`)
SELECT
    '2026_08_30_000001_add_tickets_and_gift_to_expo_asistencia',
    (SELECT COALESCE(MAX(`batch`), 0) + 1 FROM `migrations`)
WHERE NOT EXISTS (
    SELECT 1
    FROM `migrations`
    WHERE `migration` = '2026_08_30_000001_add_tickets_and_gift_to_expo_asistencia'
);

INSERT INTO `migrations` (`migration`, `batch`)
SELECT
    '2026_08_30_000002_add_comment_to_expo_asistencia',
    (SELECT COALESCE(MAX(`batch`), 0) + 1 FROM `migrations`)
WHERE NOT EXISTS (
    SELECT 1
    FROM `migrations`
    WHERE `migration` = '2026_08_30_000002_add_comment_to_expo_asistencia'
);

-- Verificacion final.
SELECT
    COLUMN_NAME AS columna,
    COLUMN_TYPE AS tipo,
    IS_NULLABLE AS permite_nulo,
    COLUMN_DEFAULT AS valor_predeterminado
FROM information_schema.COLUMNS
WHERE TABLE_SCHEMA = DATABASE()
  AND TABLE_NAME = 'expo_asistencia'
    AND COLUMN_NAME IN ('tickets', 'recibio_regalo', 'comentario')
ORDER BY ORDINAL_POSITION;
