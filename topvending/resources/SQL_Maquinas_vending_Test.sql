DROP DATABASE IF EXISTS maquinas_expendedoras;
CREATE DATABASE maquinas_expendedoras;

USE maquinas_expendedoras;



CREATE TABLE empleado(
		idempleado		INT 		AUTO_INCREMENT PRIMARY KEY
    ,	matricula 		VARCHAR(45)	NOT NULL
    ,   nombre 			VARCHAR(45)	NOT NULL
    ,	apellidos 		VARCHAR(45)	NOT NULL
    ,	dni 			VARCHAR(9)	NOT NULL
	,   email			VARCHAR(99) DEFAULT 'rrhh@topvending.com'
    ,	categoria 		VARCHAR(45)	NOT NULL
	,	estadolaboral	varchar(45)	NOT NULL
);
-- #1
INSERT INTO empleado(matricula,nombre,apellidos,dni,categoria,estadolaboral) values("0000000000A","Emilio","Fernandez","12345678A","RRHH","activo");
INSERT INTO empleado(matricula,nombre,apellidos,dni,categoria,estadolaboral) values("0000000000B","Maria","Aguilera","12345678B","Fabricacion","activo");
INSERT INTO empleado(matricula,nombre,apellidos,dni,categoria,estadolaboral) values("0000000000C","Wyllow","Gomes","12345678C","Suministros","activo");
INSERT INTO empleado(matricula,nombre,apellidos,dni,categoria,estadolaboral) values("0000000000D","Bernardo","Garcia","12345678D","Incidencias","activo");
INSERT INTO empleado(matricula,nombre,apellidos,dni,categoria,estadolaboral) values("0000000000E","Ramon","Merchan","12345678E","Calidad","activo");

INSERT INTO empleado(matricula,nombre,apellidos,dni,categoria,estadolaboral) values("0000000000F","Javier","Zurita","82345678A","RRHH","activo");
INSERT INTO empleado(matricula,nombre,apellidos,dni,categoria,estadolaboral) values("00000000010","Unai","Ramirez","82345678B","Fabricacion","activo");
INSERT INTO empleado(matricula,nombre,apellidos,dni,categoria,estadolaboral) values("00000000011","Carlos","Vincent","82345678C","Suministros","activo");
INSERT INTO empleado(matricula,nombre,apellidos,dni,categoria,estadolaboral) values("00000000012","Aissa","Diallo","82345678D","Incidencias","activo");
INSERT INTO empleado(matricula,nombre,apellidos,dni,categoria,estadolaboral) values("00000000013","Stefanni","Anahi","82345678E","Calidad","activo");

-- #11
INSERT INTO empleado(matricula,nombre,apellidos,dni,categoria,estadolaboral) values("00000000007","RMS","VDP","82345678E","Admin","activo");


CREATE TABLE producto(
		idproducto	INT 		AUTO_INCREMENT PRIMARY KEY
	,	marca 		VARCHAR(32) NOT NULL
    ,	modelo 		VARCHAR(32) NOT NULL
    ,	categoria 	VARCHAR(32)	NOT NULL	-- Categorias contempladas : REFRESCOS, DULCES, FRUTOS SECOS, BEBIDAS CALIENTES
);

