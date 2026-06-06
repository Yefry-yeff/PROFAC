-- ============================================================
-- Módulo: Boleta de Compra - Actualizaciones de tablas
-- ============================================================

ALTER TABLE `boleta_compra`
    ADD COLUMN `rtn_dni`       VARCHAR(50)  NULL AFTER `direccion`,
    ADD COLUMN `telefono`      VARCHAR(50)  NULL AFTER `rtn_dni`,
    ADD COLUMN `comentario`    TEXT         NULL AFTER `telefono`,
    ADD COLUMN `cai_boleta_id` INT UNSIGNED NULL AFTER `comentario`;

CREATE TABLE IF NOT EXISTS `cai_boleta_compra` (
    `id`                   INT UNSIGNED AUTO_INCREMENT PRIMARY KEY,
    `cai`                  VARCHAR(100) NOT NULL DEFAULT '' COMMENT 'Código de autorización impresión',
    `numero_inicial`       VARCHAR(25)  NOT NULL,
    `numero_final`         VARCHAR(25)  NOT NULL,
    `prefijo`              VARCHAR(15)  NOT NULL COMMENT 'Prefijo del número, ej: 000-001-11-',
    `contador`             INT UNSIGNED NOT NULL DEFAULT 0 COMMENT 'Último número emitido',
    `fecha_limite_emision` DATE         NOT NULL,
    `estado`               TINYINT      NOT NULL DEFAULT 1 COMMENT '1=activo, 2=inactivo',
    `created_at`           TIMESTAMP    NULL DEFAULT NULL,
    `updated_at`           TIMESTAMP    NULL DEFAULT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- Registro inicial: último número emitido es 000-001-11-00003670
-- El próximo a generar será 000-001-11-00003671
INSERT INTO `cai_boleta_compra`
    (`cai`, `numero_inicial`, `numero_final`, `prefijo`, `contador`, `fecha_limite_emision`, `estado`, `created_at`, `updated_at`)
VALUES
    ('', '000-001-11-00000001', '000-001-11-09999999', '000-001-11-', 3670, '2027-12-31', 1, NOW(), NOW());
