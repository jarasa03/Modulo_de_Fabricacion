<?php
$user = "root";
$pass = "root";
$dbn = "maquinas_expendedoras";
try {
    $dbh = new PDO('mysql:host=localhost;dbname=' . $dbn, $user, $pass);
    $dbh->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);
    echo '<script>console.log("Conexion exitosa");</script>';
} catch (Exception $e) {
    echo '<script>console.log("Error en la conexión con la base de datos");</script>';
    echo $e->getMessage();
}
?>

<!DOCTYPE html>
<html lang="es">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Máquinas</title>
</head>
<body>
    
</body>
</html>