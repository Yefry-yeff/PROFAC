-- Script SQL para crear el sistema de menús dinámicos
-- Ejecutar en este orden

USE profac_app;

-- 1. Crear tabla menu (ya incluye rol_id pero vamos a cambiar la lógica)
CREATE TABLE IF NOT EXISTS `menu` (
  `id` INT NOT NULL AUTO_INCREMENT,
  `icon` VARCHAR(45) NOT NULL,
  `nombre_menu` VARCHAR(45) NOT NULL,
  `orden` INT NOT NULL DEFAULT 0 COMMENT 'Orden de visualización',
  `estado_id` INT NOT NULL DEFAULT 1,
  `created_at` DATETIME NULL DEFAULT NULL,
  `updated_at` TIMESTAMP NULL,
  PRIMARY KEY (`id`),
  INDEX `fk_menu_estado1_idx` (`estado_id` ASC),
  CONSTRAINT `fk_menu_estado1`
    FOREIGN KEY (`estado_id`)
    REFERENCES `estado` (`id`)
    ON DELETE RESTRICT
    ON UPDATE CASCADE
) ENGINE = InnoDB;

-- 2. Crear tabla sub_menu
CREATE TABLE IF NOT EXISTS `sub_menu` (
  `id` INT NOT NULL AUTO_INCREMENT,
  `url` VARCHAR(150) NOT NULL,
  `nombre` VARCHAR(60) NOT NULL,
  `menu_id` INT NOT NULL,
  `orden` INT NOT NULL DEFAULT 0 COMMENT 'Orden de visualización',
  `estado_id` INT NOT NULL DEFAULT 1,
  `icono` VARCHAR(45) NULL COMMENT 'Icono opcional para el submenu',
  `created_at` DATETIME NULL DEFAULT NULL,
  `updated_at` TIMESTAMP NULL,
  PRIMARY KEY (`id`),
  INDEX `fk_sub_menu_menu1_idx` (`menu_id` ASC),
  INDEX `fk_sub_menu_estado1_idx` (`estado_id` ASC),
  CONSTRAINT `fk_sub_menu_menu1`
    FOREIGN KEY (`menu_id`)
    REFERENCES `menu` (`id`)
    ON DELETE CASCADE
    ON UPDATE CASCADE,
  CONSTRAINT `fk_sub_menu_estado1`
    FOREIGN KEY (`estado_id`)
    REFERENCES `estado` (`id`)
    ON DELETE RESTRICT
    ON UPDATE CASCADE
) ENGINE = InnoDB;

-- 3. Crear tabla intermedia rol_submenu (relación muchos a muchos)
CREATE TABLE IF NOT EXISTS `rol_submenu` (
  `id` INT NOT NULL AUTO_INCREMENT,
  `rol_id` INT NOT NULL,
  `sub_menu_id` INT NOT NULL,
  `created_at` DATETIME NULL DEFAULT NULL,
  `updated_at` TIMESTAMP NULL,
  PRIMARY KEY (`id`),
  UNIQUE INDEX `uk_rol_submenu` (`rol_id` ASC, `sub_menu_id` ASC),
  INDEX `fk_rol_submenu_rol1_idx` (`rol_id` ASC),
  INDEX `fk_rol_submenu_submenu1_idx` (`sub_menu_id` ASC),
  CONSTRAINT `fk_rol_submenu_rol1`
    FOREIGN KEY (`rol_id`)
    REFERENCES `rol` (`id`)
    ON DELETE CASCADE
    ON UPDATE CASCADE,
  CONSTRAINT `fk_rol_submenu_submenu1`
    FOREIGN KEY (`sub_menu_id`)
    REFERENCES `sub_menu` (`id`)
    ON DELETE CASCADE
    ON UPDATE CASCADE
) ENGINE = InnoDB;

-- Mensaje de confirmación
SELECT 'Tablas de menú dinámico creadas correctamente' as mensaje;
