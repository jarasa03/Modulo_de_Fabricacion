<?php
// Inicia la sesión PHP, que permite manejar datos persistentes como cookies o variables de sesión.
session_start();

// Define la constante DOCROOT para especificar la ruta raíz del proyecto, facilitando el manejo de rutas.
define("DOCROOT", $_SERVER['DOCUMENT_ROOT'] . "/topvending");

// Incluye los archivos necesarios para manejar la base de datos y las funciones adicionales.
require_once DOCROOT . "/clases/basededatos.php";
require_once DOCROOT . '/clases/funciones.php';

// Conecta a la base de datos utilizando la función conectar() definida en uno de los archivos incluidos.
$dbh = conectar();

// Muestra el menú generado dinámicamente, utilizando la función crearMenu().
echo crearMenu($dbh);

// Recupera los valores de las cookies y los sanitiza para evitar problemas de seguridad como XSS.
$id_maquina = isset($_COOKIE['id_maquina']) ? htmlspecialchars($_COOKIE['id_maquina']) : '';
$numero_serie = isset($_COOKIE['numero_serie']) ? htmlspecialchars($_COOKIE['numero_serie']) : '';
$id_estado = isset($_COOKIE['id_estado']) ? htmlspecialchars($_COOKIE['id_estado']) : '';
$id_ubicacion = isset($_COOKIE['id_ubicacion']) ? htmlspecialchars($_COOKIE['id_ubicacion']) : '';
$modelo = isset($_COOKIE['modelo']) ? htmlspecialchars($_COOKIE['modelo']) : '';

// Comprueba si el formulario ha sido enviado (solicitud POST).
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    // Verifica si se ha subido un archivo de imagen y si no hubo errores en la subida.
    if (isset($_FILES['foto']) && $_FILES['foto']['error'] === UPLOAD_ERR_OK) {
        // Define la carpeta de destino para guardar las imágenes subidas.
        $uploadDir = DOCROOT . '/resources/';
        $fileName = basename($_FILES['foto']['name']); // Obtiene el nombre base del archivo.
        $targetFile = $uploadDir . $fileName; // Ruta completa al archivo de destino.

        // Lista de tipos de archivo permitidos.
        $allowedTypes = ['image/jpeg', 'image/png', 'image/gif'];
        $fileType = mime_content_type($_FILES['foto']['tmp_name']); // Determina el tipo MIME del archivo subido.

        // Verifica si el tipo de archivo es válido.
        if (in_array($fileType, $allowedTypes)) {
            // Mueve el archivo subido a la carpeta de destino.
            if (move_uploaded_file($_FILES['foto']['tmp_name'], $targetFile)) {
                // Genera la ruta relativa para almacenar en la base de datos.
                $fotoRuta = "/resources/" . $fileName;

                try {
                    // Comprueba que existe un ID de máquina válido.
                    if (!empty($id_maquina)) {
                        // Actualiza la ruta de la imagen en la base de datos.
                        $sql = "UPDATE maquina SET foto = :foto WHERE idmaquina = :idmaquina";
                        $stmt = $dbh->prepare($sql);
                        $stmt->bindParam(':foto', $fotoRuta);
                        $stmt->bindParam(':idmaquina', $id_maquina);
                        $stmt->execute();

                        // Registrar acción en el log
                        RegistrarLog("Data", "Imagen subida correctamente");
                        echo "<script>console.log('Imagen subida y ruta guardada correctamente para la máquina con ID: $id_maquina.');</script>";
                    } else {
                        echo "Error: No se encontró un ID de máquina válido en las cookies.";
                        // Registrar acción en el log
                        RegistrarLog("Error", "No se encontró un ID de máquina válido en las cookies");
                    }
                } catch (Exception $e) {
                    // Captura y muestra cualquier error ocurrido al actualizar la base de datos.
                    echo "Error al guardar la ruta de la foto: " . $e->getMessage();
                    // Registrar acción en el log
                    RegistrarLog("Error", "Error " . $e->getMessage() . " al guardar la foto");
                }
            } else {
                echo "Error al mover el archivo.";
                // Registrar acción en el log
                RegistrarLog("Error",  "Error al mover el archivo");

            }
        } else {
            echo "Tipo de archivo no permitido."; // Muestra un mensaje si el tipo de archivo no es válido.
            // Registrar acción en el log
            RegistrarLog("Error",  "Tipo de archivo no permitido");

        }
    }
}

