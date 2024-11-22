<?php
// Iniciar la sesión para poder acceder a variables de sesión, como la autenticación del usuario.
session_start();

// Definir una constante que almacena la raíz del documento del servidor concatenada con el subdirectorio '/topvending'
define("DOCROOT", $_SERVER['DOCUMENT_ROOT'] . "/topvending");

// Incluir archivos PHP que contienen funciones y la clase para interactuar con la base de datos
require_once DOCROOT . "/clases/basededatos.php";
require_once DOCROOT . '/clases/funciones.php';

// Llamar a la función conectar() para establecer la conexión a la base de datos
$dbh = conectar();

// Sanitización del menú generado dinámicamente utilizando la función crearMenu
// El menú se genera dinámicamente con base en la conexión a la base de datos
echo crearMenu($dbh);
?>

<?php
try {
    // Verificar si la solicitud es de tipo POST (cuando se envía el formulario)
    if ($_SERVER['REQUEST_METHOD'] === 'POST') {
        // Sanitizar los datos recibidos del formulario para evitar inyecciones de código
        $cliente = htmlspecialchars($_POST['cliente']);
        $calle = htmlspecialchars($_POST['calle']);
        $numportal = htmlspecialchars($_POST['numportal']);
        $codigoPostal = htmlspecialchars($_POST['codigo_postal']);
        $provincia = htmlspecialchars($_POST['provincia']);

        // Validar que todos los campos estén completos antes de procesarlos
        if (!empty($calle) && !empty($numportal) && !empty($codigoPostal) && !empty($provincia) && !empty($cliente)) {
            // Formatear la dirección completa como un string delimitado por punto y coma
            $direccion = "$calle;$numportal;$codigoPostal;$provincia";

            // Crear la consulta SQL para insertar una nueva ubicación en la base de datos
            $sql = "INSERT INTO ubicacion (cliente, dir) VALUES (:cliente, :dir)";

            // Preparar la consulta para ejecutar la inserción en la base de datos
            $stmt = $dbh->prepare($sql);
            // Vincular los parámetros de la consulta con las variables sanitizadas
            $stmt->bindParam(':cliente', $cliente);
            $stmt->bindParam(':dir', $direccion);

            try {
                // Ejecutar la consulta de inserción
                $stmt->execute();
                // Mostrar un mensaje en la consola indicando que la inserción fue exitosa
                echo "<script>console.log('Nueva ubicación insertada correctamente');</script>";
                // Redireccionar al usuario a otra página después de la inserción
                header("Location: ./fabricacion.php");
                // Detener la ejecución del script después de la redirección
                exit;
            } catch (PDOException $e) {
                // Si ocurre un error al ejecutar la consulta, mostrar el mensaje de error
                echo "<p style='color: red;'>Error al insertar la ubicación: " . htmlspecialchars($e->getMessage()) . "</p>";
            }
        } else {
            // Si algún campo está vacío, mostrar un mensaje de error
            echo "<p style='color: red;'>Error: Todos los campos deben estar completos.</p>";
        }
    }
} catch (Exception $e) {
    // Manejar cualquier otro tipo de excepción que no haya sido capturada antes
    echo "<p style='color: red;'>Error: " . htmlspecialchars($e->getMessage()) . "</p>";
}
?>


<!DOCTYPE html>
<html lang="es">
<!-- Declaración del tipo de documento HTML5 y especificación del idioma español -->

<head>
    <!-- Sección de encabezado del documento -->
    <meta charset="UTF-8">
    <!-- Establece la codificación de caracteres a UTF-8, que permite usar caracteres especiales como acentos y eñes -->
    <title>Añadir Ubicación</title>
    <!-- El título de la página que aparecerá en la pestaña del navegador -->

    <!-- Enlaces a hojas de estilo CSS para aplicar estilos a la página -->
    <link rel="stylesheet" href="/topvending/css/hallentrada.css">
    <!-- Enlace a la hoja de estilo 'hallentrada.css' para estilos generales -->
    <link rel="stylesheet" href="/topvending/m2/css/maquinas.css">
    <!-- Enlace a la hoja de estilo 'maquinas.css' para el estilo específico de las máquinas -->
    <link rel="stylesheet" href="/topvending/m2/css/modificar_ubicaciones.css">
    <!-- Enlace a la hoja de estilo 'modificar_ubicaciones.css' para los estilos de modificación de ubicaciones -->
