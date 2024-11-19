<?php  
    session_start();
    //incluye la pagina donde se hace el login
    require_once __DIR__ . '/clases/funciones.php';
    //incluye la conexion a la base de datos
    require_once __DIR__ . '/clases/basededatos.php';
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Prueba</title>
    <link rel="stylesheet" href="css/hallentrada.css">
</head>
<body class="bodyy">
    <?php  
    $conexion = conectar();
 redirect(); 
 echo crearMenu($conexion);
 echo saludoini($conexion);
    
    ?>
</body>
</html>