// Verifica si se ha enviado una solicitud POST
if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['BorrarMaquina'])) {
    $id_maquina = htmlspecialchars($_POST['id_maquina']); // Escape básico para mayor seguridad

    if (!isset($_POST['confirm'])) {
        // Mostrar formulario de confirmación
        echo "<h2 id='confirmar'>¿Está seguro de que desea eliminar la máquina con ID: $id_maquina?</h2>";
        echo '<form method="post">
                <input type="hidden" name="id_maquina" value="' . $id_maquina . '">
                <input type="hidden" name="BorrarMaquina" value="1">
                <button type="submit" name="confirm" value="yes" id="conbtn">Sí, confirmar</button>
                <button type="submit" name="confirm" value="no" id="canbtn">No, cancelar</button>
              </form>';
    } else {
        if ($_POST['confirm'] === 'yes') {
            try {
                // Elimina los productos asociados a la máquina
                $delete_producto_sql = "DELETE FROM maquinaproducto WHERE idmaquina = :id";
                $stmt = $dbh->prepare($delete_producto_sql);
                $stmt->bindParam(":id", $id_maquina, PDO::PARAM_INT);
                $stmt->execute();

                // Elimina la máquina
                $delete_sql = "DELETE FROM maquina WHERE idmaquina = :id";
                $stmt = $dbh->prepare($delete_sql);
                $stmt->bindParam(':id', $id_maquina, PDO::PARAM_INT);
                $stmt->execute();

                // Registrar la acción en el log
                RegistrarLog("Data", "Máquina $id_maquina eliminada correctamente");
                echo "<script>console.log('Máquina eliminada correctamente');</script>";
            } catch (Exception $e) {
                // Manejo de errores
                echo "Error al eliminar la máquina: " . htmlspecialchars($e->getMessage());
                RegistrarLog("Error", "Error " . $e->getMessage() . " al eliminar la máquina con ID: $id_maquina");
            }

            // Redirigir para evitar el reenvío del formulario
            header("Location: ./fabricacion.php");
            exit;
        } else {
            echo "<script>alert('Acción cancelada');</script>";
            header("Location: ./fabricacion.php");
            exit;
        }
    }
}

