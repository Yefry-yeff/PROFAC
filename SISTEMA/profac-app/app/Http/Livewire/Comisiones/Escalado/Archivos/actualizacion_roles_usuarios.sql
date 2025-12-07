--USUARIOS QUE NO LABORAN EN VALENCIA
select id from users A where A.email in (
'francis.andino@distribucionesvalencia.hn',
'claudia.sabillon@distribucionesvalencia.hn',
'cristian.zelaya@distribucionesvalencia.hn',
'michael.irias@distribucionesvalencia.hn',
'williams.villalta@distribucionesvalencia.hn',
'oficina@distribucionesvalencia.hn',
'edwin.godoy@distribucionesvalencia.hn',
'jose.hernandez@distribucionesvalencia.hn',
'juan.betancourth@distribucionesvalencia.hn',
'wendy.munguia@distribucionesvalencia.hn',
'lester.cruz@distribucionesvalencia.hn',
'mayron.cabrera@distribucionesvalencia.hn',
'soary.arleth@distribucionesvalencia.hn',
'erling.peralta@distribucionesvalencia.hn',
'jeymi.varela@distribucionesvalencia.hn',
'olga.oseguera@distribucionesvalencia.hn',
'zully.garcia@distribucionesvalencia.hn',
'carlos.ponce@distribucionesvalencia.hn',
'nathaly.garcia@distribucionesvalencia.hn',
'heather.miranda@distribucionesvalencia.hn',
'alba.rodas@distribucionesvalencia.hn',
'mercedes.barahona@distribucionesvalencia.hn',
'daniel.dominguez@distribucionesvalencia.hn',
'carlos.alfaro@distribucionesvalencia.hn',
'ariana.villagra@distribucionesvalencia.hn',
'josue.cerritos@distribucionesvalencia.hn',
'zoila.martinez@distribucionesvalencia.hn',
'evelyn.paz@distribucionesvalencia.hn',
'glenda.martinez@distribucionesvalencia.hn',
'sofia.aguilera@distribucionesvalencia.hn',
'erick.sierra@distribucionesvalencia.hn',
'christian.estrada@distribucionesvalencia.hn',
'claudia.arias@distribucionesvalencia.hn',
'leonardo.barahona@distribucionesvalencia.hn',
'enis.pacheco@distribucionesvalencia.hn',
'alex.colindres@distribucionesvalencia.hn',
'carolina.oyuela@distribucionesvalencia.hn',
'natalia.quezada@distribucionesvalencia.hn',
'israel.galvez@distribucionesvalencia.hn',
'kevin.osorio@distribucionesvalencia.hn',
'andre.centeno@distribucionesvalencia.hn',
'scarleth.zepeda@distribucionesvalencia.hn',
'carlos.fernandez@distribucionesvalencia.hn'

);

--CREANDO ROL SIN ACCESO
INSERT INTO rol (nombre)
VALUES ('inaccesoble');

--ACTUALUIZANDO TODO AL ROL SIN ACCESO
UPDATE users
SET rol_id = 10
WHERE email IN (
  'francis.andino@distribucionesvalencia.hn',
  'claudia.sabillon@distribucionesvalencia.hn',
  'cristian.zelaya@distribucionesvalencia.hn',
  'michael.irias@distribucionesvalencia.hn',
  'williams.villalta@distribucionesvalencia.hn',
  'oficina@distribucionesvalencia.hn',
  'edwin.godoy@distribucionesvalencia.hn',
  'jose.hernandez@distribucionesvalencia.hn',
  'juan.betancourth@distribucionesvalencia.hn',
  'wendy.munguia@distribucionesvalencia.hn',
  'lester.cruz@distribucionesvalencia.hn',
  'mayron.cabrera@distribucionesvalencia.hn',
  'soary.arleth@distribucionesvalencia.hn',
  'erling.peralta@distribucionesvalencia.hn',
  'jeymi.varela@distribucionesvalencia.hn',
  'olga.oseguera@distribucionesvalencia.hn',
  'zully.garcia@distribucionesvalencia.hn',
  'carlos.ponce@distribucionesvalencia.hn',
  'nathaly.garcia@distribucionesvalencia.hn',
  'heather.miranda@distribucionesvalencia.hn',
  'alba.rodas@distribucionesvalencia.hn',
  'mercedes.barahona@distribucionesvalencia.hn',
  'daniel.dominguez@distribucionesvalencia.hn',
  'carlos.alfaro@distribucionesvalencia.hn',
  'ariana.villagra@distribucionesvalencia.hn',
  'josue.cerritos@distribucionesvalencia.hn',
  'zoila.martinez@distribucionesvalencia.hn',
  'evelyn.paz@distribucionesvalencia.hn',
  'glenda.martinez@distribucionesvalencia.hn',
  'sofia.aguilera@distribucionesvalencia.hn',
  'erick.sierra@distribucionesvalencia.hn',
  'christian.estrada@distribucionesvalencia.hn',
  'claudia.arias@distribucionesvalencia.hn',
  'leonardo.barahona@distribucionesvalencia.hn',
  'enis.pacheco@distribucionesvalencia.hn',
  'alex.colindres@distribucionesvalencia.hn',
  'carolina.oyuela@distribucionesvalencia.hn',
  'natalia.quezada@distribucionesvalencia.hn',
  'israel.galvez@distribucionesvalencia.hn',
  'kevin.osorio@distribucionesvalencia.hn',
  'andre.centeno@distribucionesvalencia.hn',
  'scarleth.zepeda@distribucionesvalencia.hn',
  'carlos.fernandez@distribucionesvalencia.hn'
);



-----ACTUALIZACIÓN DE ROLES DE USUARIO por diferenciación en comisiones
UPDATE rol SET nombre = 'Asesor Comercial' where id = 2;
UPDATE rol SET nombre = 'Créditos y Cobros' where id = 4;
UPDATE rol SET nombre = 'Televendedor' where id = 3;
UPDATE rol SET nombre = 'Recursos Humanos' where id = 8;
UPDATE rol SET nombre = 'Administrador' where id = 1;

