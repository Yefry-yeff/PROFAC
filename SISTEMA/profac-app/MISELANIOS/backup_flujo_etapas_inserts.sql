-- Backup de datos tabla flujo_etapas
-- Generado: 2026-05-30 09:01:02
SET NAMES utf8mb4;
SET FOREIGN_KEY_CHECKS=0;

TRUNCATE TABLE `flujo_etapas`;

INSERT INTO `flujo_etapas` (`id`, `tipo_tramite_id`, `nombre_display`, `icono`, `orden`, `es_opcional`, `activo`, `updated_by`, `created_at`, `updated_at`) VALUES
(1, 1, 'Pedido', 'fa-shopping-cart', 1, 0, 1, NULL, '2026-05-27 20:25:16', '2026-05-27 20:25:16'),
(2, 2, 'Ofertas', 'fa-tag', 2, 0, 1, NULL, '2026-05-27 20:25:16', '2026-05-27 20:25:16'),
(3, 9, 'Rev. Inventario', 'fa-search', 3, 1, 1, NULL, '2026-05-27 20:25:16', '2026-05-27 20:25:16'),
(4, 10, 'Rev. Crédito', 'fa-credit-card', 4, 1, 1, NULL, '2026-05-27 20:25:16', '2026-05-27 20:25:16'),
(5, 4, 'Prefactura', 'fa-file-o', 5, 0, 1, NULL, '2026-05-27 20:25:16', '2026-05-27 20:25:16'),
(6, 3, 'Factura', 'fa-file-text', 6, 0, 1, NULL, '2026-05-27 20:25:16', '2026-05-27 20:25:16'),
(7, 5, 'Entrega', 'fa-truck', 7, 0, 1, NULL, '2026-05-27 20:25:16', '2026-05-27 20:25:16'),
(8, 6, 'Cobro', 'fa-dollar', 8, 0, 1, NULL, '2026-05-27 20:25:16', '2026-05-27 20:25:16'),
(9, 7, 'Entrega y Cobro', 'fa-handshake-o', 9, 1, 1, NULL, '2026-05-27 20:25:16', '2026-05-27 20:25:16'),
(10, 8, 'Finalizado', 'fa-check-circle', 10, 0, 1, NULL, '2026-05-27 20:25:16', '2026-05-27 20:25:16');

SET FOREIGN_KEY_CHECKS=1;