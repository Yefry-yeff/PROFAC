-- ============================================================================
-- DATOS INICIALES DEL MODULO EXPO - PROFAC
-- Configuracion exportada: Expo_2026
-- Fecha de exportacion: 2026-09-08
--
-- REQUISITO:
--   Ejecutar primero todas las migraciones Expo. Este archivo solo inserta datos.
--
-- INCLUYE:
--   1. Tipo de venta Expo.
--   2. Bodega, segmento y seccion virtual Expo.
--   3. Tramite y etapa "Secciones de Ofertas".
--   4. Menu Expo, reporte BI y permisos para Administrador.
--   5. Expo_2026, bodegas, escalas, usuarios y descuentos vigentes.
--
-- NO INCLUYE:
--   Asistencias, ofertas, prefacturas, facturas ni historicos transaccionales.
--
-- El script se puede ejecutar mas de una vez sin duplicar configuracion.
-- Si falta un catalogo requerido, revierte la transaccion e indica cual falta.
-- ============================================================================

DELIMITER $$

DROP PROCEDURE IF EXISTS instalar_datos_modulo_expo_2026$$

CREATE PROCEDURE instalar_datos_modulo_expo_2026()
BEGIN
    DECLARE v_expo_id BIGINT UNSIGNED DEFAULT NULL;
    DECLARE v_usuario_creador BIGINT UNSIGNED DEFAULT NULL;
    DECLARE v_usuario_actualizador BIGINT UNSIGNED DEFAULT NULL;
    DECLARE v_estado_activo INT DEFAULT NULL;
    DECLARE v_municipio_id INT DEFAULT NULL;
    DECLARE v_rol_admin_id INT DEFAULT NULL;
    DECLARE v_menu_expo_id INT DEFAULT NULL;
    DECLARE v_menu_reportes_id INT DEFAULT NULL;
    DECLARE v_segmento_expo_id BIGINT UNSIGNED DEFAULT NULL;
    DECLARE v_faltantes INT DEFAULT 0;
    DECLARE v_sql_mode TEXT;

    DECLARE EXIT HANDLER FOR SQLEXCEPTION
    BEGIN
        ROLLBACK;
        SET SESSION sql_mode = v_sql_mode;
        RESIGNAL;
    END;

    SET v_sql_mode = @@SESSION.sql_mode;
    START TRANSACTION;

    -- ------------------------------------------------------------------------
    -- Validaciones de catalogos del ambiente destino
    -- ------------------------------------------------------------------------
    SELECT id INTO v_usuario_creador
    FROM users
    WHERE email = 'johann_ruiz14@hotmail.com'
    ORDER BY id
    LIMIT 1;

    SELECT id INTO v_usuario_actualizador
    FROM users
    WHERE email = 'yefryyo@gmail.com'
    ORDER BY id
    LIMIT 1;

    SELECT id INTO v_estado_activo
    FROM estado
    WHERE LOWER(TRIM(descripcion)) = 'activo'
    ORDER BY id
    LIMIT 1;

    SELECT id INTO v_municipio_id
    FROM municipio
    ORDER BY id
    LIMIT 1;

    SELECT id INTO v_rol_admin_id
    FROM rol
    WHERE LOWER(TRIM(nombre)) = 'administrador'
    ORDER BY id
    LIMIT 1;

    IF v_usuario_creador IS NULL THEN
        SIGNAL SQLSTATE '45000'
            SET MESSAGE_TEXT = 'Expo: falta el usuario johann_ruiz14@hotmail.com.';
    END IF;

    IF v_usuario_actualizador IS NULL THEN
        SIGNAL SQLSTATE '45000'
            SET MESSAGE_TEXT = 'Expo: falta el usuario yefryyo@gmail.com.';
    END IF;

    IF v_estado_activo IS NULL OR v_municipio_id IS NULL THEN
        SIGNAL SQLSTATE '45000'
            SET MESSAGE_TEXT = 'Expo: faltan los catalogos estado activo o municipio.';
    END IF;

    IF v_rol_admin_id IS NULL THEN
        SIGNAL SQLSTATE '45000'
            SET MESSAGE_TEXT = 'Expo: falta el rol Administrador.';
    END IF;

    SELECT 15 - COUNT(*) INTO v_faltantes
    FROM bodega
    WHERE nombre IN (
        'ANEXO 5', 'ANEXO 2', 'ANEXO 3', 'ANEXO 4', 'CENTRAL 3',
        'ANEXO 1', 'CENTRAL 2', 'CENTRAL 1', 'BODEGA LA JOYA', 'ALCA',
        'ANEXO 11', 'ANEXO 12', 'ANEXO 13',
        'CENTRO DE DISTRIBUCION VALENCIA COMAYAGUA', 'BODEGA ANEXO 14'
    );

    IF v_faltantes <> 0 THEN
        SIGNAL SQLSTATE '45000'
            SET MESSAGE_TEXT = 'Expo: falta una o mas de las 15 bodegas requeridas.';
    END IF;

    SELECT 9 - COUNT(*) INTO v_faltantes
    FROM categoria_precios
    WHERE nombre IN (
        'ECOBOOKS',
        'MARCAS COMPARTIDAS',
        'MARCAS EXCLUSIVAS',
        'PAPEL BOND OFICIO, LEGAL, TABLOIDE Y ROTAFOLIO 22X34',
        'PAPEL BOND T/CARTA',
        'PRODUCTOS DE CONSUMO',
        'PRODUCTOS DE PAPEL',
        'PRODUCTOS SENSIBLES',
        'SMARTNOTES'
    );

    IF v_faltantes <> 0 THEN
        SIGNAL SQLSTATE '45000'
            SET MESSAGE_TEXT = 'Expo: falta una o mas de las 9 escalas requeridas.';
    END IF;

    SELECT 3 - COUNT(*) INTO v_faltantes
    FROM users
    WHERE email IN (
        'johann_ruiz14@hotmail.com',
        'selenia.merlo@distribucionesvalencia.hn',
        'yefryyo@gmail.com'
    );

    IF v_faltantes <> 0 THEN
        SIGNAL SQLSTATE '45000'
            SET MESSAGE_TEXT = 'Expo: falta uno o mas de los 3 usuarios autorizados.';
    END IF;

    IF EXISTS (
        SELECT 1 FROM tipos_tramites
        WHERE id = 11 AND nombre <> 'Secciones de Ofertas'
    ) THEN
        SIGNAL SQLSTATE '45000'
            SET MESSAGE_TEXT = 'Expo: el tipo de tramite id 11 ya pertenece a otro proceso.';
    END IF;

    IF EXISTS (
        SELECT 1 FROM bodega
        WHERE id = 0 AND nombre <> 'EXPO - DISPONIBLE AGRUPADO'
    ) THEN
        SIGNAL SQLSTATE '45000'
            SET MESSAGE_TEXT = 'Expo: la bodega id 0 ya pertenece a otra ubicacion.';
    END IF;

    -- ------------------------------------------------------------------------
    -- Tipo de venta
    -- ------------------------------------------------------------------------
    INSERT INTO tipo_venta (descripcion, created_at, updated_at)
    SELECT 'Expo', NOW(), NOW()
    WHERE NOT EXISTS (
        SELECT 1 FROM tipo_venta
        WHERE LOWER(TRIM(descripcion)) = 'expo'
    );

    -- ------------------------------------------------------------------------
    -- Bodega virtual para consolidar existencia de las bodegas Expo
    -- ------------------------------------------------------------------------
    IF FIND_IN_SET('NO_AUTO_VALUE_ON_ZERO', @@SESSION.sql_mode) = 0 THEN
        SET SESSION sql_mode = CONCAT_WS(',', @@SESSION.sql_mode, 'NO_AUTO_VALUE_ON_ZERO');
    END IF;

    INSERT INTO bodega (
        id, nombre, direccion, estado_id, municipio_id, encargado_bodega,
        created_at, updated_at
    )
    SELECT
        0, 'EXPO - DISPONIBLE AGRUPADO',
        'Ubicacion virtual para ofertas Expo',
        v_estado_activo, v_municipio_id, v_usuario_creador, NOW(), NOW()
    WHERE NOT EXISTS (SELECT 1 FROM bodega WHERE id = 0);

    INSERT INTO segmento (descripcion, bodega_id, created_at, updated_at)
    SELECT 'EXPO - DISPONIBLE AGRUPADO', 0, NOW(), NOW()
    WHERE NOT EXISTS (
        SELECT 1 FROM segmento
        WHERE bodega_id = 0
          AND descripcion = 'EXPO - DISPONIBLE AGRUPADO'
    );

    SELECT id INTO v_segmento_expo_id
    FROM segmento
    WHERE bodega_id = 0
      AND descripcion = 'EXPO - DISPONIBLE AGRUPADO'
    ORDER BY id
    LIMIT 1;

    INSERT INTO seccion (
        descripcion, numeracion, estado_id, segmento_id, created_at, updated_at
    )
    SELECT
        'EXPO - DISPONIBLE AGRUPADO', 0, v_estado_activo,
        v_segmento_expo_id, NOW(), NOW()
    WHERE NOT EXISTS (
        SELECT 1 FROM seccion
        WHERE segmento_id = v_segmento_expo_id
          AND descripcion = 'EXPO - DISPONIBLE AGRUPADO'
    );

    -- ------------------------------------------------------------------------
    -- Etapa del flujo para seccionar ofertas Expo
    -- ------------------------------------------------------------------------
    INSERT INTO tipos_tramites (
        id, nombre, estado, created_by, updated_by, estado_id, created_at, updated_at
    ) VALUES (
        11, 'Secciones de Ofertas', 'activo', NULL, NULL, NULL, NOW(), NOW()
    )
    ON DUPLICATE KEY UPDATE
        nombre = VALUES(nombre),
        estado = VALUES(estado),
        updated_at = NOW();

    INSERT INTO flujo_etapas (
        tipo_tramite_id, nombre_display, icono, orden,
        es_opcional, activo, updated_by, created_at, updated_at
    )
    SELECT
        11, 'Secciones de Ofertas', 'fa-object-group', 3,
        1, 1, NULL, NOW(), NOW()
    WHERE NOT EXISTS (
        SELECT 1 FROM flujo_etapas WHERE tipo_tramite_id = 11
    );

    UPDATE flujo_etapas
    SET nombre_display = 'Secciones de Ofertas',
        icono = 'fa-object-group',
        orden = 3,
        es_opcional = 1,
        activo = 1,
        updated_at = NOW()
    WHERE tipo_tramite_id = 11;

    UPDATE flujo_etapas SET orden = 4 WHERE tipo_tramite_id = 10;
    UPDATE flujo_etapas SET orden = 5 WHERE tipo_tramite_id = 9;
    UPDATE flujo_etapas SET orden = 6 WHERE tipo_tramite_id = 4;
    UPDATE flujo_etapas SET orden = 7 WHERE tipo_tramite_id = 3;
    UPDATE flujo_etapas SET orden = 8 WHERE tipo_tramite_id = 5;
    UPDATE flujo_etapas SET orden = 9 WHERE tipo_tramite_id = 6;
    UPDATE flujo_etapas SET orden = 10 WHERE tipo_tramite_id = 7;
    UPDATE flujo_etapas SET orden = 11 WHERE tipo_tramite_id = 8;

    -- ------------------------------------------------------------------------
    -- Menu dinamico y accesos del Administrador
    -- ------------------------------------------------------------------------
    INSERT INTO menu (
        icon, nombre_menu, orden, estado_id, created_at, updated_at
    )
    SELECT
        'fa-solid fa-circle-dollar-to-slot', 'Expo', 3,
        v_estado_activo, NOW(), NOW()
    WHERE NOT EXISTS (
        SELECT 1 FROM menu WHERE nombre_menu = 'Expo'
    );

    SELECT id INTO v_menu_expo_id
    FROM menu
    WHERE nombre_menu = 'Expo'
    ORDER BY id
    LIMIT 1;

    INSERT INTO menu (
        icon, nombre_menu, orden, estado_id, created_at, updated_at
    )
    SELECT
        'fa-solid fa-chart-bar', 'Reportes', 29,
        v_estado_activo, NOW(), NOW()
    WHERE NOT EXISTS (
        SELECT 1 FROM menu WHERE nombre_menu = 'Reportes'
    );

    SELECT id INTO v_menu_reportes_id
    FROM menu
    WHERE nombre_menu = 'Reportes'
    ORDER BY id
    LIMIT 1;

    INSERT INTO sub_menu (
        url, nombre, menu_id, orden, estado_id, icono, created_at, updated_at
    )
    SELECT
        'flujo_de_venta/expo', 'Expo', v_menu_expo_id, 6,
        v_estado_activo, 'fa-brands fa-shopify', NOW(), NOW()
    WHERE NOT EXISTS (
        SELECT 1 FROM sub_menu WHERE url = 'flujo_de_venta/expo'
    );

    INSERT INTO sub_menu (
        url, nombre, menu_id, orden, estado_id, icono, created_at, updated_at
    )
    SELECT
        'expo/reporte_de_expo', 'Reporte de Expo', v_menu_expo_id, 2,
        v_estado_activo, 'fa-solid fa-square-poll-vertical', NOW(), NOW()
    WHERE NOT EXISTS (
        SELECT 1 FROM sub_menu WHERE url = 'expo/reporte_de_expo'
    );

    INSERT INTO sub_menu (
        url, nombre, menu_id, orden, estado_id, icono, created_at, updated_at
    )
    SELECT
        'expo/lista_de_asistencia', 'Lista de Asistencia', v_menu_expo_id, 3,
        v_estado_activo, 'fa-solid fa-clipboard', NOW(), NOW()
    WHERE NOT EXISTS (
        SELECT 1 FROM sub_menu WHERE url = 'expo/lista_de_asistencia'
    );

    INSERT INTO sub_menu (
        url, nombre, menu_id, orden, estado_id, icono, created_at, updated_at
    )
    SELECT
        'reportes/reporte_expo', 'Reporte Expo', v_menu_reportes_id, 11,
        v_estado_activo, 'fa-solid fa-barcode', NOW(), NOW()
    WHERE NOT EXISTS (
        SELECT 1 FROM sub_menu WHERE url = 'reportes/reporte_expo'
    );

    UPDATE sub_menu
    SET menu_id = v_menu_expo_id, estado_id = v_estado_activo, updated_at = NOW()
    WHERE url IN (
        'flujo_de_venta/expo',
        'expo/reporte_de_expo',
        'expo/lista_de_asistencia'
    );

    UPDATE sub_menu
    SET menu_id = v_menu_reportes_id, estado_id = v_estado_activo, updated_at = NOW()
    WHERE url = 'reportes/reporte_expo';

    INSERT INTO rol_submenu (rol_id, sub_menu_id, created_at, updated_at)
    SELECT v_rol_admin_id, sm.id, NOW(), NOW()
    FROM sub_menu sm
    WHERE sm.url IN (
        'flujo_de_venta/expo',
        'expo/reporte_de_expo',
        'expo/lista_de_asistencia',
        'reportes/reporte_expo'
    )
      AND NOT EXISTS (
          SELECT 1 FROM rol_submenu rs
          WHERE rs.rol_id = v_rol_admin_id
            AND rs.sub_menu_id = sm.id
      );

    -- ------------------------------------------------------------------------
    -- Expo_2026
    -- ------------------------------------------------------------------------
    IF EXISTS (
        SELECT 1 FROM expo
        WHERE estado = 'Activo'
          AND nombre <> 'Expo_2026'
    ) THEN
        SIGNAL SQLSTATE '45000'
            SET MESSAGE_TEXT = 'Expo: ya existe otra Expo activa. Inactivela antes de continuar.';
    END IF;

    SELECT id INTO v_expo_id
    FROM expo
    WHERE nombre = 'Expo_2026'
    ORDER BY id
    LIMIT 1;

    IF v_expo_id IS NULL THEN
        INSERT INTO expo (
            nombre, descripcion, estado, fecha_inicio, fecha_fin,
            cerrada_por, cerrada_at, motivo_cierre, expo_anterior_id,
            created_by, updated_by, created_at, updated_at
        ) VALUES (
            'Expo_2026', 'Expo de 2026', 'Activo',
            '2026-08-29 00:00:00', '2026-11-04 20:19:00',
            NULL, NULL, NULL, NULL,
            v_usuario_creador, v_usuario_actualizador, NOW(), NOW()
        );
        SET v_expo_id = LAST_INSERT_ID();
    ELSE
        UPDATE expo
        SET descripcion = 'Expo de 2026',
            estado = 'Activo',
            fecha_inicio = '2026-08-29 00:00:00',
            fecha_fin = '2026-11-04 20:19:00',
            cerrada_por = NULL,
            cerrada_at = NULL,
            motivo_cierre = NULL,
            updated_by = v_usuario_actualizador,
            updated_at = NOW()
        WHERE id = v_expo_id;
    END IF;

    -- Bodegas autorizadas.
    INSERT INTO expo_bodega (expo_id, bodega_id, created_at)
    SELECT v_expo_id, b.id, NOW()
    FROM bodega b
    WHERE b.nombre IN (
        'ANEXO 5', 'ANEXO 2', 'ANEXO 3', 'ANEXO 4', 'CENTRAL 3',
        'ANEXO 1', 'CENTRAL 2', 'CENTRAL 1', 'BODEGA LA JOYA', 'ALCA',
        'ANEXO 11', 'ANEXO 12', 'ANEXO 13',
        'CENTRO DE DISTRIBUCION VALENCIA COMAYAGUA', 'BODEGA ANEXO 14'
    )
      AND NOT EXISTS (
          SELECT 1 FROM expo_bodega eb
          WHERE eb.expo_id = v_expo_id
            AND eb.bodega_id = b.id
      );

    -- Escalas de precio autorizadas.
    INSERT INTO expo_escala (expo_id, escala_id, created_at)
    SELECT v_expo_id, cp.id, NOW()
    FROM categoria_precios cp
    WHERE cp.nombre IN (
        'ECOBOOKS',
        'MARCAS COMPARTIDAS',
        'MARCAS EXCLUSIVAS',
        'PAPEL BOND OFICIO, LEGAL, TABLOIDE Y ROTAFOLIO 22X34',
        'PAPEL BOND T/CARTA',
        'PRODUCTOS DE CONSUMO',
        'PRODUCTOS DE PAPEL',
        'PRODUCTOS SENSIBLES',
        'SMARTNOTES'
    )
      AND NOT EXISTS (
          SELECT 1 FROM expo_escala ee
          WHERE ee.expo_id = v_expo_id
            AND ee.escala_id = cp.id
      );

    -- Usuarios autorizados.
    INSERT INTO expo_usuario (expo_id, usuario_id, created_at)
    SELECT v_expo_id, u.id, NOW()
    FROM users u
    WHERE u.email IN (
        'johann_ruiz14@hotmail.com',
        'selenia.merlo@distribucionesvalencia.hn',
        'yefryyo@gmail.com'
    )
      AND NOT EXISTS (
          SELECT 1 FROM expo_usuario eu
          WHERE eu.expo_id = v_expo_id
            AND eu.usuario_id = u.id
      );

    -- Descuentos por escala de precio.
    INSERT INTO expo_descuento_escala (
        expo_id, escala_id, venta_minima, porcentaje_descuento,
        requiere_asistencia, orden, created_at, updated_at
    )
    SELECT
        v_expo_id, cp.id, reglas.venta_minima, reglas.porcentaje,
        reglas.requiere_asistencia, reglas.orden, NOW(), NOW()
    FROM (
        SELECT 'MARCAS EXCLUSIVAS' escala, 1.00 venta_minima, 20.00 porcentaje, 1 requiere_asistencia, 1 orden
        UNION ALL SELECT 'MARCAS EXCLUSIVAS', 500000.00, 25.00, 0, 2
        UNION ALL SELECT 'MARCAS EXCLUSIVAS', 1000000.00, 30.00, 0, 3
        UNION ALL SELECT 'MARCAS EXCLUSIVAS', 2500000.00, 35.00, 0, 4
        UNION ALL SELECT 'MARCAS EXCLUSIVAS', 5000000.00, 40.00, 0, 5
        UNION ALL SELECT 'MARCAS COMPARTIDAS', 1.00, 10.00, 1, 6
        UNION ALL SELECT 'MARCAS COMPARTIDAS', 500000.00, 20.00, 0, 7
        UNION ALL SELECT 'PAPEL BOND OFICIO, LEGAL, TABLOIDE Y ROTAFOLIO 22X34', 1.00, 10.00, 1, 8
        UNION ALL SELECT 'PAPEL BOND OFICIO, LEGAL, TABLOIDE Y ROTAFOLIO 22X34', 500000.00, 20.00, 0, 9
        UNION ALL SELECT 'PAPEL BOND T/CARTA', 1.00, 10.00, 1, 10
        UNION ALL SELECT 'PAPEL BOND T/CARTA', 500000.00, 20.00, 0, 11
        UNION ALL SELECT 'PRODUCTOS DE PAPEL', 1.00, 10.00, 1, 12
        UNION ALL SELECT 'PRODUCTOS DE PAPEL', 500000.00, 20.00, 0, 13
        UNION ALL SELECT 'PRODUCTOS SENSIBLES', 1.00, 10.00, 1, 14
        UNION ALL SELECT 'PRODUCTOS SENSIBLES', 500000.00, 20.00, 0, 15
        UNION ALL SELECT 'PRODUCTOS DE CONSUMO', 1.00, 10.00, 1, 16
        UNION ALL SELECT 'PRODUCTOS DE CONSUMO', 500000.00, 20.00, 0, 17
        UNION ALL SELECT 'SMARTNOTES', 1.00, 5.00, 1, 18
        UNION ALL SELECT 'SMARTNOTES', 500000.00, 10.00, 0, 19
        UNION ALL SELECT 'SMARTNOTES', 1000000.00, 15.00, 0, 20
        UNION ALL SELECT 'SMARTNOTES', 2500000.00, 20.00, 0, 21
        UNION ALL SELECT 'ECOBOOKS', 1.00, 5.00, 1, 22
        UNION ALL SELECT 'ECOBOOKS', 500000.00, 10.00, 0, 23
        UNION ALL SELECT 'ECOBOOKS', 1000000.00, 15.00, 0, 24
        UNION ALL SELECT 'ECOBOOKS', 2500000.00, 20.00, 0, 25
    ) reglas
    INNER JOIN categoria_precios cp ON cp.nombre = reglas.escala
    ON DUPLICATE KEY UPDATE
        porcentaje_descuento = VALUES(porcentaje_descuento),
        requiere_asistencia = VALUES(requiere_asistencia),
        orden = VALUES(orden),
        updated_at = NOW();

    INSERT INTO expo_historial_cambios (
        expo_id, accion, detalle, datos_anteriores,
        datos_nuevos, user_id, created_at
    )
    SELECT
        v_expo_id,
        'IMPORTACION_DATOS',
        'Configuracion Expo_2026 instalada mediante script SQL.',
        NULL,
        JSON_OBJECT(
            'nombre', 'Expo_2026',
            'bodegas', 15,
            'escalas', 9,
            'usuarios', 3,
            'reglas_descuento', 25
        ),
        v_usuario_actualizador,
        NOW()
    WHERE NOT EXISTS (
        SELECT 1 FROM expo_historial_cambios
        WHERE expo_id = v_expo_id
          AND accion = 'IMPORTACION_DATOS'
    );

    SET SESSION sql_mode = v_sql_mode;
    COMMIT;

    SELECT
        e.id AS expo_id,
        e.nombre,
        e.estado,
        (SELECT COUNT(*) FROM expo_bodega eb WHERE eb.expo_id = e.id) AS bodegas,
        (SELECT COUNT(*) FROM expo_escala ee WHERE ee.expo_id = e.id) AS escalas,
        (SELECT COUNT(*) FROM expo_usuario eu WHERE eu.expo_id = e.id) AS usuarios,
        (SELECT COUNT(*) FROM expo_descuento_escala ed WHERE ed.expo_id = e.id) AS reglas_descuento
    FROM expo e
    WHERE e.id = v_expo_id;
END$$

CALL instalar_datos_modulo_expo_2026()$$
DROP PROCEDURE instalar_datos_modulo_expo_2026$$

DELIMITER ;

-- Resultado esperado:
-- Expo_2026 | Activo | 15 bodegas | 9 escalas | 3 usuarios | 25 reglas