-- #1
INSERT INTO producto (marca, modelo,categoria) values ('COCACOLA','Coke Classic','REFRESCOS');
INSERT INTO producto (marca, modelo,categoria) values ('COCACOLA','Coke Zero','REFRESCOS');
INSERT INTO producto (marca, modelo,categoria) values ('COCACOLA','Fanta Naranja','REFRESCOS');
INSERT INTO producto (marca, modelo,categoria) values ('COCACOLA','Fanta Limón','REFRESCOS');
INSERT INTO producto (marca, modelo,categoria) values ('COCACOLA','Nestea','REFRESCOS');
INSERT INTO producto (marca, modelo,categoria) values ('COCACOLA','Sprite','REFRESCOS');
-- #7
INSERT INTO producto (marca, modelo,categoria) values ('SAIMAZA','Cafe Expresso','BEBIDAS CALIENTES');
INSERT INTO producto (marca, modelo,categoria) values ('SAIMAZA','Cafe con Leche','BEBIDAS CALIENTES');
INSERT INTO producto (marca, modelo,categoria) values ('SAIMAZA','Cafe Capuchino','BEBIDAS CALIENTES');
INSERT INTO producto (marca, modelo,categoria) values ('SAIMAZA','Te Rojo','BEBIDAS CALIENTES');
-- #11
INSERT INTO producto (marca, modelo,categoria) values ('FINNI','Gominolas Fruit','DULCES');
INSERT INTO producto (marca, modelo,categoria) values ('FINNI','Ositos','DULCES');
INSERT INTO producto (marca, modelo,categoria) values ('TRIDENT','Fresa','DULCES');
INSERT INTO producto (marca, modelo,categoria) values ('TRIDENT','Menta','DULCES');
INSERT INTO producto (marca, modelo,categoria) values ('BORGES','Almendras','FRUTOS SECOS');
INSERT INTO producto (marca, modelo,categoria) values ('BORGES','Pipas con Sal','FRUTOS SECOS');
INSERT INTO producto (marca, modelo,categoria) values ('BORGES','Cacahuetes','FRUTOS SECOS');

CREATE TABLE estado(
		idestado	INT 		AUTO_INCREMENT PRIMARY KEY
	,	descripcion	VARCHAR(64)	NOT NULL	-- Valores permitidos : 'Averiada', 'en Servicio',  'Desactivada')
);

INSERT INTO estado (descripcion) values ('Averiada');
INSERT INTO estado (descripcion) values ('En Servicio');
INSERT INTO estado (descripcion) values ('Desactivada');


CREATE TABLE ubicacion(
		idubicacion	INT 		AUTO_INCREMENT PRIMARY KEY
	,	cliente		VARCHAR(64)	NOT NULL
	,	dir			VARCHAR(64) NOT NULL 	-- PARA METERLO EN MAPS
);

-- Dirección de la empresa operadora de las máquinas
-- #1
INSERT INTO ubicacion(cliente, dir) values ('TOP-VENDING','Pintor Rosales;15;28000;Madrid');
-- Direcciones de clientes del servicio
-- #2
INSERT INTO ubicacion(cliente, dir) values ('CORTE INGLES','Puerta del Sol;5;28000;Madrid');
INSERT INTO ubicacion(cliente, dir) values ('CORTE INGLES','Avda. Libertad;54;28043;Madrid');
INSERT INTO ubicacion(cliente, dir) values ('CORTE INGLES','Arguelles;154;28003;Madrid');
INSERT INTO ubicacion(cliente, dir) values ('LAVAMATIC','Franciso Silvela;39;28010;Madrid');
INSERT INTO ubicacion(cliente, dir) values ('LAVAMATIC','Juan Yuste;65;24010;Toledo');
INSERT INTO ubicacion(cliente, dir) values ('LAVAMATIC','Franciso Silvela;39;28010;Madrid');
INSERT INTO ubicacion(cliente, dir) values ('LAVAMATIC','Ronda de Valencia;65;28003;Madrid');
INSERT INTO ubicacion(cliente, dir) values ('TELA,TELITA','Ronda Litoral;89;37041;Castellón');
INSERT INTO ubicacion(cliente, dir) values ('EL RÁPIDO','Juan Gris;157;25003;Barcelona');
INSERT INTO ubicacion(cliente, dir) values ('EL TRATO','Avinguda Diagonal;443;25008;Barcelona');

