
-- ============================================================================
-- INSTALACION COMPLETA - MODULO CARTERA DE CLIENTES
-- MySQL / MariaDB
-- ============================================================================
-- Este archivo incluye todo el esquema adicional requerido por el modulo:
--   1. usuario_rol (roles adicionales de un usuario).
--   2. cliente_usuario (asesores y teleasesores asignados a cada cliente).
--   3. cliente_asesor_auditoria (historial de asignaciones).
--   4. Migracion de cliente.vendedor como Asesor Comercial (rol_id = 2).
--
-- Puede ejecutarse varias veces. No elimina asignaciones ni auditorias previas
-- y no duplica los vendedores que ya fueron migrados.
--
-- IMPORTANTE:
--   - Ejecute primero un respaldo de la base de datos.
--   - Seleccione la base de datos correcta antes de ejecutar este archivo.
--   - Las tablas base users, rol y cliente deben existir.
--   - En esta aplicacion: rol 2 = Asesor Comercial, rol 3 = Tele Asesor.
-- ============================================================================

SET @lote_id = UUID();

-- 1) Limpieza de datos existentes en las tablas del módulo
DELETE FROM cliente_asesor_auditoria;

DROP PROCEDURE IF EXISTS instalar_cartera_clientes;

DELIMITER $$

