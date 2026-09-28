-- Agrega campos unificados a cai_boleta_compra para que tenga los mismos campos que cai
-- Fecha: 2025

ALTER TABLE `cai_boleta_compra`
    ADD COLUMN `cantidad_otorgada`   INT UNSIGNED NULL AFTER `cai`,
    ADD COLUMN `cantidad_solicitada` INT UNSIGNED NULL AFTER `cantidad_otorgada`,
    ADD COLUMN `punto_de_emision`    VARCHAR(100) NULL AFTER `cantidad_solicitada`;
