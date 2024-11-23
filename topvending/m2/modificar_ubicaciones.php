<?php
// Inicia la sesión para habilitar el uso de variables de sesión.
session_start();

// Define la constante DOCROOT con la ruta base del proyecto en el servidor.
define("DOCROOT", $_SERVER['DOCUMENT_ROOT'] . "/topvending");

// Incluye la clase de conexión a la base de datos.
require_once DOCROOT . "/clases/basededatos.php";

// Incluye un archivo con funciones adicionales que se usarán en el script.
require_once DOCROOT . '/clases/funciones.php';

// Establece la conexión a la base de datos utilizando una función definida en 'basededatos.php'.
$dbh = conectar();

// Genera y muestra el menú principal utilizando una función personalizada.
echo crearMenu($dbh);

// Recupera el valor de las cookies, si están definidas, y los convierte en datos seguros.
$idubicacion = isset($_COOKIE['idubicacion']) ? htmlspecialchars($_COOKIE['idubicacion']) : '';
$cliente = isset($_COOKIE['cliente']) ? htmlspecialchars($_COOKIE['cliente']) : '';
$dir = isset($_COOKIE['dir']) ? htmlspecialchars($_COOKIE['dir']) : '';

// Divide la cookie 'dir' en un array utilizando el carácter ';' como separador.
$direccion = explode(";", $dir);

// Asigna cada posición del array a una variable para trabajar más fácilmente.
$calle = $direccion[0];
$num_portal = $direccion[1];
$cod_postal = $direccion[2];
$provincia = $direccion[3];

// Comprueba si se ha enviado una solicitud POST (normalmente por un formulario).
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    try {
        // Verifica si se envió un 'idubicacion' en la solicitud POST.
        if (isset($_POST['idubicacion'])) {
            $idubicacion = $_POST['idubicacion']; // Asigna el valor a la variable.

            // Comprueba si se presionó el botón 'BorrarMaquina'.
            if (isset($_POST['BorrarMaquina'])) {
                // Elimina los registros relacionados en la tabla 'maquinaproducto' que dependen de la ubicación.
                $sql_maquinaproducto = "DELETE FROM maquinaproducto WHERE idmaquina IN (SELECT idmaquina FROM maquina WHERE idubicacion = :idubicacion)";
                $stmt_maquinaproducto = $dbh->prepare($sql_maquinaproducto);
                $stmt_maquinaproducto->bindParam(':idubicacion', $idubicacion, PDO::PARAM_INT);
                $stmt_maquinaproducto->execute();

                // Actualiza las máquinas asociadas a la ubicación eliminada, asignándolas a una ubicación genérica (ID 1).
                $sql_maquinas = "UPDATE maquina SET idubicacion = 1 WHERE idubicacion = :idubicacion";
                $stmt_maquinas = $dbh->prepare($sql_maquinas);
                $stmt_maquinas->bindParam(':idubicacion', $idubicacion, PDO::PARAM_INT);
                $stmt_maquinas->execute();

                // Elimina la ubicación seleccionada de la base de datos.
                $sql = "DELETE FROM ubicacion WHERE idubicacion = :idubicacion";
                $stmt = $dbh->prepare($sql);
                $stmt->bindParam(':idubicacion', $idubicacion, PDO::PARAM_INT);
                $stmt->execute();

                // Registrar acción en el log
                RegistrarLog("Data",  "Ubicación de la máquina $id_maquina actualizada y ubicación $id_ubicacion borrada");
                // Redirige al usuario a otra página después de eliminar la ubicación.
                header("Location: fabricacion.php");
                exit; // Termina la ejecución del script después de la redirección.
            }

            // Comprueba si se presionó el botón 'Aplicar'.
            if (isset($_POST['Aplicar'])) {
                // Recupera los datos enviados por el formulario y los asigna a variables.
                $cliente = $_POST['cliente'];
                $calle = $_POST['calle'];
                $num_portal = $_POST['num_portal'];
                $cod_postal = $_POST['cod_postal'];
                $provincia = $_POST['provincia'];

                // Combina los datos de dirección en un único string separado por ';'.
                $dir = "$calle;$num_portal;$cod_postal;$provincia";

                try {
                    // Actualiza la información de la ubicación en la base de datos.
                    $sql_update = "UPDATE ubicacion SET cliente = :cliente, dir = :dir WHERE idubicacion = :idubicacion";
                    $stmt_update = $dbh->prepare($sql_update);
                    $stmt_update->bindParam(':cliente', $cliente, PDO::PARAM_STR);
                    $stmt_update->bindParam(':dir', $dir, PDO::PARAM_STR);
                    $stmt_update->bindParam(':idubicacion', $idubicacion, PDO::PARAM_INT);
                    $stmt_update->execute();

                    // Registrar acción en el log
                    RegistrarLog("Data",  "Ubicación $id_ubicacion modificada");
                    // Redirige a otra página
                    header("Location: fabricacion.php");
                    exit;
                } catch (Exception $e) {
                    // Maneja errores durante la actualización de la base de datos.
                    echo "<p>Error al actualizar la ubicación: " . $e->getMessage() . "</p>";
                    // Registrar acción en el log
                    RegistrarLog("Error", "Error " . $e->getMessage() . " al actualizar la ubicación: $id_ubicacion.");
                }
            }
        }
    } catch (Exception $e) {
        // Maneja errores generales durante el procesamiento de la solicitud POST.
        echo "<p>Error al eliminar la ubicación: " . $e->getMessage() . "</p>";
        RegistrarLog("Error", "Error ". $e->getMessage() . " al eliminar la ubicación");
    }
}
?>