// Verifica si se ha enviado una solicitud POST para aplicar cambios a una máquina.
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    if (isset($_POST['Aplicar'])) {
        // Recupera los datos del formulario.
        $id_maquina = $_POST['id_maquina'];
        $numero_serie = $_POST['numero_serie'];
        $id_estado = $_POST['id_estado'];
        $id_ubicacion = $_POST['id_ubicacion'];
        $modelo = $_POST['modelo'];

        // Verificar si el formulario de confirmación no ha sido enviado.
        if (!isset($_POST['confirmAplicar'])) {
            // Mostrar formulario de confirmación
            echo "<h2 id='confirmar'>¿Está seguro de que desea modificar la máquina con ID: $id_maquina?</h2>";
            echo '<form method="post">
                    <input type="hidden" name="id_maquina" value="' . $id_maquina . '">
                    <input type="hidden" name="numero_serie" value="' . $numero_serie . '">
                    <input type="hidden" name="id_estado" value="' . $id_estado . '">
                    <input type="hidden" name="id_ubicacion" value="' . $id_ubicacion . '">
                    <input type="hidden" name="modelo" value="' . $modelo . '">
                    <input type="hidden" name="Aplicar" value="1">
                    <button type="submit" name="confirmAplicar" value="yes" id="conbtn">Sí, confirmar</button>
                    <button type="submit" name="confirmAplicar" value="no" id="canbtn">No, cancelar</button>
                  </form>';
        } else {
            // Si se confirma la acción de aplicar los cambios
            if ($_POST['confirmAplicar'] === 'yes') {
                try {
                    // Actualiza los datos de la máquina en la base de datos.
                    $sql_update = "UPDATE maquina 
                                   SET numserie = :numserie, idestado = :idestado, idubicacion = :idubicacion, modelo = :modelo 
                                   WHERE idmaquina = :idmaquina";

                    $stmt_update = $dbh->prepare($sql_update);
                    $stmt_update->bindParam(':numserie', $numero_serie, PDO::PARAM_STR);
                    $stmt_update->bindParam(':idestado', $id_estado, PDO::PARAM_INT);
                    $stmt_update->bindParam(':idubicacion', $id_ubicacion, PDO::PARAM_INT);
                    $stmt_update->bindParam(':modelo', $modelo, PDO::PARAM_STR);
                    $stmt_update->bindParam(':idmaquina', $id_maquina, PDO::PARAM_INT);
                    $stmt_update->execute();

                    // Registrar acción en el log
                    RegistrarLog("Data", "Máquina $id_maquina actualizada correctamente");

                    header("Location: fabricacion.php");
                } catch (Exception $e) {
                    // Captura y muestra cualquier error ocurrido al actualizar la máquina.
                    echo "Error al actualizar la máquina: " . $e->getMessage();
                    // Registrar acción en el log
                    RegistrarLog("Error", "Error " . $e->getMessage() . " al actualizar la máquina con ID: $id_maquina");
                }
            } else {
                // Si se cancela, redirigir sin hacer nada
                echo "<script>alert('Acción cancelada');</script>";
            }
        }
    }
}
?>


<!DOCTYPE html>
<html lang="es">
<!-- Define el documento como HTML5 y establece el idioma como español. -->

<head>
    <meta charset="UTF-8">
    <!-- Especifica que el documento usa UTF-8, compatible con caracteres especiales en español. -->
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <!-- Habilita un diseño adaptable para diferentes tamaños de pantalla. -->
    <title>Máquinas</title>
    <!-- Título que aparecerá en la pestaña del navegador. -->
    <link rel="stylesheet" href="./css/maquinas.css">
    <!-- Vincula un archivo CSS específico para el estilo de la página. -->
    <link rel="stylesheet" href="/topvending/css/hallentrada.css">
    <!-- Vincula otro archivo CSS que podría contener estilos globales. -->
</head>

