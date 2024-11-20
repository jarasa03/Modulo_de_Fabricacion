<?php
session_start();
define("DOCROOT", $_SERVER['DOCUMENT_ROOT'] . "/topvending");
require_once DOCROOT . "/clases/basededatos.php";
require_once DOCROOT . '/clases/funciones.php';
$dbh = conectar();
?>

<?php
// Recuperar las cookies individuales si están definidas
$id_maquina = isset($_COOKIE['id_maquina']) ? htmlspecialchars($_COOKIE['id_maquina']) : '';
$numero_serie = isset($_COOKIE['numero_serie']) ? htmlspecialchars($_COOKIE['numero_serie']) : '';
$id_estado = isset($_COOKIE['id_estado']) ? htmlspecialchars($_COOKIE['id_estado']) : '';
$id_ubicacion = isset($_COOKIE['id_ubicacion']) ? htmlspecialchars($_COOKIE['id_ubicacion']) : '';
$modelo = isset($_COOKIE['modelo']) ? htmlspecialchars($_COOKIE['modelo']) : '';

// Si alguna cookie no está definida, se muestra un mensaje en consola
if (!$id_maquina) {
    echo "<script>console.log('No se ha seleccionado ninguna máquina');</script>";
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

    // Guardar la información en las cookies por separado (expira en 7 días)
    setcookie('id_maquina', $id_maquina, time() + 7 * 24 * 60 * 60, "/");
    setcookie('numero_serie', $numero_serie, time() + 7 * 24 * 60 * 60, "/");
    setcookie('id_estado', $id_estado, time() + 7 * 24 * 60 * 60, "/");
    setcookie('id_ubicacion', $id_ubicacion, time() + 7 * 24 * 60 * 60, "/");
    setcookie('modelo', $modelo, time() + 7 * 24 * 60 * 60, "/");

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
    } catch (Exception $e) {
        echo "Error: " . $e->getMessage();
    }

    header("Location: ./fabricacion.php");
    exit;
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
<header><button onclick="window.location.href='../../login.php'">Cerrar sesión</button></header>

<body>
    <div id="imagen_maquina">
        <?php
        try {
            // Crear la consulta SQL para actualizar los valores
            $sqlx = "SELECT foto FROM maquina
            WHERE idmaquina = :idmaquina";

            // Preparar la consulta
            $stmt = $dbh->prepare($sqlx);

            // Enlazar los parámetros
            $stmt->bindParam(':idmaquina', $id_maquina);

            // Ejecutar la consulta
            $stmt->execute();

            // Guardo en la variable foto lo que devuelve la consulta
            $foto = $stmt->fetchColumn();
            
            if ($foto === "null") {
                echo "<img src='../resources/default.jpg'>";
            } else {
                echo "<img src='" . $foto . "'>";
            }
            echo "<script>console.log('Se muestra la imagen correctamente');</script>";
        } catch (Exception $e) {
            echo "<script>console.log('Error al mostrarse la imagen');</script>";
        }
        ?>
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
                    <!-- Campo oculto para el ID de la máquina -->
                    <input type="hidden" name="id_maquina" value="<?php echo $id_maquina; ?>">
                    <?php echo $id_maquina; ?> <!-- Muestra el ID de la máquina en la celda -->
                </td>
                <td>
                    <!-- Campo para el número de serie -->
                    <input id="num_serie" type="text" name="numero_serie" value="<?php echo $numero_serie; ?>">
                </td>
                <td>
                    <!-- Campo para el ID de estado -->
                    <input id="id_estado" type="text" name="id_estado" value="<?php echo $id_estado; ?>">
                </td>
                <td>
                    <!-- Campo para el ID de ubicación -->
                    <input id="id_ubi" type="text" name="id_ubicacion" value="<?php echo $id_ubicacion; ?>">
                </td>
                <td>
                    <!-- Campo para el modelo -->
                    <input id="modelo" type="text" name="modelo" value="<?php echo $modelo; ?>">
                </td>
            </tr>
        </table>
        <input type="submit" value="Aplicar" id="aplicar">
    </form>
</body>

</html>