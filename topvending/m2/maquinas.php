<?php
session_start();
define("DOCROOT", $_SERVER['DOCUMENT_ROOT'] . "/topvending");
require_once DOCROOT . "/clases/basededatos.php";
require_once DOCROOT . '/clases/funciones.php';
$dbh = conectar();
echo crearMenu($dbh);

// Recuperar las cookies individuales si están definidas
$id_maquina = isset($_COOKIE['id_maquina']) ? htmlspecialchars($_COOKIE['id_maquina']) : '';
$numero_serie = isset($_COOKIE['numero_serie']) ? htmlspecialchars($_COOKIE['numero_serie']) : '';
$id_estado = isset($_COOKIE['id_estado']) ? htmlspecialchars($_COOKIE['id_estado']) : '';
$id_ubicacion = isset($_COOKIE['id_ubicacion']) ? htmlspecialchars($_COOKIE['id_ubicacion']) : '';
$modelo = isset($_COOKIE['modelo']) ? htmlspecialchars($_COOKIE['modelo']) : '';

// Procesar el formulario al enviar
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    // Procesar la subida de la imagen
    if (isset($_FILES['foto']) && $_FILES['foto']['error'] === UPLOAD_ERR_OK) {
        $uploadDir = DOCROOT . '/resources/';
        $fileName = basename($_FILES['foto']['name']);
        $targetFile = $uploadDir . $fileName;

        // Validar tipo de archivo
        $allowedTypes = ['image/jpeg', 'image/png', 'image/gif'];
        $fileType = mime_content_type($_FILES['foto']['tmp_name']);
        if (in_array($fileType, $allowedTypes)) {
            if (move_uploaded_file($_FILES['foto']['tmp_name'], $targetFile)) {
                // Guardar la ruta relativa de la foto como texto en la base de datos
                $fotoRuta = "/resources/" . $fileName;

                try {
                    // Asegurarse de que el ID de la máquina está disponible
                    if (!empty($id_maquina)) {
                        $sql = "UPDATE maquina SET foto = :foto WHERE idmaquina = :idmaquina";
                        $stmt = $dbh->prepare($sql);
                        $stmt->bindParam(':foto', $fotoRuta);
                        $stmt->bindParam(':idmaquina', $id_maquina);
                        $stmt->execute();

                        echo "<script>console.log('Imagen subida y ruta guardada correctamente para la máquina con ID: $id_maquina.');</script>";
                    } else {
                        echo "Error: No se encontró un ID de máquina válido en las cookies.";
                    }
                } catch (Exception $e) {
                    echo "Error al guardar la ruta de la foto: " . $e->getMessage();
                }
            } else {
                echo "Error al mover el archivo.";
            }
        } else {
            echo "Tipo de archivo no permitido.";
        }
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
    <link rel="stylesheet" href="/topvending/css/hallentrada.css">
</head>

<body>
    <div id="imagen_maquina">
        <?php
        try {
            // Obtener la ruta de la imagen desde la base de datos
            $sqlx = "SELECT foto FROM maquina WHERE idmaquina = :idmaquina";
            $stmt = $dbh->prepare($sqlx);
            $stmt->bindParam(':idmaquina', $id_maquina);
            $stmt->execute();
            $foto = $stmt->fetchColumn();

            if ($foto === "null") {
                echo "<img src='../resources/default.jpg' alt='Imagen por defecto'>";
            } else {
                echo "<img src='.." . $foto . "' alt='imagen establecida'>";
            }
        } catch (Exception $e) {
            echo "Error al mostrar la foto: " . $e->getMessage();
        }
        ?>
    </div>

    <form id="formulario_maquina" method="POST" enctype="multipart/form-data">
        <table>
            <thead>
                <tr id="encabezados">
                    <th>Id de Máquina</th>
                    <th>Número de Serie</th>
                    <th>Id de Estado</th>
                    <th>Id de Ubicación</th>
                    <th>Modelo</th>
                    <th>Subir Imagen</th>
                </tr>
            </thead>
            <tr>
                <td>
                    <!-- Campo oculto para el ID de la máquina -->
                    <input type="hidden" name="id_maquina" value="<?php echo $id_maquina; ?>">
                    <?php echo $id_maquina; ?> <!-- Muestra el ID de la máquina en la celda -->
                </td>
                <td>
                    <input id="num_serie" type="text" name="numero_serie" value="<?php echo $numero_serie; ?>">
                </td>
                <td>
                    <select id="id_estado" name="id_estado">
                        <option value="1" <?php echo ($id_estado == "1") ? 'selected' : ''; ?>>1</option>
                        <option value="2" <?php echo ($id_estado == "2") ? 'selected' : ''; ?>>2</option>
                        <option value="3" <?php echo ($id_estado == "3") ? 'selected' : ''; ?>>3</option>
                    </select>
                </td>
                <td>
                    <select id="id_ubicacion" name="id_ubicacion">
                        <?php
                        $ubicaciones_query = "SELECT DISTINCT idubicacion FROM ubicacion";
                        $stm = $dbh->prepare($ubicaciones_query);
                        $stm->execute();
                        $ubicaciones = $stm->fetchAll(PDO::FETCH_COLUMN);

                        foreach ($ubicaciones as $ubicacion_option) {
                            $selected = ($id_ubicacion == $ubicacion_option) ? 'selected' : '';
                            echo "<option value='" . htmlspecialchars($ubicacion_option) . "' $selected>" . htmlspecialchars($ubicacion_option) . "</option>";
                        }
                        ?>
                    </select>
                </td>
                <td>
                    <select id="modelo" name="modelo">
                        <?php
                        $modelos_query = "SELECT DISTINCT modelo FROM maquina";
                        $stm = $dbh->prepare($modelos_query);
                        $stm->execute();
                        $modelos = $stm->fetchAll(PDO::FETCH_COLUMN);

                        foreach ($modelos as $modelo_option) {
                            $selected = ($modelo == $modelo_option) ? 'selected' : '';
                            echo "<option value='" . htmlspecialchars($modelo_option) . "' $selected>" . htmlspecialchars($modelo_option) . "</option>";
                        }
                        ?>
                    </select>
                </td>
                <td>
                    <input type="file" name="foto" accept="image/*">
                </td>
            </tr>
        </table>
        <input type="submit" value="Aplicar" id="aplicar">
    </form>
</body>

</html>