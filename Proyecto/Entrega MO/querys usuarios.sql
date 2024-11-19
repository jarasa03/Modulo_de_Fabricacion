use maquinas_expendedoras;

INSERT INTO usuarios(idempleado,user,pass,rol) values(6,"rootR","root","RRHH");
INSERT INTO usuarios(idempleado,user,pass,rol) values("7","rootF","root","Fabricacion");
INSERT INTO usuarios(idempleado,user,pass,rol) values("8","rootS","root","Suministros");
INSERT INTO usuarios(idempleado,user,pass,rol) values("9","rootI","root","Incidencias");
INSERT INTO usuarios(idempleado,user,pass,rol) values("10","rootC","root","Calidad");

select * from usuarios;