CREATE PROCEDURE instalar_cartera_clientes()
BEGIN
    DECLARE v_existe INT DEFAULT 0;
    DECLARE v_indice_correcto INT DEFAULT 0;

    -- ------------------------------------------------------------------------
    -- 1. Validaciones de las tablas y catalogos base
    -- ------------------------------------------------------------------------
    SELECT COUNT(*) INTO v_existe
    FROM information_schema.TABLES
    WHERE TABLE_SCHEMA = DATABASE() AND TABLE_NAME = 'users';
    IF v_existe = 0 THEN
        SIGNAL SQLSTATE '45000'
            SET MESSAGE_TEXT = 'No existe la tabla base users en la base seleccionada.';
    END IF;

    SELECT COUNT(*) INTO v_existe
    FROM information_schema.TABLES
    WHERE TABLE_SCHEMA = DATABASE() AND TABLE_NAME = 'rol';
    IF v_existe = 0 THEN
        SIGNAL SQLSTATE '45000'
            SET MESSAGE_TEXT = 'No existe la tabla base rol en la base seleccionada.';
    END IF;

    SELECT COUNT(*) INTO v_existe
    FROM information_schema.TABLES
    WHERE TABLE_SCHEMA = DATABASE() AND TABLE_NAME = 'cliente';
    IF v_existe = 0 THEN
        SIGNAL SQLSTATE '45000'
            SET MESSAGE_TEXT = 'No existe la tabla base cliente en la base seleccionada.';
    END IF;

    SELECT COUNT(*) INTO v_existe
    FROM rol
    WHERE id = @rol_asesor_comercial;
    IF v_existe = 0 THEN
        SIGNAL SQLSTATE '45000'
            SET MESSAGE_TEXT = 'No existe el rol 2 (Asesor Comercial) en la tabla rol.';
    END IF;

    SELECT COUNT(*) INTO v_existe
    FROM rol
    WHERE id = @rol_tele_asesor;
    IF v_existe = 0 THEN
        SIGNAL SQLSTATE '45000'
            SET MESSAGE_TEXT = 'No existe el rol 3 (Tele Asesor) en la tabla rol.';
    END IF;

    -- ------------------------------------------------------------------------
    -- 2. Roles adicionales por usuario
    -- users.rol_id sigue siendo el rol principal. Esta tabla solo almacena
    -- roles adicionales y permite que una persona tenga ambos roles.
    -- ------------------------------------------------------------------------
    CREATE TABLE IF NOT EXISTS usuario_rol (
        id INT UNSIGNED NOT NULL AUTO_INCREMENT,
        usuario_id BIGINT UNSIGNED NOT NULL,
        rol_id INT NOT NULL,
        created_at DATETIME NULL,
        updated_at TIMESTAMP NULL DEFAULT NULL,
        PRIMARY KEY (id),
        UNIQUE KEY uk_usuario_rol (usuario_id, rol_id),
        KEY fk_usuario_rol_usuario_idx (usuario_id),
        KEY fk_usuario_rol_rol_idx (rol_id),
        CONSTRAINT fk_usuario_rol_usuario
            FOREIGN KEY (usuario_id) REFERENCES users (id)
            ON DELETE CASCADE ON UPDATE CASCADE,
        CONSTRAINT fk_usuario_rol_rol
            FOREIGN KEY (rol_id) REFERENCES rol (id)
            ON DELETE CASCADE ON UPDATE CASCADE
    ) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;

    -- ------------------------------------------------------------------------
    -- 3. Asignaciones de usuarios a clientes
    -- rol_id guarda el tipo concreto de asignacion:
    --   2 = Asesor Comercial, 3 = Tele Asesor.
    -- ------------------------------------------------------------------------
    CREATE TABLE IF NOT EXISTS cliente_usuario (
        id BIGINT UNSIGNED NOT NULL AUTO_INCREMENT,
        cliente_id INT NOT NULL,
        usuario_id BIGINT UNSIGNED NOT NULL,
        rol_id BIGINT UNSIGNED NULL,
        fecha_asignacion DATETIME NOT NULL DEFAULT CURRENT_TIMESTAMP,
        asignado_por BIGINT UNSIGNED NULL,
        created_at TIMESTAMP NULL DEFAULT NULL,
        updated_at TIMESTAMP NULL DEFAULT NULL,
        PRIMARY KEY (id),
        KEY cliente_usuario_cliente_id_index (cliente_id),
        KEY cliente_usuario_usuario_id_index (usuario_id),
        UNIQUE KEY cliente_usuario_unique (cliente_id, usuario_id, rol_id),
        CONSTRAINT fk_cliente_usuario_cliente
            FOREIGN KEY (cliente_id) REFERENCES cliente (id) ON DELETE CASCADE,
        CONSTRAINT fk_cliente_usuario_usuario
            FOREIGN KEY (usuario_id) REFERENCES users (id) ON DELETE CASCADE,
        CONSTRAINT fk_cliente_usuario_asignado_por
            FOREIGN KEY (asignado_por) REFERENCES users (id) ON DELETE SET NULL
    ) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;

    -- Actualiza una instalacion anterior en la que cliente_usuario no tenia rol_id.
    SELECT COUNT(*) INTO v_existe
    FROM information_schema.COLUMNS
    WHERE TABLE_SCHEMA = DATABASE()
      AND TABLE_NAME = 'cliente_usuario'
      AND COLUMN_NAME = 'rol_id';

    IF v_existe = 0 THEN
        ALTER TABLE cliente_usuario
            ADD COLUMN rol_id BIGINT UNSIGNED NULL AFTER usuario_id;
    END IF;

    -- Conserva el significado de asignaciones antiguas usando el rol principal
    -- que tenia el usuario antes de habilitar la asignacion multirrol.
    UPDATE cliente_usuario cu
    INNER JOIN users u ON u.id = cu.usuario_id
    SET cu.rol_id = u.rol_id
    WHERE cu.rol_id IS NULL;

    -- La clave debe incluir rol_id para que una misma persona pueda ser asesor
    -- comercial y teleasesor del mismo cliente.
    SELECT COUNT(*) INTO v_indice_correcto
    FROM (
        SELECT INDEX_NAME
        FROM information_schema.STATISTICS
        WHERE TABLE_SCHEMA = DATABASE()
          AND TABLE_NAME = 'cliente_usuario'
          AND INDEX_NAME = 'cliente_usuario_unique'
        GROUP BY INDEX_NAME
        HAVING COUNT(*) = 3
           AND GROUP_CONCAT(COLUMN_NAME ORDER BY SEQ_IN_INDEX)
               = 'cliente_id,usuario_id,rol_id'
    ) indices;

    IF v_indice_correcto = 0 THEN
        SELECT COUNT(*) INTO v_existe
        FROM information_schema.STATISTICS
        WHERE TABLE_SCHEMA = DATABASE()
          AND TABLE_NAME = 'cliente_usuario'
          AND INDEX_NAME = 'cliente_usuario_unique';

        IF v_existe > 0 THEN
            ALTER TABLE cliente_usuario DROP INDEX cliente_usuario_unique;
        END IF;

        ALTER TABLE cliente_usuario
            ADD UNIQUE KEY cliente_usuario_unique
                (cliente_id, usuario_id, rol_id);
    END IF;

    -- ------------------------------------------------------------------------
    -- 4. Historial de cambios de asignacion
    -- ------------------------------------------------------------------------
    CREATE TABLE IF NOT EXISTS cliente_asesor_auditoria (
        id BIGINT UNSIGNED NOT NULL AUTO_INCREMENT,
        cliente_id INT NOT NULL,
        asesor_id BIGINT UNSIGNED NOT NULL,
        tipo VARCHAR(60) NULL,
        accion VARCHAR(20) NOT NULL,
        usuario BIGINT UNSIGNED NULL,
        comentario VARCHAR(255) NULL,
        lote_id VARCHAR(40) NULL,
        fecha TIMESTAMP NOT NULL DEFAULT CURRENT_TIMESTAMP,
        PRIMARY KEY (id),
        KEY cliente_asesor_auditoria_cliente_id_index (cliente_id),
        KEY cliente_asesor_auditoria_asesor_id_index (asesor_id),
        KEY cliente_asesor_auditoria_lote_id_index (lote_id),
        CONSTRAINT fk_cliente_asesor_auditoria_cliente
            FOREIGN KEY (cliente_id) REFERENCES cliente (id) ON DELETE CASCADE,
        CONSTRAINT fk_cliente_asesor_auditoria_asesor
            FOREIGN KEY (asesor_id) REFERENCES users (id) ON DELETE CASCADE,
        CONSTRAINT fk_cliente_asesor_auditoria_usuario
            FOREIGN KEY (usuario) REFERENCES users (id) ON DELETE SET NULL
    ) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;

    -- ------------------------------------------------------------------------
    -- 5. Migracion: cliente.vendedor -> Asesor Comercial
    -- Solo se preparan clientes cuyo vendedor existe realmente en users y cuya
    -- asignacion comercial aun no esta registrada.
    -- ------------------------------------------------------------------------
    DROP TEMPORARY TABLE IF EXISTS tmp_cartera_vendedores;
    CREATE TEMPORARY TABLE tmp_cartera_vendedores (
        cliente_id INT NOT NULL,
        usuario_id BIGINT UNSIGNED NOT NULL,
        PRIMARY KEY (cliente_id, usuario_id)
    ) ENGINE=InnoDB;

    INSERT INTO tmp_cartera_vendedores (cliente_id, usuario_id)
    SELECT c.id, c.vendedor
    FROM cliente c
    INNER JOIN users u ON u.id = c.vendedor
    WHERE c.vendedor IS NOT NULL
      AND NOT EXISTS (
          SELECT 1
          FROM cliente_usuario cu
          WHERE cu.cliente_id = c.id
            AND cu.usuario_id = c.vendedor
            AND cu.rol_id = @rol_asesor_comercial
      );

    INSERT INTO cliente_usuario
        (cliente_id, usuario_id, rol_id, fecha_asignacion,
         asignado_por, created_at, updated_at)
    SELECT cliente_id, usuario_id, @rol_asesor_comercial, NOW(),
           NULL, NOW(), NOW()
    FROM tmp_cartera_vendedores;

    INSERT INTO cliente_asesor_auditoria
        (cliente_id, asesor_id, tipo, accion, usuario,
         comentario, lote_id, fecha)
    SELECT cliente_id, usuario_id, 'Asesor Comercial', 'INSERT', NULL,
           'Migracion inicial desde cliente.vendedor', @lote_id, NOW()
    FROM tmp_cartera_vendedores;

    DROP TEMPORARY TABLE IF EXISTS tmp_cartera_vendedores;
