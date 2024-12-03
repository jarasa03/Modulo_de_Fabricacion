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

if (count($direccion) == 4) {
// Asigna cada posición del array a una variable para trabajar más fácilmente.
$calle = $direccion[0];
$num_portal = $direccion[1];
$cod_postal = $direccion[2];
$provincia = $direccion[3];
} else {
    // Combina los datos de dirección en un único string separado por ';'.
    $dir = "$calle;$num_portal;$cod_postal;$provincia";
}

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    try {
        if (isset($_POST['BorrarUbicacion'])) {
            $idubicacion = $_POST['idubicacion']; 

            if (!isset($_POST['confirm'])) {
                // Mostrar formulario de confirmación
                echo "<h2 id='confirmar'>¿Está seguro de que desea eliminar la ubicación con ID: $idubicacion?</h2>";
                echo '<form method="post">
                        <input type="hidden" name="idubicacion" value="' . $idubicacion . '">
                        <input type="hidden" name="BorrarUbicacion" value="1">
                        <button type="submit" name="confirm" value="yes" id="conbtn">Sí, confirmar</button>
                        <button type="submit" name="confirm" value="no" id="canbtn">No, cancelar</button>
                      </form>';
            } else {
                if ($_POST['confirm'] === 'yes') {
                    // Procesar la eliminación
                    $sql_maquinaproducto = "DELETE FROM maquinaproducto 
                                            WHERE idmaquina IN (SELECT idmaquina FROM maquina WHERE idubicacion = :idubicacion)";
                    $stmt_maquinaproducto = $dbh->prepare($sql_maquinaproducto);
                    $stmt_maquinaproducto->bindParam(':idubicacion', $idubicacion, PDO::PARAM_INT);
                    $stmt_maquinaproducto->execute();

                    $sql_maquinas = "UPDATE maquina SET idubicacion = 1 WHERE idubicacion = :idubicacion";
                    $stmt_maquinas = $dbh->prepare($sql_maquinas);
                    $stmt_maquinas->bindParam(':idubicacion', $idubicacion, PDO::PARAM_INT);
                    $stmt_maquinas->execute();

                    $sql_delete = "DELETE FROM ubicacion WHERE idubicacion = :idubicacion";
                    $stmt_delete = $dbh->prepare($sql_delete);
                    $stmt_delete->bindParam(':idubicacion', $idubicacion, PDO::PARAM_INT);
                    $stmt_delete->execute();

                    // Registrar acción en el log
                    RegistrarLog("Data", "Ubicación $idubicacion eliminada correctamente");

                    // Realiza la redirección después de la eliminación
                    header("Location: fabricacion.php");
                    exit;
                } else {
                    // Si se cancela, redirigir sin hacer nada
                    echo "<script>alert('Acción cancelada');</script>";
                    header("Location: fabricacion.php");
                    exit;
                }
            }
        }
        if ($_SERVER['REQUEST_METHOD'] === 'POST') {
            if (isset($_POST['Aplicar'])) {
                // Almacenar los datos en variables de sesión antes de la confirmación
                $_SESSION['cliente'] = $_POST['cliente'];
                $_SESSION['calle'] = $_POST['calle'];
                $_SESSION['num_portal'] = $_POST['num_portal'];
                $_SESSION['cod_postal'] = $_POST['cod_postal'];
                $_SESSION['provincia'] = $_POST['provincia'];
                $_SESSION['idubicacion'] = $_POST['idubicacion'];
        
                // Mostrar formulario de confirmación
                echo "<h2 id='confirmar'>¿Está seguro de que desea modificar la ubicación con ID: {$_POST['idubicacion']}?</h2>";
                echo '<form method="post">
                        <input type="hidden" name="confirmAplicar" value="yes">
                        <button type="submit" id="conbtn">Sí, confirmar</button>
                        <button type="submit" name="confirmAplicar" value="no" id="canbtn">No, cancelar</button>
                      </form>';
            }
        
            if (isset($_POST['confirmAplicar']) && $_POST['confirmAplicar'] === 'yes') {
                // Recuperar los datos desde las variables de sesión
                $cliente = $_SESSION['cliente'];
                $calle = $_SESSION['calle'];
                $num_portal = $_SESSION['num_portal'];
                $cod_postal = $_SESSION['cod_postal'];
                $provincia = $_SESSION['provincia'];
                $idubicacion = $_SESSION['idubicacion'];
        
                // Concatenar la dirección
                $dir = "$calle;$num_portal;$cod_postal;$provincia";
        
                try {
                    // Realizar el update
                    $sql_update = "UPDATE ubicacion SET cliente = :cliente, dir = :dir WHERE idubicacion = :idubicacion";
                    $stmt_update = $dbh->prepare($sql_update);
                    $stmt_update->bindParam(':cliente', $cliente, PDO::PARAM_STR);
                    $stmt_update->bindParam(':dir', $dir, PDO::PARAM_STR);
                    $stmt_update->bindParam(':idubicacion', $idubicacion, PDO::PARAM_INT);
                    $stmt_update->execute();
        
                    // Registrar acción en el log
                    RegistrarLog("Data", "Ubicación $idubicacion modificada");
        
                    // Limpiar la sesión
                    unset($_SESSION['cliente']);
                    unset($_SESSION['calle']);
                    unset($_SESSION['num_portal']);
                    unset($_SESSION['cod_postal']);
                    unset($_SESSION['provincia']);
                    unset($_SESSION['idubicacion']);
        
                    header("Location: fabricacion.php");
                } catch (Exception $e) {
                    echo "<p>Error al actualizar la ubicación: " . $e->getMessage() . "</p>";
                    RegistrarLog("Error", "Error " . $e->getMessage() . " al actualizar la ubicación: $idubicacion.");
                }
            }
        }
    } catch (Exception $e) {
        echo "<p>Error: " . $e->getMessage() . "</p>";
        RegistrarLog("Error", "Error ". $e->getMessage() . " al procesar la solicitud.");
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
        <button type="submit" name="BorrarUbicacion" id="botonBorrar">Eliminar</button>
    </form>
</body>

</html>