<?php
$user ="root";
$pass="root";
$dbn ="maquinas_expendedoras";
try {
    //  Crear conexión
    $dbh = new PDO('mysql:host=localhost;dbname='.$dbn, $user, $pass);
    $dbh->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);
    echo "Conexion exitosa";
}
catch(Exception $e){
    echo "Error al conectar con la base de datos." .PHP_EOL;
    echo $e->getMessage();
}
?>