<?php
session_start();
define("DOCROOT", $_SERVER['DOCUMENT_ROOT'] . "/topvending");
require_once DOCROOT . "/clases/basededatos.php";
require_once DOCROOT . '/clases/funciones.php';
$dbh = conectar();

// Sanitización del menú generado dinámicamente
echo crearMenu($dbh);
?>

<?php
try {
    if ($_SERVER['REQUEST_METHOD'] === 'POST') {
        // Recibir y sanitizar los datos del formulario
        $numero_serie = htmlspecialchars($_POST['numero_serie']);
        $id_estado = htmlspecialchars($_POST['id_estado']);
        $id_ubicacion = htmlspecialchars($_POST['id_ubicacion']);
        $capacidad = htmlspecialchars($_POST['capacidad']);
        $stockmax = htmlspecialchars($_POST['stockmax']);
        $modelo = htmlspecialchars($_POST['modelo']);

        // Foto por defecto
        $foto = 'default.jpg';

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
                $foto = $file_name;
            } else {
                echo "<p style='color: red;'>Error al subir la foto.</p>";
            }
        }

        // Crear la consulta SQL
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
        $stmt->bindParam(':foto', $foto);

        try {
            $stmt->execute();
            echo "<script>console.log('Nueva máquina insertada correctamente');</script>";
            header("Location: ./fabricacion.php");
            exit;
        } catch (PDOException $e) {
            echo "<p style='color: red;'>Error al insertar la máquina: " . htmlspecialchars($e->getMessage()) . "</p>";
        }
    }
} catch (Exception $e) {
    echo "<p style='color: red;'>Error: " . htmlspecialchars($e->getMessage()) . "</p>";
}
?>

<!DOCTYPE html>
<html lang="es">

<head>
    <meta charset="UTF-8">
    <title>Insertar Máquina</title>
    <!--<link rel="stylesheet" href="./css/maquinas.css">-->
    <link rel="stylesheet" href="./css/anadir_maquina_y_ubicacion.css">
    <link rel="stylesheet" href="/topvending/css/hallentrada.css">

</head>

<body>
    <header>
        <button onclick="window.location.href='../../login.php'">Cerrar sesión</button>
    </header>

    <h1>Insertar Nueva Máquina</h1>

    <form id="formulario_maquina" method="POST" enctype="multipart/form-data">
        <table id="tabla_anadir_maquina">
            <thead>
                <tr>
                    <th>Número de Serie</th>
                    <th>Estado</th>
                    <th>Ubicación</th>
                    <th>Capacidad</th>
                    <th>Stock Máximo</th>
                    <th>Modelo</th>
                    <th>Foto</th>
                </tr>
            </thead>
            <tbody>
                <tr>
                    <!-- Sanitizar valores predeterminados (si se usan en futuros ajustes de formulario) -->
                    <td id="maquina"><input id="num_serie" type="text" name="numero_serie" value="<?php echo htmlspecialchars($numero_serie ?? ''); ?>" placeholder="Campo obligatorio" required></td>
                    <td id="maquina">
                        <select id="id_estado" name="id_estado" required>
                            <option value="1" <?php echo (isset($id_estado) && $id_estado == '1') ? 'selected' : ''; ?>>1</option>
                            <option value="2" <?php echo (isset($id_estado) && $id_estado == '2') ? 'selected' : ''; ?>>2</option>
                            <option value="3" <?php echo (isset($id_estado) && $id_estado == '3') ? 'selected' : ''; ?>>3</option>
                        </select>
                    </td>
                    <td id="maquina"><input id="id_ubicacion" type="number" name="id_ubicacion" value="<?php echo htmlspecialchars($id_ubicacion ?? ''); ?>" placeholder="Campo obligatorio" required></td>
                    <td id="maquina"><input id="capacidad" type="number" name="capacidad" value="<?php echo htmlspecialchars($capacidad ?? ''); ?>" placeholder="Campo obligatorio" required></td>
                    <td id="maquina"><input id="stockmax" type="number" name="stockmax" value="<?php echo htmlspecialchars($stockmax ?? ''); ?>" placeholder="Campo obligatorio" required></td>
                    <td id="maquina">
                        <select id="modelo" name="modelo" required>
                            <option value="">Todos</option>
                            <?php
                            $modelos_query = "SELECT DISTINCT modelo FROM maquina";
                            $stmt = $dbh->prepare($modelos_query);
                            ?>
                        </select>
                    </td>
                    <td id="maquina"><input id="foto" type="file" name="foto"></td>
                </tr>
            </tbody>
        </table>
        <div style="text-align: center;">
            <button type="submit" id="btn_insertar">Insertar</button>
        </div>
    </form>
</body>

</html>
