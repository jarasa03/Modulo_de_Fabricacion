<?php
session_start();
define("DOCROOT", $_SERVER['DOCUMENT_ROOT'] . "/topvending");
require_once DOCROOT . "/clases/basededatos.php";
require_once DOCROOT . '/clases/funciones.php';
$dbh = conectar();
echo crearMenu($dbh);

// Mapeo de modelos a capacidades
$modelos_capacidad = [
    'STAR24' => 20,
    'STAR30' => 30,
    'STAR42' => 40
];

// Variables iniciales para el formulario
$capacidad = '';
$modelo = '';
$numero_serie = '';
$id_estado = '';
$id_ubicacion = '';
$stockmax = '';
$foto = 'null';  // Valor por defecto "null" para la foto

// Procesar formulario al seleccionar un modelo
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    // Verificar si se ha seleccionado un modelo
    if (isset($_POST['modelo']) && !empty($_POST['modelo'])) {
        $modelo = htmlspecialchars($_POST['modelo']);
        // Asignar capacidad según el modelo seleccionado
        $capacidad = $modelos_capacidad[$modelo] ?? '';
        // Establecer el stockmax igual a la capacidad por defecto
        $stockmax = $capacidad;
    }

    // Si el botón "Insertar" es presionado, procesar la inserción
    if (isset($_POST['accion']) && $_POST['accion'] === 'Aplicar') {
        // Verificar que capacidad y stockmax no sean vacíos
        if (empty($capacidad) || empty($stockmax)) {
            echo "<p style='color: red;'>Por favor, seleccione un modelo para definir la capacidad y el stock máximo.</p>";
        } else {
            // Obtener los valores del formulario
            $numero_serie = htmlspecialchars($_POST['numero_serie']);
            $id_estado = htmlspecialchars($_POST['id_estado']);
            $id_ubicacion = htmlspecialchars($_POST['id_ubicacion']);
            $modelo = htmlspecialchars($_POST['modelo']);

            // Procesar la foto si se ha subido
            if (!empty($_FILES['foto']['name'])) {
                $uploads_dir = DOCROOT . '/resources/';
                if (!is_dir($uploads_dir)) {
                    mkdir($uploads_dir, 0755, true);
                }

                $file_name = htmlspecialchars(basename($_FILES['foto']['name']));
                $file_tmp = $_FILES['foto']['tmp_name'];
                $file_path = $uploads_dir . $file_name;

                if (move_uploaded_file($file_tmp, $file_path)) {
                    $foto = $file_name;  // Asignar el nombre del archivo a $foto
                } else {
                    echo "<p style='color: red;'>Error al subir la foto.</p>";
                }
            }

            // Realizar la inserción en la base de datos
            $sql = "INSERT INTO maquina (
                        numserie, idestado, idubicacion, capacidad, stockmax, modelo, foto
                    ) VALUES (
                        :numserie, :idestado, :idubicacion, :capacidad, :stockmax, :modelo, :foto
                    )";

            $stmt = $dbh->prepare($sql);
            $stmt->bindParam(':numserie', $numero_serie);
            $stmt->bindParam(':idestado', $id_estado);
            $stmt->bindParam(':idubicacion', $id_ubicacion);
            $stmt->bindParam(':capacidad', $capacidad);
            $stmt->bindParam(':stockmax', $stockmax);
            $stmt->bindParam(':modelo', $modelo);
            // Siempre asignamos el valor de foto, que puede ser "null" o el nombre del archivo
            $stmt->bindValue(':foto', $foto, PDO::PARAM_STR);

            try {
                $stmt->execute();
                header("Location: ./fabricacion.php");
                exit;
            } catch (PDOException $e) {
                echo "<p style='color: red;'>Error al insertar la máquina: " . htmlspecialchars($e->getMessage()) . "</p>";
            }
        }
    }
}
?>

<!DOCTYPE html>
<html lang="es">

<head>
    <meta charset="UTF-8">
    <title>Añadir Máquina</title>
    <link rel="stylesheet" href="/topvending/css/hallentrada.css">
    <link rel="stylesheet" href="/topvending/m2/css/maquinas.css">
    <link rel="stylesheet" href="/topvending/m2/css/modificar_ubicaciones.css">
</head>

<body>

    <form id="formulario_maquina" method="POST" enctype="multipart/form-data">
        <table id="tablita">
            <thead>
                <tr id="encabezados">
                    <th>Número de Serie</th>
                    <th>Estado</th>
                    <th>Ubicación</th>
                    <th>Modelo</th>
                    <th>Foto</th>
                </tr>
            </thead>
            <tbody>
                <tr>
                    <td><input type="text" name="numero_serie" value="<?php echo htmlspecialchars($numero_serie); ?>" required></td>
                    <td>
                        <select id="id_estado" name="id_estado" required>
                            <?php
                            $estados_query = "SELECT DISTINCT idestado FROM estado";
                            $stmt = $dbh->prepare($estados_query);
                            $stmt->execute();
                            $estados = $stmt->fetchAll(PDO::FETCH_COLUMN);
                            foreach ($estados as $estado_option) {
                                $selected = ($id_estado == $estado_option) ? 'selected' : '';
                                echo "<option value='" . htmlspecialchars($estado_option) . "' $selected>" . htmlspecialchars($estado_option) . "</option>";
                            }
                            ?>
                        </select>
                    </td>
                    <td>
                    <select id="id_ubicacion" name="id_ubicacion" required>
                            <?php
                            $ubicaciones_query = "SELECT DISTINCT idubicacion FROM ubicacion";
                            $stmt = $dbh->prepare($ubicaciones_query);
                            $stmt->execute();
                            $ubicaciones = $stmt->fetchAll(PDO::FETCH_COLUMN);
                            foreach ($ubicaciones as $ubicacion_option) {
                                $selected = ($ubicacion == $ubicacion_option) ? 'selected' : '';
                                echo "<option value='" . htmlspecialchars($ubicacion_option) . "' $selected>" . htmlspecialchars($ubicacion_option) . "</option>";
                            }
                            ?>
                        </select>
                    </td>
                    <td>
                        <select id="modelo" name="modelo" required>
                            <?php
                            $modelos_query = "SELECT DISTINCT modelo FROM maquina ORDER BY CAST(SUBSTRING(modelo, 5) AS UNSIGNED)";
                            $stmt = $dbh->prepare($modelos_query);
                            $stmt->execute();
                            $modelos = $stmt->fetchAll(PDO::FETCH_COLUMN);
                            foreach ($modelos as $modelo_option) {
                                $selected = ($modelo == $modelo_option) ? 'selected' : '';
                                echo "<option value='" . htmlspecialchars($modelo_option) . "' $selected>" . htmlspecialchars($modelo_option) . "</option>";
                            }
                            ?>
                        </select>
                    </td>
                    <td><input type="file" name="foto"></td>
                </tr>
            </tbody>
        </table>
        <div style="text-align: center;">
            <input type="submit" name="accion" value="Aplicar" id="aplicando">
        </div>
    </form>
</body>

</html>
