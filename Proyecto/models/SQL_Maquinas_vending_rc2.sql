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
    ,	categoria 	VARCHAR(32)	NOT NULL
);

CREATE TABLE estado(
		idestado	INT 		AUTO_INCREMENT PRIMARY KEY
	,	descripcion	VARCHAR(64)	NOT NULL
);

CREATE TABLE ubicacion(
		idubicacion	INT 		AUTO_INCREMENT PRIMARY KEY
	,	cliente		VARCHAR(64)	NOT NULL
	,	dir			VARCHAR(64) NOT NULL -- PARA METERLO EN MAPS
);

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
    ,	severidad		VARCHAR(45)		NOT NULL
    ,	estado			VARCHAR(45)		NOT NULL
    ,	descripcion		VARCHAR(128)	NOT NULL
    ,	fecharegistro	DATETIME		NOT NULL
    ,	fecharesolucion	DATETIME		NOT NULL
	,	solucion		VARCHAR(128)	NOT NULL
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