CREATE TABLE maquina(
		idmaquina	INT 			AUTO_INCREMENT PRIMARY KEY
	,	numserie	VARCHAR(32)		NOT NULL
    ,	idestado	INT 			NOT NULL		-- FK DE ESTADO
    ,	idubicacion	INT 			NOT NULL		-- FK DE UBICACION
													--  LAS MÁQUINAS EN UBICACIÓN 1, SE ENCUENTRAN EN EL TALLER DE LA OPERADORA, EN ESTE CASO ESTADO DEBERÍA VALER 1 O 3
    ,	capacidad	INT				NOT NULL        -- NUMERO DE ARTICULOS DISTINTOS
	,   stockmax    INT             NOT NULL		-- CAPACIDAD MÁXIMA POR ARTICULO (TODOS IGUALES)
    ,	modelo		VARCHAR(32) 	NOT NULL		-- MODELO : STAR30 (5X6) ,STAR24 (4X6) ,STAR42 (7X6)
    ,	foto		VARCHAR(128)	NOT NULL		-- RUTA RELATIVA A DONDE ESTÁN LAS IMÁGENES
    ,	FOREIGN KEY (idestado) 	  REFERENCES estado(idestado)
    ,	FOREIGN KEY (idubicacion) REFERENCES ubicacion(idubicacion)
);

-- #1,2,3
INSERT INTO maquina(numserie, idestado, idubicacion, capacidad, stockmax, modelo, foto) values ('SN12345678',1,1,30,20,'STAR30','null'); 
INSERT INTO maquina(numserie, idestado, idubicacion, capacidad, stockmax, modelo, foto) values ('SN98765432',1,1,24,20,'STAR24','null'); 
INSERT INTO maquina(numserie, idestado, idubicacion, capacidad, stockmax, modelo, foto) values ('SN74185296',1,1,42,20,'STAR42','null'); 
-- #4,5,6
INSERT INTO maquina(numserie, idestado, idubicacion, capacidad, stockmax, modelo, foto) values ('SN87654321',2,5,30,20,'STAR30','null'); 
INSERT INTO maquina(numserie, idestado, idubicacion, capacidad, stockmax, modelo, foto) values ('SN00234567',2,6,24,20,'STAR24','null'); 
INSERT INTO maquina(numserie, idestado, idubicacion, capacidad, stockmax, modelo, foto) values ('SN99323232',2,7,42,20,'STAR42','null'); 