END$$

DELIMITER ;

CALL instalar_cartera_clientes();
DROP PROCEDURE IF EXISTS instalar_cartera_clientes;

-- ============================================================================
-- 6. Verificacion final
-- ============================================================================
SELECT
    (SELECT COUNT(*) FROM cliente) AS total_clientes,
    (SELECT COUNT(*) FROM cliente WHERE vendedor IS NOT NULL)
        AS clientes_con_vendedor,
    (SELECT COUNT(*) FROM cliente_usuario WHERE rol_id = @rol_asesor_comercial)
        AS asignaciones_asesor_comercial,
    (SELECT COUNT(*) FROM cliente_usuario WHERE rol_id = @rol_tele_asesor)
        AS asignaciones_tele_asesor,
    (SELECT COUNT(*) FROM cliente_asesor_auditoria)
        AS registros_auditoria;

-- Vendedores referenciados por cliente que no existen en users. Estas filas no
-- se migran para evitar violar la llave foranea y deben revisarse manualmente.
SELECT c.id AS cliente_id, c.nombre AS cliente, c.vendedor AS usuario_inexistente
FROM cliente c
LEFT JOIN users u ON u.id = c.vendedor
WHERE c.vendedor IS NOT NULL
  AND u.id IS NULL
ORDER BY c.id;

