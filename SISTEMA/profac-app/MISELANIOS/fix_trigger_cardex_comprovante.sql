-- Corregir el trigger para comprobante de entrega
-- El problema es que el SELECT tiene 14 columnas pero el INSERT solo especifica 12

DROP TRIGGER IF EXISTS trg_after_insert_log_translado_unificado;

DELIMITER $$

CREATE TRIGGER trg_after_insert_log_translado_unificado
AFTER INSERT ON log_translado
FOR EACH ROW
BEGIN
    -- Trigger para ajuste
    IF NEW.descripcion like '%Ajuste%' THEN
        INSERT INTO cardex (
            fecha_creacion,
            producto,
            id_producto,
            ajuste_cod,
            ajuste,
            descripcion,
            id_Bodega_origen,
            Bodega_origen_nombre,
            id_segmento_origen,
            segmento_origen_nombre,
            id_seccion_origen,
            seccion_origen_nombre,
            cantidad,
            usuario
        )
        SELECT DISTINCT
            NEW.created_at creacion,
            p.nombre nombre,
            p.id idproducto,
            a.numero_ajuste,
            a.id idajuste,
            IF(ap.tipo_aritmetica = 1, CONCAT(NEW.descripcion, ' (+)'), CONCAT(NEW.descripcion, ' (-)')) descripcion,
            b.id idbodega,
            b.nombre,
            sg.id,
            sg.descripcion,
            s.id idseccion,
            s.descripcion,
            ap.cantidad_total cantidad,
            u.name usuario
        FROM ajuste a
        INNER JOIN ajuste_has_producto ap ON ap.ajuste_id = a.id
        INNER JOIN recibido_bodega rb ON ap.recibido_bodega_id = rb.id
        INNER JOIN producto p ON p.id = ap.producto_id
        INNER JOIN seccion s ON s.id = rb.seccion_id
        INNER JOIN segmento sg ON sg.id = s.segmento_id
        INNER JOIN bodega b ON b.id = sg.bodega_id
        INNER JOIN users u ON u.id = NEW.users_id
        WHERE a.id = NEW.ajuste_id;
    END IF;

    -- Trigger para comprobante de entrega anulado
    IF NEW.descripcion = 'Orden de Entrega - Anulado' AND NEW.destino IS NOT NULL THEN
        INSERT INTO cardex (
            fecha_creacion,
            producto,
            id_producto,
            numero_comprobante,
            comprobante,
            descripcion,
            id_bodega_destino,
            Bodega_destino_nombre,
            id_segmento_destino,
            segmento_destino_nombre,
            id_seccion_destino,
            seccion_destino_nombre,
            cantidad,
            usuario
        )
        SELECT DISTINCT
            NEW.created_at creacion,
            p.nombre nombre,
            p.id idproducto,
            ce.numero_comprovante,
            ce.id idcomprobanteentrega,
            CONCAT(NEW.descripcion, ' (+)') descripcion,
            b.id idbodega,
            b.nombre,
            sg.id,
            sg.descripcion,
            s.id idseccion,
            s.descripcion,
            NEW.cantidad cantidad,
            u.name usuario
        FROM comprovante_entrega ce
        INNER JOIN comprovante_has_producto cp ON cp.lote_id = NEW.destino AND cp.comprovante_id = NEW.comprovante_entrega_id
        INNER JOIN recibido_bodega rb ON cp.lote_id = rb.id
        INNER JOIN producto p ON p.id = rb.producto_id
        INNER JOIN seccion s ON s.id = rb.seccion_id
        INNER JOIN segmento sg ON sg.id = s.segmento_id
        INNER JOIN bodega b ON b.id = sg.bodega_id
        INNER JOIN users u ON u.id = NEW.users_id
        WHERE ce.id = NEW.comprovante_entrega_id;
    END IF;

    -- Trigger para comprobante de entrega (CORREGIDO)
    IF NEW.descripcion = 'Orden de Entrega' AND NEW.destino IS NULL THEN
        INSERT INTO cardex (
            fecha_creacion,
            producto,
            id_producto,
            numero_comprobante,
            comprobante,
            descripcion,
            id_Bodega_origen,
            Bodega_origen_nombre,
            id_segmento_origen,
            segmento_origen_nombre,
            id_seccion_origen,
            seccion_origen_nombre,
            cantidad,
            usuario
        )
        SELECT DISTINCT
            NEW.created_at creacion,
            p.nombre nombre,
            p.id idproducto,
            ce.numero_comprovante,
            ce.id idcomprobanteentrega,
            CONCAT(NEW.descripcion, ' (-)') descripcion,
            b.id idbodega,
            b.nombre,
            sg.id,
            sg.descripcion,
            s.id idseccion,
            s.descripcion,
            NEW.cantidad cantidad,
            u.name usuario
        FROM comprovante_entrega ce
        INNER JOIN comprovante_has_producto cp ON cp.lote_id = NEW.origen AND cp.comprovante_id = NEW.comprovante_entrega_id
        INNER JOIN recibido_bodega rb ON cp.lote_id = rb.id
        INNER JOIN producto p ON p.id = rb.producto_id
        INNER JOIN seccion s ON s.id = rb.seccion_id
        INNER JOIN segmento sg ON sg.id = s.segmento_id
        INNER JOIN bodega b ON b.id = sg.bodega_id
        INNER JOIN users u ON u.id = NEW.users_id
        WHERE ce.id = NEW.comprovante_entrega_id;
    END IF;

    -- Trigger para factura anulada
    IF NEW.descripcion = 'Factura Anulada' AND NEW.destino IS NOT NULL THEN
        INSERT INTO cardex (
            fecha_creacion,
            producto,
            id_producto,
            numero_factura,
            id_factura,
            descripcion,
            id_bodega_destino,
            Bodega_destino_nombre,
            id_segmento_destino,
            segmento_destino_nombre,
            id_seccion_destino,
            seccion_destino_nombre,
            cantidad,
            usuario
        )
        SELECT DISTINCT
            NEW.created_at AS fecha_creacion,
            p.nombre AS producto,
            p.id AS id_producto,
            f.numero_factura,
            f.id idfactura,
            CONCAT(NEW.descripcion, ' (-)') AS descripcion,
            b.id idbodega,
            b.nombre,
            sg.id,
            sg.descripcion,
            s.id idseccion,
            s.descripcion,
            NEW.cantidad AS cantidad,
            u.name AS usuario
        FROM factura f
        INNER JOIN venta_has_producto vp ON vp.factura_id = NEW.factura_id
        INNER JOIN recibido_bodega rb ON rb.id = NEW.destino
        INNER JOIN producto p ON p.id = rb.producto_id
        INNER JOIN seccion s ON s.id = rb.seccion_id
        INNER JOIN segmento sg ON sg.id = s.segmento_id
        INNER JOIN bodega b ON b.id = sg.bodega_id
        INNER JOIN users u ON u.id = NEW.users_id
        WHERE f.id = NEW.factura_id;
    END IF;

    -- Trigger para factura
    IF NEW.descripcion NOT IN ('Vale de producto', 'Venta de producto - vale') AND NEW.destino IS NULL THEN
        INSERT INTO cardex (
            fecha_creacion,
            producto,
            id_producto,
            numero_factura,
            id_factura,
            descripcion,
            id_Bodega_origen,
            Bodega_origen_nombre,
            id_segmento_origen,
            segmento_origen_nombre,
            id_seccion_origen,
            seccion_origen_nombre,
            cantidad,
            usuario
        )
        SELECT DISTINCT
            NEW.created_at AS fecha_creacion,
            p.nombre AS producto,
            p.id AS id_producto,
            f.numero_factura,
            f.id idfactura,
            CONCAT(NEW.descripcion, ' (-)') AS descripcion,
            b.id idbodega,
            b.nombre,
            sg.id,
            sg.descripcion,
            s.id idseccion,
            s.descripcion,
            NEW.cantidad AS cantidad,
            u.name AS usuario
        FROM factura f
        INNER JOIN venta_has_producto vp ON vp.factura_id = NEW.factura_id
        INNER JOIN recibido_bodega rb ON rb.id = NEW.origen
        INNER JOIN producto p ON p.id = rb.producto_id
        INNER JOIN seccion s ON s.id = rb.seccion_id
        INNER JOIN segmento sg ON sg.id = s.segmento_id
        INNER JOIN bodega b ON b.id = sg.bodega_id
        INNER JOIN users u ON u.id = NEW.users_id
        WHERE f.id = NEW.factura_id;
    END IF;

    -- Trigger para ingreso de compra
    IF NEW.descripcion = 'Ingreso de producto por compra' THEN
        INSERT INTO cardex (
            fecha_creacion,
            producto,
            id_producto,
            detalleCompra,
            descripcion,
            id_Bodega_origen,
            Bodega_origen_nombre,
            id_segmento_origen,
            segmento_origen_nombre,
            id_seccion_origen,
            seccion_origen_nombre,
            cantidad,
            usuario
        )
        SELECT DISTINCT
            NEW.created_at creacion,
            p.nombre nombre,
            p.id idproducto,
            c.id idcompra,
            CONCAT(NEW.descripcion, ' (+)') descripcion,
            b.id idbodega,
            b.nombre,
            sg.id,
            sg.descripcion,
            s.id idseccion,
            s.descripcion,
            NEW.cantidad cantidad,
            u.name usuario
        FROM compra c
        INNER JOIN recibido_bodega rb ON NEW.origen = rb.id
        INNER JOIN producto p ON p.id = rb.producto_id
        INNER JOIN seccion s ON s.id = rb.seccion_id
        INNER JOIN segmento sg ON sg.id = s.segmento_id
        INNER JOIN bodega b ON b.id = sg.bodega_id
        INNER JOIN users u ON u.id = NEW.users_id
        WHERE c.estado_compra_id = 1 AND c.id = NEW.compra_id;
    END IF;

    -- Trigger para nota de crédito
    IF NEW.descripcion = 'Devolucion de producto' THEN
        INSERT INTO cardex (
            fecha_creacion,
            producto,
            id_producto,
            numero_factura,
            id_factura,
            nota_credito,
            descripcion,
            id_bodega_destino,
            Bodega_destino_nombre,
            id_segmento_destino,
            segmento_destino_nombre,
            id_seccion_destino,
            seccion_destino_nombre,
            cantidad,
            usuario
        )
        SELECT DISTINCT
            NEW.created_at creacion,
            p.nombre nombre,
            p.id idproducto,
            f.numero_factura,
            nc.factura_id idfactura,
            nc.id idnotacredito,
            CONCAT(NEW.descripcion, ' (+)') descripcion,
            b.id idbodega,
            b.nombre,
            sg.id,
            sg.descripcion,
            s.id idseccion,
            s.descripcion,
            NEW.cantidad cantidad,
            u.name usuario
        FROM nota_credito nc
        INNER JOIN recibido_bodega rb ON NEW.destino = rb.id
        INNER JOIN producto p ON p.id = rb.producto_id
        INNER JOIN seccion s ON s.id = rb.seccion_id
        INNER JOIN segmento sg ON sg.id = s.segmento_id
        INNER JOIN bodega b ON b.id = sg.bodega_id
        INNER JOIN users u ON u.id = NEW.users_id
        inner join factura f on f.id = nc.factura_id
        WHERE nc.id = NEW.nota_credito_id;
    END IF;

    -- Trigger para translado de bodega
    IF NEW.descripcion = 'Translado de bodega' AND NEW.destino IS NOT NULL AND NEW.origen IS NOT NULL THEN
        INSERT INTO cardex (
            fecha_creacion,
            producto,
            id_producto,
            descripcion,
            id_Bodega_origen,
            Bodega_origen_nombre,
            id_segmento_origen,
            segmento_origen_nombre,
            id_seccion_origen,
            seccion_origen_nombre,
            id_bodega_destino,
            Bodega_destino_nombre,
            id_segmento_destino,
            segmento_destino_nombre,
            id_seccion_destino,
            seccion_destino_nombre,
            cantidad,
            usuario
        )
        SELECT DISTINCT
            NEW.created_at creacion,
            p.nombre nombre,
            p.id idproducto,
            NEW.descripcion,
            bo.id idbodegao,
            bo.nombre,
            sgo.id idsegmentoo,
            sgo.descripcion,
            so.id idsecciono,
            so.descripcion,
            bd.id idbodegad,
            bd.nombre,
            sgd.id idsegmentod,
            sgd.descripcion,
            sd.id idsecciond,
            sd.descripcion,
            NEW.cantidad cantidad,
            u.name usuario
        FROM recibido_bodega rbo
        INNER JOIN seccion so ON so.id = rbo.seccion_id
        INNER JOIN segmento sgo ON sgo.id = so.segmento_id
        INNER JOIN bodega bo ON bo.id = sgo.bodega_id
        INNER JOIN recibido_bodega rbd ON NEW.destino = rbd.id
        INNER JOIN seccion sd ON sd.id = rbd.seccion_id
        INNER JOIN segmento sgd ON sgd.id = sd.segmento_id
        INNER JOIN bodega bd ON bd.id = sgd.bodega_id
        INNER JOIN producto p ON p.id = rbd.producto_id
        INNER JOIN users u ON u.id = NEW.users_id
        WHERE NEW.origen = rbo.id;
    END IF;

    -- Trigger para vale tipo 1
    IF NEW.descripcion = 'Vale de producto' THEN
        INSERT INTO cardex (
            fecha_creacion,
            producto,
            id_producto,
            numero_factura,
            id_factura,
            vale_tipo_1_cod,
            vale_tipo_1,
            descripcion,
            id_Bodega_origen,
            Bodega_origen_nombre,
            id_segmento_origen,
            segmento_origen_nombre,
            id_seccion_origen,
            seccion_origen_nombre,
            cantidad,
            usuario
        )
        SELECT DISTINCT
            NEW.created_at creacion,
            p.nombre nombre,
            p.id idproducto,
            f.numero_factura,
            v.factura_id idfactura,
            v.numero_vale,
            v.id idvale1,
            CONCAT(NEW.descripcion, ' (-)') descripcion,
            b.id idbodega,
            b.nombre,
            sg.id,
            sg.descripcion,
            s.id idseccion,
            s.descripcion,
            NEW.cantidad cantidad,
            u.name usuario
        FROM vale v
        INNER JOIN vale_has_producto vp ON vp.lote_id = NEW.origen AND vp.vale_id = NEW.vale_id
        INNER JOIN recibido_bodega rb ON vp.lote_id = rb.id
        INNER JOIN producto p ON p.id = rb.producto_id
        INNER JOIN seccion s ON s.id = rb.seccion_id
        INNER JOIN segmento sg ON sg.id = s.segmento_id
        INNER JOIN bodega b ON b.id = sg.bodega_id
        INNER JOIN users u ON u.id = NEW.users_id
        inner join factura f on f.id = v.factura_id
        WHERE v.id = NEW.vale_id;
    END IF;

END$$

DELIMITER ;