CREATE TABLE maquinaproducto(
		id			INT AUTO_INCREMENT PRIMARY KEY
	,	idmaquina	INT NOT NULL					-- FK DE MAQUINA
	,	idproducto	INT	NOT NULL					-- FK DE PRODUCTO
	,	stock		INT NOT NULL
    ,	FOREIGN KEY (idmaquina)	 REFERENCES maquina(idmaquina)
    ,	FOREIGN KEY (idproducto) REFERENCES producto(idproducto)
);
-- Datos para máquias 1,2 y 3
INSERT INTO maquinaproducto (idmaquina,idproducto,stock) values(1,3,20);
INSERT INTO maquinaproducto (idmaquina,idproducto,stock) values(2,10,20);
INSERT INTO maquinaproducto (idmaquina,idproducto,stock) values(3,7,20);
INSERT INTO maquinaproducto (idmaquina,idproducto,stock) values(1,9,20);
INSERT INTO maquinaproducto (idmaquina,idproducto,stock) values(2,6,20);
INSERT INTO maquinaproducto (idmaquina,idproducto,stock) values(3,17,20);
-- Datos para máquina 4
INSERT INTO maquinaproducto (idmaquina,idproducto,stock) values(4,1,20);
INSERT INTO maquinaproducto (idmaquina,idproducto,stock) values(4,2,20);
INSERT INTO maquinaproducto (idmaquina,idproducto,stock) values(4,3,20);
INSERT INTO maquinaproducto (idmaquina,idproducto,stock) values(4,4,20);
INSERT INTO maquinaproducto (idmaquina,idproducto,stock) values(4,5,20);
INSERT INTO maquinaproducto (idmaquina,idproducto,stock) values(4,6,20);
INSERT INTO maquinaproducto (idmaquina,idproducto,stock) values(4,7,20);
INSERT INTO maquinaproducto (idmaquina,idproducto,stock) values(4,8,20);
INSERT INTO maquinaproducto (idmaquina,idproducto,stock) values(4,9,20);
INSERT INTO maquinaproducto (idmaquina,idproducto,stock) values(4,10,20);
INSERT INTO maquinaproducto (idmaquina,idproducto,stock) values(4,11,20);
INSERT INTO maquinaproducto (idmaquina,idproducto,stock) values(4,12,20);
INSERT INTO maquinaproducto (idmaquina,idproducto,stock) values(4,13,20);
INSERT INTO maquinaproducto (idmaquina,idproducto,stock) values(4,14,20);
INSERT INTO maquinaproducto (idmaquina,idproducto,stock) values(4,15,20);
INSERT INTO maquinaproducto (idmaquina,idproducto,stock) values(4,16,20);
INSERT INTO maquinaproducto (idmaquina,idproducto,stock) values(4,17,20);
-- Datos para máquina 5
INSERT INTO maquinaproducto (idmaquina,idproducto,stock) values(5,1,20);
INSERT INTO maquinaproducto (idmaquina,idproducto,stock) values(5,2,20);
INSERT INTO maquinaproducto (idmaquina,idproducto,stock) values(5,3,20);
INSERT INTO maquinaproducto (idmaquina,idproducto,stock) values(5,4,20);
INSERT INTO maquinaproducto (idmaquina,idproducto,stock) values(5,5,20);
INSERT INTO maquinaproducto (idmaquina,idproducto,stock) values(5,6,20);
INSERT INTO maquinaproducto (idmaquina,idproducto,stock) values(5,7,20);
INSERT INTO maquinaproducto (idmaquina,idproducto,stock) values(5,8,20);
INSERT INTO maquinaproducto (idmaquina,idproducto,stock) values(5,9,20);
INSERT INTO maquinaproducto (idmaquina,idproducto,stock) values(5,10,20);
INSERT INTO maquinaproducto (idmaquina,idproducto,stock) values(5,11,20);
INSERT INTO maquinaproducto (idmaquina,idproducto,stock) values(5,12,20);
INSERT INTO maquinaproducto (idmaquina,idproducto,stock) values(5,13,20);
INSERT INTO maquinaproducto (idmaquina,idproducto,stock) values(5,14,20);
INSERT INTO maquinaproducto (idmaquina,idproducto,stock) values(5,15,20);
INSERT INTO maquinaproducto (idmaquina,idproducto,stock) values(5,16,20);
INSERT INTO maquinaproducto (idmaquina,idproducto,stock) values(5,17,20);
-- Datos para máquina 6
INSERT INTO maquinaproducto (idmaquina,idproducto,stock) values(6,1,20);
INSERT INTO maquinaproducto (idmaquina,idproducto,stock) values(6,2,20);
INSERT INTO maquinaproducto (idmaquina,idproducto,stock) values(6,3,20);
INSERT INTO maquinaproducto (idmaquina,idproducto,stock) values(6,4,20);
INSERT INTO maquinaproducto (idmaquina,idproducto,stock) values(6,5,20);
INSERT INTO maquinaproducto (idmaquina,idproducto,stock) values(6,6,20);
INSERT INTO maquinaproducto (idmaquina,idproducto,stock) values(6,7,20);
INSERT INTO maquinaproducto (idmaquina,idproducto,stock) values(6,8,20);
INSERT INTO maquinaproducto (idmaquina,idproducto,stock) values(6,9,20);
INSERT INTO maquinaproducto (idmaquina,idproducto,stock) values(6,10,20);
INSERT INTO maquinaproducto (idmaquina,idproducto,stock) values(6,11,20);
INSERT INTO maquinaproducto (idmaquina,idproducto,stock) values(6,12,20);
INSERT INTO maquinaproducto (idmaquina,idproducto,stock) values(6,13,20);
INSERT INTO maquinaproducto (idmaquina,idproducto,stock) values(6,14,20);
INSERT INTO maquinaproducto (idmaquina,idproducto,stock) values(6,15,20);
INSERT INTO maquinaproducto (idmaquina,idproducto,stock) values(6,16,20);
INSERT INTO maquinaproducto (idmaquina,idproducto,stock) values(6,17,20);