</head>

<body>
    <!-- Cuerpo de la página -->

    <!-- Div que contiene el menú, pero está oculto con 'display: none;' -->
    <div style="display: none;">
        <?php echo $menu_sanitizado; ?>
        <!-- El menú es insertado aquí, pero no es visible debido al estilo de ocultación -->
    </div>

    <!-- Formulario de HTML que se enviará mediante POST al servidor -->
    <form id="formulario_ubicacion" method="POST">
        <!-- El formulario tiene un id "formulario_ubicacion" y utiliza el método POST para enviar los datos -->

        <!-- Tabla para organizar los campos del formulario -->
        <table id="tablita">
            <thead>
                <!-- Cabecera de la tabla con los nombres de las columnas -->
                <tr id="encabezados">
                    <th>Cliente</th>
                    <!-- Columna para el nombre del cliente -->
                    <th>Calle</th>
                    <!-- Columna para la calle de la dirección -->
                    <th>Número de Portal</th>
                    <!-- Columna para el número del portal -->
                    <th>Código Postal</th>
                    <!-- Columna para el código postal -->
                    <th>Provincia</th>
                    <!-- Columna para la provincia -->
                </tr>
            </thead>
            <tbody>
                <!-- Cuerpo de la tabla, donde se encuentran los campos del formulario -->
                <tr>
                    <!-- Fila para los inputs del formulario -->

                    <!-- Campo para ingresar el nombre del cliente -->
                    <td><input id="cliente" type="text" name="cliente" value="<?php echo htmlspecialchars($cliente ?? ''); ?>" required></td>
                    <!-- Se usa 'htmlspecialchars' para evitar inyecciones de código y se muestra el valor de la variable 'cliente' si está disponible -->

                    <!-- Campo para ingresar la calle -->
                    <td><input id="calle" type="text" name="calle" value="<?php echo htmlspecialchars($calle ?? ''); ?>" required></td>
                    <!-- Similar al campo anterior, con el valor de la variable 'calle' si está disponible -->

                    <!-- Campo para ingresar el número de portal, con un rango de 1 a 1000 -->
                    <td><input id="numportal" min="1" max="1000" type="number" name="numportal" value="<?php echo htmlspecialchars($numportal ?? ''); ?>" required></td>
                    <!-- Rango de valores validado para el número del portal, mostrando el valor de la variable 'numportal' si está disponible -->

                    <!-- Campo para ingresar el código postal, con un rango de 1000 a 60000 -->
                    <td><input id="codigo_postal" min="1000" max="60000" type="number" name="codigo_postal" value="<?php echo htmlspecialchars($codigoPostal ?? ''); ?>" required></td>
                    <!-- Rango de valores para el código postal, mostrando el valor de la variable 'codigoPostal' si está disponible -->

                    <!-- Campo para ingresar la provincia -->
                    <td><input id="provincia" type="text" name="provincia" value="<?php echo htmlspecialchars($provincia ?? ''); ?>" required></td>
                    <!-- Similar a los campos anteriores, con el valor de la variable 'provincia' si está disponible -->
                </tr>
            </tbody>
        </table>

        <!-- Sección para el botón de envío del formulario -->
        <div style="text-align: center;">
            <!-- El formulario tiene un botón de tipo submit para enviarlo -->
            <input type="submit" value="Aplicar" id="aplicando">
            <!-- El texto del botón es "Aplicar" y tiene el id "aplicando" para posibles estilos o funcionalidades -->
        </div>
    </form>
</body>

</html>