<!DOCTYPE html>
<html lang="es">
<!-- Se declara que el documento está en español -->

<head>
    <!-- Metaetiquetas para la codificación de caracteres y la configuración de la vista en dispositivos móviles -->
    <meta charset="UTF-8"> <!-- Establece la codificación de caracteres a UTF-8 para permitir caracteres especiales (acentos, etc.) -->
    <meta name="viewport" content="width=device-width, initial-scale=1.0"> <!-- Asegura que el diseño sea adaptable a pantallas móviles -->

    <title>Modificar Ubicaciones</title> <!-- Título de la página que aparecerá en la pestaña del navegador -->

    <!-- Enlaces a archivos CSS que estilizan la página -->
    <link rel="stylesheet" href="/topvending/css/hallentrada.css"> <!-- Estilo general de la página -->
    <link rel="stylesheet" href="/topvending/m2/css/maquinas.css"> <!-- Estilo específico para máquinas -->
    <link rel="stylesheet" href="/topvending/m2/css/modificar_ubicaciones.css"> <!-- Estilo para la página de modificación de ubicaciones -->

</head>

<body>
    <!-- Formulario para modificar o eliminar una ubicación -->
    <form id="formulario_maquina" method="POST" enctype="multipart/form-data">
        <!-- Tabla para mostrar y editar la información de una ubicación -->
        <table id="tablita">
            <!-- Cabecera de la tabla -->
            <thead>
                <tr id="encabezados">
                    <th>Id de Ubicación</th> <!-- Título de la columna para el ID de ubicación -->
                    <th>Cliente</th> <!-- Título de la columna para el nombre del cliente -->
                    <th>Calle</th> <!-- Título de la columna para la calle -->
                    <th>Número de Portal</th> <!-- Título de la columna para el número de portal -->
                    <th>Código Postal</th> <!-- Título de la columna para el código postal -->
                    <th>Provincia</th> <!-- Título de la columna para la provincia -->
                </tr>
            </thead>

            <!-- Fila de la tabla para los datos de una ubicación -->
            <tr>
                <td>
                    <!-- Campo oculto que contiene el ID de la ubicación, necesario para identificar y actualizar la ubicación -->
                    <input type="hidden" name="idubicacion" value="<?php echo $idubicacion; ?>">
                    <!-- Muestra el ID de la ubicación en la celda para que sea visible en la interfaz -->
                    <?php echo $idubicacion; ?>
                </td>

                <td>
                    <!-- Campo de texto para el nombre del cliente -->
                    <input required id="cliente" type="text" name="cliente" value="<?php echo $cliente; ?>">
                </td>

                <td>
                    <!-- Campo de texto para la calle de la ubicación -->
                    <input required id="calle" type="text" name="calle" value="<?php echo $calle; ?>">
                </td>

                <td>
                    <!-- Campo de texto para el número de portal de la ubicación -->
                    <input required id="num_portal" type="number" name="num_portal" value="<?php echo $num_portal; ?>">
                </td>

                <td>
                    <!-- Campo de texto para el código postal de la ubicación -->
                    <input required id="cod_postal" type="number" name="cod_postal" value="<?php echo $cod_postal; ?>">
                </td>

                <td>
                    <!-- Campo de texto para la provincia de la ubicación -->
                    <input required id="provincia" type="text" name="provincia" value="<?php echo $provincia; ?>">
                </td>
            </tr>
        </table>

        <!-- Botón para enviar los datos modificados -->
        <input type="submit" name="Aplicar" value="Aplicar" id="aplicando">

        <!-- Botón para eliminar la ubicación -->
        <button type="submit" name="BorrarMaquina" id="botonBorrar">Eliminar</button>
    </form>
</body>

</html>