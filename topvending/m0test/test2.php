<?php
session_start();
    define("DOCROOT",$_SERVER['DOCUMENT_ROOT']."/topvending");
    require_once DOCROOT . "/clases/basededatos.php";
    require_once DOCROOT . '/clases/funciones.php';
    
?>
<!DOCTYPE html>
<html>

<head>
    <title Opcion2></title>
    <meta charset="UTF-8">
    <link rel="stylesheet" href="/topvending/css/hallentrada.css">
    <?php echo crearMenu(conectar());?>
</head>
<body>
    
<?php 
        echo "<br><h1>- TOP VENDING : OPCION 2 -</h1><br>";
        echo "<hr>";
    ?>
</body>
</html>