CREATE TABLE incidencias(
		idincidencia	INT 														AUTO_INCREMENT PRIMARY KEY
    ,	idmaquina		INT 														NOT NULL	-- FK DE MAQUINA
    ,	idproducto		INT 														NOT NULL 	-- FK DE PRODUCTO
    ,	idubicacion		int															NOT NULL	-- FK DE UBICACION
    ,	categoria		ENUM('Producto', 'Equipo', 'Red')							NOT NULL	-- Categoría de la incidencia :
																						-- 'Producto' : relacionada con los productos (sin stock, debajo del umbral, etc)
																						-- 'Equipo'   : relacionada con el funcionamiento del equipo : atasco, mensaje error algún componente
																						-- 'Red'      : cortes en la comunicación con la central o algún problema de conectividad
    ,	stock			INT 														NOT NULL
    ,	severidad		ENUM('ALTA', 'MEDIA', 'BAJA')								NOT NULL	
    ,	estado			ENUM('Registrada', 'Revisada', 'En curso', 'Solucionada')	NOT NULL	-- Valores permitidos : 'Registrada', 'Revisada', 'En curso', 'Solucionada'
    ,	descripcion		VARCHAR(128)												NOT NULL
    ,	fecharegistro	DATETIME													NOT NULL
    ,	fecharesolucion	DATETIME													NULL
	,	solucion		VARCHAR(128)												NULL
    ,	FOREIGN KEY (idmaquina)   REFERENCES maquina(idmaquina)
    ,	FOREIGN KEY (idproducto)  REFERENCES producto(idproducto)
    ,	FOREIGN KEY (idubicacion) REFERENCES ubicacion(idubicacion)
);


CREATE TABLE usuarios(
		idempleado		INT 		NOT NULL
	,	user			VARCHAR(32)	NOT NULL
    ,	pass			VARCHAR(64)	NOT NULL			-- CONTRASEÑA ENCRIPTADA
    ,	rol				VARCHAR(32) NOT NULL
    , 	FOREIGN KEY (idempleado) REFERENCES empleado(idempleado)
);


INSERT INTO usuarios values (11,'m0','pass','ADMIN');
INSERT INTO usuarios values (1,'m1','pass','RRHH');
INSERT INTO usuarios values (2,'m2','pass','FABRICACION');
INSERT INTO usuarios values (3,'m3','pass','SUMINISTROS');
INSERT INTO usuarios values (4,'m4','pass','INCIDENCIAS');
INSERT INTO usuarios values (5,'m5','pass','CALIDAD');

CREATE TABLE perfil(
		rol		VARCHAR(32)	NOT NULL					-- PERFIL DEL USUARIO
    ,	modulo	VARCHAR(8)	NOT NULL					-- MODULO AL QUE TIENE ACCESO
	,   titulo  VARCHAR(64) NOT NULL
);
INSERT INTO perfil(rol, modulo,titulo) values ('ADMIN','M0','TopVending ADMINISTRACIÓN');
INSERT INTO perfil(rol, modulo,titulo) values ('RRHH','M1','TopVending RECURSOS HUMANOS');
INSERT INTO perfil(rol, modulo,titulo) values ('FABRICACION','M2','TopVending FABRICACIÓN');
INSERT INTO perfil(rol, modulo,titulo) values ('SUMINISTROS','M3','TopVending SUMINISTROS');
INSERT INTO perfil(rol, modulo,titulo) values ('INCIDENCIAS','M4','TopVending INCIDENCIAS');
INSERT INTO perfil(rol, modulo,titulo) values ('CALIDAD','M5','TopVending INFORMES');