-- Clientes cuyo vendedor legado todavia no quedo asignado como asesor comercial.
-- El resultado esperado despues de una ejecucion correcta es cero filas.
SELECT c.id AS cliente_id, c.nombre AS cliente, c.vendedor
FROM cliente c
INNER JOIN users u ON u.id = c.vendedor
LEFT JOIN cliente_usuario cu
       ON cu.cliente_id = c.id
      AND cu.usuario_id = c.vendedor
      AND cu.rol_id = @rol_asesor_comercial
WHERE c.vendedor IS NOT NULL
  AND cu.id IS NULL
ORDER BY c.id;
DELETE FROM cliente_usuario;
ALTER TABLE cliente_usuario AUTO_INCREMENT = 1;
ALTER TABLE cliente_asesor_auditoria AUTO_INCREMENT = 1;

-- 2) Backfill: cliente.vendedor -> cliente_usuario como Asesor Comercial.
--    rol_id = 2 identifica explícitamente este tipo de asignación.
INSERT INTO cliente_usuario (cliente_id, usuario_id, rol_id, fecha_asignacion, asignado_por, created_at, updated_at)
SELECT c.id, c.vendedor, 2, NOW(), NULL, NOW(), NOW()
FROM cliente c
WHERE c.vendedor IS NOT NULL;

-- 3) Auditoría del alta inicial (todas las filas pertenecen al mismo lote)
INSERT INTO cliente_asesor_auditoria (cliente_id, asesor_id, tipo, accion, usuario, comentario, lote_id, fecha)
SELECT cu.cliente_id, cu.usuario_id, 'Asesor Comercial', 'INSERT', NULL,
       'Migración inicial desde cliente.vendedor', @lote_id, NOW()
FROM cliente_usuario cu
WHERE cu.rol_id = 2;

-- 4) Verificación rápida (solo lectura, informativo)
SELECT (SELECT COUNT(*) FROM cliente_usuario) AS total_cliente_usuario,
       (SELECT COUNT(*) FROM cliente_asesor_auditoria) AS total_auditoria,
       (SELECT COUNT(*) FROM cliente WHERE vendedor IS NOT NULL) AS total_clientes_con_vendedor;
