<?php
// Inicia una nueva sesión o reanuda la existente
session_start();

// Define la constante DOCROOT con la raíz del documento del servidor y la ruta hacia la carpeta 'topvending'
define("DOCROOT", $_SERVER['DOCUMENT_ROOT'] . "/topvending");

// Incluye los archivos necesarios para trabajar con la base de datos y funciones adicionales
require_once DOCROOT . "/clases/basededatos.php";
require_once DOCROOT . '/clases/funciones.php';

// Establece una conexión con la base de datos
$dbh = conectar();

// Muestra el menú, llamando a la función 'crearMenu' pasando la conexión a la base de datos
echo crearMenu($dbh);

// Mapeo de modelos de máquinas a capacidades (se define la capacidad dependiendo del modelo)
$modelos_capacidad = [
    'STAR24' => 20,
    'STAR30' => 30,
    'STAR42' => 40
];

// Variables iniciales para el formulario
$capacidad = '';  // Capacidad por defecto vacía
$modelo = '';      // Modelo por defecto vacío
$numero_serie = '';  // Número de serie por defecto vacío
$id_estado = '';  // Estado de la máquina por defecto vacío
$id_ubicacion = '';  // Ubicación de la máquina por defecto vacío
$stockmax = '';  // Stock máximo por defecto vacío
$foto = 'null';  // Foto por defecto se establece como 'null'

// Comienza a procesar el formulario si la solicitud es POST (cuando el usuario envía datos)
if ($_SERVER['REQUEST_METHOD'] === 'POST') {

    // Verifica si se ha seleccionado un modelo y si no está vacío
    if (isset($_POST['modelo']) && !empty($_POST['modelo'])) {
        // Asigna el valor del modelo seleccionado
        $modelo = htmlspecialchars($_POST['modelo']);
        // Asigna la capacidad correspondiente al modelo seleccionado
        $capacidad = $modelos_capacidad[$modelo] ?? '';
        // Establece el stock máximo igual a la capacidad seleccionada
        $stockmax = $capacidad;
    }

    // Verifica si el botón de acción es 'Aplicar', lo que indica que el formulario debe procesarse
    if (isset($_POST['accion']) && $_POST['accion'] === 'Aplicar') {
        // Verifica si la capacidad o el stock máximo están vacíos
        if (empty($capacidad) || empty($stockmax)) {
            // Muestra un mensaje de error si no se ha seleccionado un modelo
            echo "<p style='color: red;'>Por favor, seleccione un modelo para definir la capacidad y el stock máximo.</p>";
            // Registrar acción en el log
            RegistrarLog("Error", "No se seleccionó un modelo válido");
        } else {
            // Si todo está correcto, obtenemos los valores del formulario
            $numero_serie = htmlspecialchars($_POST['numero_serie']);
            $id_estado = htmlspecialchars($_POST['id_estado']);
            $id_ubicacion = htmlspecialchars($_POST['id_ubicacion']);
            $modelo = htmlspecialchars($_POST['modelo']);

            // Procesa la foto si el usuario ha subido una imagen
            if (!empty($_FILES['foto']['name'])) {
                // Define el directorio donde se guardarán las fotos subidas
                $uploads_dir = DOCROOT . '/resources/';
                // Si el directorio no existe, lo crea con permisos 0755
                if (!is_dir($uploads_dir)) {
                    mkdir($uploads_dir, 0755, true);
                }

                // Obtiene el nombre del archivo subido
                $file_name = htmlspecialchars(basename($_FILES['foto']['name']));
                // Obtiene el archivo temporal de la foto subida
                $file_tmp = $_FILES['foto']['tmp_name'];
                // Define la ruta completa donde se guardará el archivo
                $file_path = $uploads_dir . $file_name;

                // Intenta mover el archivo desde su ubicación temporal a la ruta final
                if (move_uploaded_file($file_tmp, $file_path)) {
                    // Si se mueve correctamente, asigna el nombre del archivo a la variable foto
                    $foto = $file_name;
                } else {
                    // Si hay un error al subir la foto, muestra un mensaje de error
                    echo "<p style='color: red;'>Error al subir la foto.</p>";
                    // Registrar acción en el log
                    RegistrarLog("Error",  "Error al subir la foto");
                }
            }

            // Preparación de la consulta SQL para insertar los datos en la base de datos
            $sql = "INSERT INTO maquina (
                        numserie, idestado, idubicacion, capacidad, stockmax, modelo, foto
                    ) VALUES (
                        :numserie, :idestado, :idubicacion, :capacidad, :stockmax, :modelo, :foto
                    )";

            // Prepara la consulta con la conexión a la base de datos
            $stmt = $dbh->prepare($sql);
            // Asocia los parámetros de la consulta con los valores del formulario
            $stmt->bindParam(':numserie', $numero_serie);
            $stmt->bindParam(':idestado', $id_estado);
            $stmt->bindParam(':idubicacion', $id_ubicacion);
            $stmt->bindParam(':capacidad', $capacidad);
            $stmt->bindParam(':stockmax', $stockmax);
            $stmt->bindParam(':modelo', $modelo);
            // Asigna el valor de la foto (que puede ser 'null' o el nombre del archivo subido)
            $stmt->bindValue(':foto', $foto, PDO::PARAM_STR);

            try {
                // Intenta ejecutar la consulta
                $stmt->execute();
                // Registrar acción en el log
                RegistrarLog("Data", "Se añadió una nueva máquina $id_maquina con el número de serie:  $numero_serie");
                // Si la inserción es exitosa, redirige a la página 'fabricacion.php'
                header("Location: ./fabricacion.php");
                exit;
            } catch (PDOException $e) {
                // Si ocurre un error en la consulta, muestra un mensaje de error
                echo "<p style='color: red;'>Error al insertar la máquina: " . htmlspecialchars($e->getMessage()) . "</p>";
                // Registrar acción en el log
                RegistrarLog("Error", "Error en la consulta: " . $e->getMessage());
            }
        }
    }
}
?>