<body>
    <div id="imagen_maquina">
        <!-- Contenedor para mostrar la imagen de la máquina. -->
        <?php
        try {
            // Consulta para obtener la ruta de la imagen asociada a la máquina desde la base de datos.
            $sqlx = "SELECT foto FROM maquina WHERE idmaquina = :idmaquina";
            $stmt = $dbh->prepare($sqlx); // Prepara la consulta SQL.
            $stmt->bindParam(':idmaquina', $id_maquina); // Vincula el ID de la máquina al parámetro de la consulta.
            $stmt->execute(); // Ejecuta la consulta.
            $foto = $stmt->fetchColumn(); // Recupera el valor de la columna 'foto'.

            // Verifica si la imagen está definida o si se debe usar una imagen por defecto.
            if ($foto === "null") {
                echo "<img src='../resources/default.jpg' alt='Imagen por defecto'>";
            } else {
                echo "<img src='.." . $foto . "' alt='imagen establecida'>";
            }
        } catch (Exception $e) {
            // Captura y muestra cualquier error al intentar obtener la imagen.
            echo "Error al mostrar la foto: " . $e->getMessage();
            // Registrar acción en el log
            RegistrarLog("Error", "Error " . $e->getMessage()  . " al mostrar la foto");
        }
        ?>
    </div>

    <!-- Formulario principal para gestionar la información de la máquina. -->
    <form id="formulario_maquina" method="POST" enctype="multipart/form-data">
        <!-- Usa POST para enviar datos al servidor, incluyendo imágenes. -->
        <table id="tabla_princ">
            <!-- Define una tabla para organizar los datos de manera estructurada. -->
            <thead>
                <tr id="encabezados">
                    <!-- Cabeceras de la tabla, representan los campos que el usuario puede ver o modificar. -->
                    <th>Id de Máquina</th>
                    <th>Número de Serie</th>
                    <th>Id de Estado</th>
                    <th>Id de Ubicación</th>
                    <th>Modelo</th>
                    <th>Subir Imagen</th>
                </tr>
            </thead>
            <tr>
                <!-- Fila para los datos de entrada. -->
                <td>
                    <!-- Campo oculto que guarda el ID de la máquina, necesario para realizar actualizaciones. -->
                    <input type="hidden" name="id_maquina" value="<?php echo $id_maquina; ?>">
                    <?php echo $id_maquina; ?> <!-- Muestra el ID de la máquina en la celda. -->
                </td>
                <td>
                    <!-- Campo de texto para el número de serie de la máquina. -->
                    <input id="num_serie" type="text" name="numero_serie" value="<?php echo $numero_serie; ?>">
                </td>
                <td>
                    <!-- Selector desplegable para el estado de la máquina. -->
                    <select id="id_estado" name="id_estado">
                        <!-- Opciones predefinidas con selección dinámica basada en el valor actual. -->
                        <option value="1" <?php echo ($id_estado == "1") ? 'selected' : ''; ?>>1</option>
                        <option value="2" <?php echo ($id_estado == "2") ? 'selected' : ''; ?>>2</option>
                        <option value="3" <?php echo ($id_estado == "3") ? 'selected' : ''; ?>>3</option>
                    </select>
                </td>
                <td>
                    <!-- Selector desplegable para la ubicación de la máquina. -->
                    <select id="id_ubicacion" name="id_ubicacion">
                        <?php
                        // Consulta para obtener todas las ubicaciones distintas de la base de datos.
                        $ubicaciones_query = "SELECT DISTINCT idubicacion FROM ubicacion";
                        $stm = $dbh->prepare($ubicaciones_query); // Prepara la consulta SQL.
                        $stm->execute(); // Ejecuta la consulta.
                        $ubicaciones = $stm->fetchAll(PDO::FETCH_COLUMN); // Recupera todas las ubicaciones.

                        // Crea una opción para cada ubicación, marcando como seleccionada la actual.
                        foreach ($ubicaciones as $ubicacion_option) {
                            $selected = ($id_ubicacion == $ubicacion_option) ? 'selected' : '';
                            echo "<option value='" . htmlspecialchars($ubicacion_option) . "' $selected>" . htmlspecialchars($ubicacion_option) . "</option>";
                        }
                        ?>
                    </select>
                </td>
                <td>
                    <!-- Selector desplegable para los modelos de máquina. -->
                    <select id="modelo" name="modelo">
                        <?php
                        // Consulta para obtener todos los modelos distintos de la base de datos.
                        $modelos_query = "SELECT DISTINCT modelo FROM maquina";
                        $stm = $dbh->prepare($modelos_query);
                        $stm->execute();
                        $modelos = $stm->fetchAll(PDO::FETCH_COLUMN);

                        // Crea una opción para cada modelo, marcando como seleccionado el actual.
                        foreach ($modelos as $modelo_option) {
                            $selected = ($modelo == $modelo_option) ? 'selected' : '';
                            echo "<option value='" . htmlspecialchars($modelo_option) . "' $selected>" . htmlspecialchars($modelo_option) . "</option>";
                        }
                        ?>
                    </select>
                </td>
                <td>
                    <!-- Entrada para subir una imagen, acepta solo archivos de tipo imagen. -->
                    <input type="file" name="foto" accept="image/*">
                </td>
            </tr>
        </table>
        <!-- Botón para aplicar los cambios. -->
        <input type="submit" name="Aplicar" value="Aplicar" id="aplicar">
        <!-- Botón para eliminar la máquina. -->
        <button type="submit" name="BorrarMaquina" id="botonBorrar">Eliminar</button>
    </form>
</body>

</html>