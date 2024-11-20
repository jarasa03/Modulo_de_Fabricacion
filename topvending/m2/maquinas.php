<?php
session_start();
define("DOCROOT", $_SERVER['DOCUMENT_ROOT'] . "/topvending");
require_once DOCROOT . "/clases/basededatos.php";
require_once DOCROOT . '/clases/funciones.php';
$dbh = conectar();
echo crearMenu($dbh);
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
// Si alguna cookie no está definida, se muestra un mensaje en consola
if (!$id_maquina) {
    echo "<script>console.log('No se ha seleccionado ninguna máquina');</script>";
}
?>

<?php
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    if (isset($_POST['BorrarMaquina'])) {
        $id_maquina = $_POST['id_maquina']; // Recupera el ID de la máquina a eliminar

        try {
            // Eliminar los productos asociados a la máquina
            $delete_producto_sql = "DELETE FROM maquinaproducto WHERE idmaquina = :id";
            $stmt = $dbh->prepare($delete_producto_sql);
            $stmt->bindParam(":id", $id_maquina, PDO::PARAM_INT);
            $stmt->execute();

            // Eliminar la máquina
            $delete_sql = "DELETE FROM maquina WHERE idmaquina = :id";
            $stmt = $dbh->prepare($delete_sql);
            $stmt->bindParam(':id', $id_maquina, PDO::PARAM_INT);
            $stmt->execute();

            // Eliminar las cookies asociadas a la máquina
            setcookie('id_maquina', '', time() - 3600, "/");
            setcookie('numero_serie', '', time() - 3600, "/");
            setcookie('id_estado', '', time() - 3600, "/");
            setcookie('id_ubicacion', '', time() - 3600, "/");
            setcookie('modelo', '', time() - 3600, "/");

            echo "<script>console.log('Máquina eliminada correctamente');</script>";
        } catch (Exception $e) {
            echo "Error al eliminar la máquina: " . $e->getMessage();
        }

        header("Location: ./fabricacion.php");
        exit;
    }
}

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
    <link rel="stylesheet" href="/topvending/css/hallentrada.css">
</head>

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
                    <!-- Campo para el ID de estado usando un select con tres opciones -->
                    <select id="id_estado" name="id_estado">
                        <option value="1" <?php echo ($id_estado == "1") ? 'selected' : ''; ?>>1</option>
                        <option value="2" <?php echo ($id_estado == "2") ? 'selected' : ''; ?>>2</option>
                        <option value="3" <?php echo ($id_estado == "3") ? 'selected' : ''; ?>>3</option>
                    </select>
                </td>

                <td>
                    <!-- Campo para el ID de ubicación usando un select con opciones dinámicas -->
                    <select id="id_ubicacion" name="id_ubicacion" onchange="this.form.submit()">
                        <?php
                        // Consulta para obtener las ubicaciones distintas de la base de datos
                        $ubicaciones_query = "SELECT DISTINCT idubicacion FROM ubicacion";
                        $stm = $dbh->prepare($ubicaciones_query);
                        $stm->execute();
                        $ubicaciones = $stm->fetchAll(PDO::FETCH_COLUMN);  // Obtener las ubicaciones en un array

                        // Recorrer las ubicaciones obtenidas y crear las opciones del select
                        foreach ($ubicaciones as $ubicacion_option) {
                            // Comprobar si el valor de la cookie coincide con la ubicación actual
                            $selected = ($id_ubicacion == $ubicacion_option) ? 'selected' : '';
                            echo "<option value='" . htmlspecialchars($ubicacion_option) . "' $selected>" . htmlspecialchars($ubicacion_option) . "</option>";
                        }
                        ?>
                    </select>
                </td>

                <td>
                    <!-- Campo para el modelo usando un select dinámico -->
                    <select id="modelo" name="modelo" onchange="this.form.submit()">
                        <?php
                        // Consulta para obtener los modelos distintos de la base de datos
                        $modelos_query = "SELECT DISTINCT modelo FROM maquina";
                        $stm = $dbh->prepare($modelos_query);
                        $stm->execute();
                        $modelos = $stm->fetchAll(PDO::FETCH_COLUMN);  // Obtener los modelos en un array

                        // Recorrer los modelos obtenidos y crear las opciones del select
                        foreach ($modelos as $modelo_option) {
                            // Comprobar si el valor de la cookie coincide con el modelo actual
                            $selected = ($modelo == $modelo_option) ? 'selected' : '';
                            echo "<option value='" . htmlspecialchars($modelo_option) . "' $selected>" . htmlspecialchars($modelo_option) . "</option>";
                        }
                        ?>
                    </select>
                </td>

            </tr>
        </table>
        <input type="submit" value="Aplicar" id="aplicar">
        <button type="submit" name="BorrarMaquina" id="botonBorrar">Eliminar</button>
    </form>
</body>

</html>