<!DOCTYPE html>
<html lang="es">

<head>
    <!-- Definir la codificación de caracteres como UTF-8 para soporte de caracteres especiales -->
    <meta charset="UTF-8">

    <!-- Título de la página que aparecerá en la pestaña del navegador -->
    <title>Añadir Máquina</title>

    <!-- Enlazar hojas de estilo CSS para la página -->
    <link rel="stylesheet" href="/topvending/css/hallentrada.css">
    <link rel="stylesheet" href="/topvending/m2/css/maquinas.css">
    <link rel="stylesheet" href="/topvending/m2/css/modificar_ubicaciones.css">
</head>

<body>
    <!-- Formulario para añadir una nueva máquina -->
    <form id="formulario_maquina" method="POST" enctype="multipart/form-data">
        <!-- Tabla para organizar los campos del formulario -->
        <table id="tablita">
            <thead>
                <!-- Encabezados de la tabla que describen los campos de entrada -->
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
                    <!-- Campo de entrada para el número de serie -->
                    <td>
                        <input type="text" name="numero_serie" value="<?php echo htmlspecialchars($numero_serie); ?>" required>
                    </td>

                    <!-- Selección de estado, llenado dinámico con datos de la base de datos -->
                    <td>
                        <select id="id_estado" name="id_estado" required>
                            <?php
                            // Consulta para obtener los estados disponibles en la base de datos
                            $estados_query = "SELECT DISTINCT idestado FROM estado";
                            $stmt = $dbh->prepare($estados_query);
                            $stmt->execute();
                            $estados = $stmt->fetchAll(PDO::FETCH_COLUMN);
                            // Itera sobre los estados y los muestra como opciones
                            foreach ($estados as $estado_option) {
                                // Marca la opción seleccionada si coincide con el estado actual
                                $selected = ($id_estado == $estado_option) ? 'selected' : '';
                                echo "<option value='" . htmlspecialchars($estado_option) . "' $selected>" . htmlspecialchars($estado_option) . "</option>";
                            }
                            ?>
                        </select>
                    </td>

                    <!-- Selección de ubicación, llenado dinámico con datos de la base de datos -->
                    <td>
                        <select id="id_ubicacion" name="id_ubicacion" required>
                            <?php
                            // Consulta para obtener las ubicaciones disponibles en la base de datos
                            $ubicaciones_query = "SELECT DISTINCT idubicacion FROM ubicacion";
                            $stmt = $dbh->prepare($ubicaciones_query);
                            $stmt->execute();
                            $ubicaciones = $stmt->fetchAll(PDO::FETCH_COLUMN);
                            // Itera sobre las ubicaciones y las muestra como opciones
                            foreach ($ubicaciones as $ubicacion_option) {
                                // Marca la opción seleccionada si coincide con la ubicación actual
                                $selected = ($ubicacion == $ubicacion_option) ? 'selected' : '';
                                echo "<option value='" . htmlspecialchars($ubicacion_option) . "' $selected>" . htmlspecialchars($ubicacion_option) . "</option>";
                            }
                            ?>
                        </select>
                    </td>

                    <!-- Selección de modelo, llenado dinámico con datos de la base de datos -->
                    <td>
                        <select id="modelo" name="modelo" required>
                            <?php
                            // Consulta para obtener los modelos de máquinas ordenados numéricamente
                            $modelos_query = "SELECT DISTINCT modelo FROM maquina ORDER BY CAST(SUBSTRING(modelo, 5) AS UNSIGNED)";
                            $stmt = $dbh->prepare($modelos_query);
                            $stmt->execute();
                            $modelos = $stmt->fetchAll(PDO::FETCH_COLUMN);
                            // Itera sobre los modelos y los muestra como opciones
                            foreach ($modelos as $modelo_option) {
                                // Marca la opción seleccionada si coincide con el modelo actual
                                $selected = ($modelo == $modelo_option) ? 'selected' : '';
                                echo "<option value='" . htmlspecialchars($modelo_option) . "' $selected>" . htmlspecialchars($modelo_option) . "</option>";
                            }
                            ?>
                        </select>
                    </td>

                    <!-- Campo de entrada para cargar una foto -->
                    <td>
                        <input type="file" name="foto">
                    </td>
                </tr>
            </tbody>
        </table>

        <!-- Botón de envío para aplicar los cambios -->
        <div style="text-align: center;">
            <input type="submit" name="accion" value="Aplicar" id="aplicando">
        </div>
    </form>
</body>

</html>