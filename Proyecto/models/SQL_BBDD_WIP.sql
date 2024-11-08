DROP DATABASE IF EXISTS maquinas_expendedoras;
CREATE DATABASE maquinas_expendedoras;

USE maquinas_expendedoras;

CREATE TABLE empleado(
		idempleado		INT 		AUTO_INCREMENT PRIMARY KEY
    ,	matricula 		VARCHAR(45)	NOT NULL
    ,   nombre 			VARCHAR(45)	NOT NULL
    ,	apellidos 		VARCHAR(45)	NOT NULL
    ,	dni 			VARCHAR(9)	NOT NULL
    ,	categoria 		VARCHAR(45)	NOT NULL
	,	estadolaboral	varchar(45)	NOT NULL
);

CREATE TABLE producto(
		idproducto	INT 		AUTO_INCREMENT PRIMARY KEY
	,	marca 		VARCHAR(32) NOT NULL
    ,	modelo 		VARCHAR(32) NOT NULL
    ,	categoria 	VARCHAR(32)	NOT NULL	-- Categorias contempladas : REFRESCOS, DULCES, FRUTOS SECOS, BEBIDAS CALIENTES
);


INSERT INTO producto (marca, modelo,categoria) values ('COCACOLA','Coke Classic','REFRESCOS');
INSERT INTO producto (marca, modelo,categoria) values ('COCACOLA','Coke Zero','REFRESCOS');
INSERT INTO producto (marca, modelo,categoria) values ('COCACOLA','Fanta Naranja','REFRESCOS');
INSERT INTO producto (marca, modelo,categoria) values ('COCACOLA','Fanta Limón','REFRESCOS');
INSERT INTO producto (marca, modelo,categoria) values ('COCACOLA','Nestea','REFRESCOS');
INSERT INTO producto (marca, modelo,categoria) values ('COCACOLA','Sprite','REFRESCOS');
INSERT INTO producto (marca, modelo,categoria) values ('SAIMAZA','Cafe Expresso','BEBIDAS CALIENTES');
INSERT INTO producto (marca, modelo,categoria) values ('SAIMAZA','Cafe con Leche','BEBIDAS CALIENTES');
INSERT INTO producto (marca, modelo,categoria) values ('SAIMAZA','Cafe Capuchino','BEBIDAS CALIENTES');
INSERT INTO producto (marca, modelo,categoria) values ('SAIMAZA','Te Rojo','BEBIDAS CALIENTES');
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
	,	dir			VARCHAR(64) NOT NULL -- PARA METERLO EN MAPS
);

-- Dirección de la empresa operadora de las máquinas
INSERT INTO ubicacion(cliente, dir) values ('TOP-VENDING','Pintor Rosales;15;28000;Madrid');
-- Direcciones de clientes del servicio
INSERT INTO ubicacion(cliente, dir) values ('CORTE INGLES','Puerta del Sol;5;28000;Madrid');
INSERT INTO ubicacion(cliente, dir) values ('CORTE INGLES','Avda. Libertad;54;28043;Madrid');
INSERT INTO ubicacion(cliente, dir) values ('CORTE INGLES','Arguelles;154;28003;Madrid');
INSERT INTO ubicacion(cliente, dir) values ('LAVAMATIC','Franciso Silvela;39;28010;Madrid');
INSERT INTO ubicacion(cliente, dir) values ('LAVAMATIC','Franciso Yueste;65;24010;Toledo');
INSERT INTO ubicacion(cliente, dir) values ('LAVAMATIC','Franciso Silvela;39;28010;Madrid');
INSERT INTO ubicacion(cliente, dir) values ('LAVAMATIC','Ronda de Valencia;65;28003;Madrid');
INSERT INTO ubicacion(cliente, dir) values ('TELA,TELITA','Ronda Litoral;89;37041;Castellón');
INSERT INTO ubicacion(cliente, dir) values ('EL RÁPIDO','Juan Gris;157;25003;Barcelona');
INSERT INTO ubicacion(cliente, dir) values ('EL TRATO','Avinguda Diagonal;443;25008;Barcelona');

