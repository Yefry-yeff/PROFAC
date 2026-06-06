-- ============================================================
-- Módulo: Boleta de Compra
-- Descripción: Tablas para el registro manual de boletas de compra
-- ============================================================

CREATE TABLE IF NOT EXISTS `boleta_compra` (
    `id`            INT UNSIGNED AUTO_INCREMENT PRIMARY KEY,
    `numero_boleta` VARCHAR(20)  NOT NULL COMMENT 'Ej: BC-0001',
    `cliente`       VARCHAR(255) NOT NULL,
    `direccion`     VARCHAR(500) NULL,
    `fecha`         DATE         NOT NULL,
    `sub_total`     DECIMAL(10,2) NOT NULL DEFAULT 0.00,
    `total`         DECIMAL(10,2) NOT NULL DEFAULT 0.00,
    `estado`        TINYINT      NOT NULL DEFAULT 1 COMMENT '1=activa, 2=anulada',
    `users_id`      INT UNSIGNED NOT NULL,
    `created_at`    TIMESTAMP    NULL DEFAULT NULL,
    `updated_at`    TIMESTAMP    NULL DEFAULT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

CREATE TABLE IF NOT EXISTS `boleta_compra_detalle` (
    `id`               INT UNSIGNED AUTO_INCREMENT PRIMARY KEY,
    `boleta_compra_id` INT UNSIGNED NOT NULL,
    `linea`            INT          NOT NULL COMMENT 'Número de línea secuencial',
    `descripcion`      VARCHAR(500) NOT NULL,
    `precio`           DECIMAL(10,2) NOT NULL DEFAULT 0.00,
    `cantidad`         DECIMAL(10,2) NOT NULL DEFAULT 0.00,
    `importe`          DECIMAL(10,2) NOT NULL DEFAULT 0.00,
    `created_at`       TIMESTAMP    NULL DEFAULT NULL,
    `updated_at`       TIMESTAMP    NULL DEFAULT NULL,
    CONSTRAINT `fk_bcd_boleta` FOREIGN KEY (`boleta_compra_id`)
        REFERENCES `boleta_compra` (`id`) ON DELETE CASCADE
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;
