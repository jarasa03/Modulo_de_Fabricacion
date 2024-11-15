<?php
session_start();
require_once "../../lib/basededatos.php";
require_once "../../lib/funciones.php";
$dbh = conectar();
?>

<?php
// Verifica si la cookie 'filaSeleccionada' está establecida
if (isset($_COOKIE['filaSeleccionada'])) {
    $valor = htmlspecialchars($_COOKIE['filaSeleccionada']);
    $valoresArray = explode(',', $valor); // Divide la cadena por las comas
} else {
    echo "<script>console.log('No se ha seleccionado ningún campo');</script>";
    exit;
}
?>

<?php
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    // Recibir los datos del formulario usando $_POST
    $numero_serie = $_POST['numero_serie'];
    $id_estado = $_POST['id_estado'];
    $id_ubicacion = $_POST['id_ubicacion'];
    $modelo = $_POST['modelo'];
    $id_maquina = $_POST['id_maquina'];  // Campo oculto con el ID de la máquina

    try {
        // Crear la consulta SQL para actualizar los valores
        $sql = "UPDATE maquina SET 
                numserie = :numserie, 
                idestado = :idestado, 
                idubicacion = :idubicacion, 
                modelo = :modelo
                WHERE idmaquina = :idmaquina";

        // Preparar la consulta
        $stmt = $dbh->prepare($sql);

        // Enlazar los parámetros
        $stmt->bindParam(':numserie', $numero_serie);
        $stmt->bindParam(':idestado', $id_estado);
        $stmt->bindParam(':idubicacion', $id_ubicacion);
        $stmt->bindParam(':modelo', $modelo);
        $stmt->bindParam(':idmaquina', $id_maquina);

        // Ejecutar la consulta
        $stmt->execute();

        echo "<script>console.log('Base de datos actualizada correctamente');</script>";

        // Actualizar la cookie con los nuevos valores
        $cookieValue = $id_maquina . ',' . $numero_serie . ',' . $id_estado . ',' . $id_ubicacion . ',' . $modelo;

        // Establecer la cookie con los nuevos valores (expira en 7 días)
        setcookie("filaSeleccionada", $cookieValue, time() + 7 * 24 * 60 * 60, "/"); // 7 días

        // Confirmar que la cookie fue actualizada
        echo "<script>console.log('Cookie actualizada correctamente');</script>";
    } catch (Exception $e) {
        echo "Error: " . $e->getMessage();
    }
}
?>

<!DOCTYPE html>
<html lang="es">

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Máquinas</title>
    <link rel="stylesheet" href="./css/maquinas.css">
    <script src="./js/maquinas.js" defer></script>
</head>
<header><button onclick="window.location.href='../../login.php  '">Cerrar sesion</button></header>

<body>
    <div id="imagen_maquina">
        <?php echo "<img src=''>"; ?>
    </div>
    <form id="formulario_maquina" method="POST">
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
                <td>
                    <?php echo $valoresArray[0]; ?>
                    <input type="hidden" name="id_maquina" value="<?php echo $valoresArray[0]; ?>"> <!-- ID oculto -->
                </td>
                <td>
                    <input id="num_serie" type="text" name="numero_serie" value="<?php echo $valoresArray[1]; ?>">
                </td>
                <td>
                    <input id="id_estado" type="text" name="id_estado" value="<?php echo $valoresArray[2]; ?>">
                </td>
                <td>
                    <input id="id_ubi" type="text" name="id_ubicacion" value="<?php echo $valoresArray[3]; ?>">
                </td>
                <td>
                    <input id="modelo" type="text" name="modelo" value="<?php echo $valoresArray[4]; ?>">
                </td>
            </tr>
        </table>
        <input type="submit" value="Aplicar" id="aplicar">
    </form>
</body>

</html>