CREATE TABLE maquina(
		idmaquina	INT 			AUTO_INCREMENT PRIMARY KEY
	,	numserie	VARCHAR(32)		NOT NULL
    ,	idestado	INT 			NOT NULL	-- FK DE ESTADO
    ,	idubicacion	INT 			NOT NULL	-- FK DE UBICACION
    ,	capacidad	INT				NOT NULL
    ,	modelo		VARCHAR(32) 	NOT NULL
    ,	foto		VARCHAR(128)	NOT NULL	-- RUTA RELATIVA A DONDE ESTÁN LAS IMÁGENES
    ,	FOREIGN KEY (idestado) 	  REFERENCES estado(idestado)
    ,	FOREIGN KEY (idubicacion) REFERENCES ubicacion(idubicacion)
);

CREATE TABLE maquinaproducto(
		id			INT AUTO_INCREMENT PRIMARY KEY
	,	idmaquina	INT NOT NULL	-- FK DE MAQUINA
	,	idproducto	INT	NOT NULL	-- FK DE PRODUCTO
	,	stock		INT NOT NULL
    ,	FOREIGN KEY (idmaquina)	 REFERENCES maquina(idmaquina)
    ,	FOREIGN KEY (idproducto) REFERENCES producto(idproducto)
);

CREATE TABLE incidencias(
		idincidencia	INT 			AUTO_INCREMENT PRIMARY KEY
    ,	idmaquina		INT 			NOT NULL	-- FK DE MAQUINA
    ,	idproducto		INT 			NOT NULL 	-- FK DE PRODUCTO
    ,	idubicacion		int				NOT NULL	-- FK DE UBICACION
    ,	categoria		VARCHAR(45)		NOT NULL
    ,	stock			INT 			NOT NULL
    ,	severidad		VARCHAR(45)		CHECK (severidad ='ALTA' or severidad ='MEDIA' or severidad='BAJA')
    ,	estado			VARCHAR(45)		NOT NULL  -- Valores permitidos : 'Averiada', 'en Servicio',  'Desactivada') (Ver tabla de estados)
    ,	descripcion		VARCHAR(128)	NOT NULL
    ,	fecharegistro	DATETIME		NOT NULL
    ,	fecharesolucion	DATETIME		NULL
	,	solucion		VARCHAR(128)	NULL
    ,	FOREIGN KEY (idmaquina)   REFERENCES maquina(idmaquina)
    ,	FOREIGN KEY (idproducto)  REFERENCES producto(idproducto)
    ,	FOREIGN KEY (idubicacion) REFERENCES ubicacion(idubicacion)
);

CREATE TABLE usuarios(
		idempleado		INT 		NOT NULL
	,	user			VARCHAR(32)	NOT NULL
    ,	pass			VARCHAR(32)	NOT NULL
    ,	rol				VARCHAR(32) NOT NULL
    , 	FOREIGN KEY (idempleado) REFERENCES empleado(idempleado)
);

CREATE TABLE perfil(
		rol		VARCHAR(32)	NOT NULL	-- PERFIL DEL USUARIO
    ,	modulo	VARCHAR(8)	NOT NULL	-- MODULO AL QUE TIENE ACCESO
);
INSERT INTO perfil(rol, modulo) values ('ADMIN','M0');
INSERT INTO perfil(rol, modulo) values ('RRHH','M1');
INSERT INTO perfil(rol, modulo) values ('FABRICACION','M2');
INSERT INTO perfil(rol, modulo) values ('SUMINISTROS','M3');
INSERT INTO perfil(rol, modulo) values ('INCIDENCIAS','M4');
INSERT INTO perfil(rol, modulo) values ('CALIDAD','M5');


CREATE TABLE menu(
		idmenu	INT AUTO_INCREMENT 	PRIMARY KEY,
    	modulo	VARCHAR(8)			NOT NULL,	-- MODULO AL QUE TIENE ACCESO
		orden    INT 				NOT NULL,			-- ORDEN LA OPCIÓN
		boton   VARCHAR(10) 		NOT NULL,   -- TEXTO DE LA OPCIÓN
		enlace  VARCHAR(128) 		DEFAULT 'login.php' -- PAGINA DE INICIO DEL MÓDULO
);