CREATE TABLE menu(
		idmenu	INT AUTO_INCREMENT 	PRIMARY KEY,
    	modulo	VARCHAR(8)			NOT NULL,			-- MODULO AL QUE TIENE ACCESO (FK)
        rol     VARCHAR(32)			NOT NULL,			-- ROL (FK ALTERNATIVA)
		orden    INT 				NOT NULL,			-- ORDEN LA OPCIÓN
		boton   VARCHAR(24) 		NOT NULL,   		-- TEXTO DE LA OPCIÓN
		enlace  VARCHAR(128) 		DEFAULT '/m0test/wip.php'  -- PAGINA DE INICIO DEL MÓDULO
);
-- Creación de menús genéricos para pruebas de la aplicación
INSERT INTO menu (rol,modulo,orden,boton,enlace) values ('ADMIN','M0',1,'Opcion 1','/m0test/test1.php');
INSERT INTO menu (rol,modulo,orden,boton,enlace) values ('ADMIN','M0',2,'Opcion 2','/m0test/test2.php');
INSERT INTO menu (rol,modulo,orden,boton,enlace) values ('ADMIN','M0',3,'Opcion 3','/m0test/test3.php');


INSERT INTO menu (rol,modulo,orden,boton) values ('RRHH','M1',1,'Opcion 1');
INSERT INTO menu (rol,modulo,orden,boton) values ('RRHH','M1',2,'Opcion 2');
INSERT INTO menu (rol,modulo,orden,boton) values ('RRHH','M1',3,'Opcion 3');
INSERT INTO menu (rol,modulo,orden,boton) values ('RRHH','M1',4,'Opcion 4');

-- Opciones de menu

INSERT INTO menu (rol,modulo,orden,boton,enlace) values ('FABRICACION','M2',1,'Inicio','/m2/fabricacion.php');
INSERT INTO menu (rol,modulo,orden,boton,enlace) values ('FABRICACION','M2',2,'Añadir Máquina','/m2/anyadir_maquina.php');
INSERT INTO menu (rol,modulo,orden,boton,enlace) values ('FABRICACION','M2',3,'Añadir Ubicación','/m2/anyadir_ubicacion.php');
INSERT INTO menu (rol,modulo,orden,boton,enlace) values ('FABRICACION','M2',4,'Ubicaciones','/m2/ubicaciones.php');

INSERT INTO menu (rol,modulo,orden,boton) values ('SUMINISTROS','M3',1,'Opcion 1');
INSERT INTO menu (rol,modulo,orden,boton) values ('SUMINISTROS','M3',2,'Opcion 2');
INSERT INTO menu (rol,modulo,orden,boton) values ('SUMINISTROS','M3',3,'Opcion 3');
INSERT INTO menu (rol,modulo,orden,boton) values ('SUMINISTROS','M3',4,'Opcion 4');

INSERT INTO menu (rol,modulo,orden,boton) values ('INCIDENCIAS','M4',1,'Opcion 1');
INSERT INTO menu (rol,modulo,orden,boton) values ('INCIDENCIAS','M4',2,'Opcion 2');
INSERT INTO menu (rol,modulo,orden,boton) values ('INCIDENCIAS','M4',3,'Opcion 3');
INSERT INTO menu (rol,modulo,orden,boton) values ('INCIDENCIAS','M4',4,'Opcion 4');

INSERT INTO menu (rol,modulo,orden,boton) values  ('CALIDAD','M5', 1,'Opcion 1');
INSERT INTO menu (rol,modulo,orden,boton) values  ('CALIDAD','M5', 2,'Opcion 2');
INSERT INTO menu (rol,modulo,orden,boton) values  ('CALIDAD','M5', 3,'Opcion 3');
INSERT INTO menu (rol,modulo,orden,boton) values  ('CALIDAD','M5', 4,'Opcion 4');
