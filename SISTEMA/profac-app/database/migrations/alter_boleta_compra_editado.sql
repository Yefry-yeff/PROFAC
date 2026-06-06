-- ============================================================
-- Módulo: Boleta de Compra - Auditoría de edición
-- ============================================================

ALTER TABLE `boleta_compra`
    ADD COLUMN `editado_por` VARCHAR(255) NULL AFTER `comentario`,
    ADD COLUMN `editado_at`  TIMESTAMP    NULL AFTER `editado_por`;
