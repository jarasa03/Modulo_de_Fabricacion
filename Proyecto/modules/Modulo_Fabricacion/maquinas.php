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
<?php
// Verifica si la cookie 'filaSeleccionada' está establecida
if (isset($_COOKIE['filaSeleccionada'])) {
    // Recuperar la cookie
    $valor = $_COOKIE['filaSeleccionada'];

    // Mostrar el valor de la cookie (en este caso, la cadena de valores)
    echo "Valores de la fila seleccionada: " . $valor;

    // Si quieres dividir la cadena en un array de valores individuales
    $valoresArray = explode(',', $valor); // Divide la cadena por las comas

    // Ahora puedes usar $valoresArray como un array normal
    echo "<pre>";
    print_r($valoresArray);
    echo "</pre>";
} else {
    echo "<script>console.log('No se ha seleccionado ningún campo');</script>";
}
?>


<!DOCTYPE html>
<html lang="es">

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Máquinas</title>
    <link rel="stylesheet" href="./css/maquinas.css">
</head>
<header></header>

<body>
    <div id="imagen_maquina">
        <?php
        
        echo "<img src=''>"
        ?>
    </div>
    <table>
        <thead>
            <tr id="encabezados">
                <th>Id de Máquina</th>
                <th>Número de Serie</th>
                <th>Id de Estado</th>
                <th>Id de Ubicación</th>
                <th>Modelo</th>
            </tr>
        </thead>
        <tr>
            <td></td>
            <td></td>
            <td></td>
            <td></td>
            <td></td>
        </tr>
    </table>

    <input type="submit" value="Aplicar" id="aplicar">
</body>

</html>