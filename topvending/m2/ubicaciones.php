<?php
session_start();
// Inicia una sesión para gestionar las variables de sesión en el servidor.

define("DOCROOT", $_SERVER['DOCUMENT_ROOT'] . "/topvending");
// Define una constante llamada "DOCROOT" que almacena la ruta raíz del servidor para acceder a los archivos del proyecto.

require_once DOCROOT . "/clases/basededatos.php";
// Incluye el archivo 'basededatos.php' que probablemente contiene funciones para interactuar con la base de datos.

require_once DOCROOT . '/clases/funciones.php';
// Incluye el archivo 'funciones.php' que probablemente contiene funciones adicionales del proyecto.

$dbh = conectar();
// Llama a la función 'conectar' para establecer una conexión con la base de datos y guarda el resultado en la variable '$dbh'.

echo crearMenu($dbh);
// Llama a la función 'crearMenu' (probablemente para crear el menú de navegación en la página) pasándole la conexión de base de datos.

try {
    // Inicia un bloque try-catch para manejar errores de forma controlada.

    // Consulta SQL para obtener las máquinas en el taller (idubicacion = 1)
    $sql = "
        SELECT m.numserie 
        FROM maquina m 
        JOIN ubicacion u ON m.idubicacion = u.idubicacion 
        WHERE u.idubicacion = 1";
    $stmt = $dbh->query($sql);
    // Ejecuta la consulta SQL a través de la conexión y almacena el resultado en '$stmt'.

    $maquinas = $stmt->fetchAll(PDO::FETCH_ASSOC);
    // Recupera todos los resultados de la consulta como un arreglo asociativo y los almacena en la variable '$maquinas'.

    // Consulta SQL para obtener las ubicaciones existentes (excepto el taller con idubicacion = 1)
    $sqlUbicaciones = "SELECT idubicacion, dir FROM ubicacion WHERE idubicacion != 1";
    $stmtUbicaciones = $dbh->query($sqlUbicaciones);
    // Ejecuta la consulta para obtener ubicaciones y almacena el resultado en '$stmtUbicaciones'.

    $ubicaciones = $stmtUbicaciones->fetchAll(PDO::FETCH_ASSOC);
    // Recupera todas las ubicaciones y las almacena en la variable '$ubicaciones'.

    // Verifica si la solicitud es POST y si se están enviando los parámetros necesarios para asignar una ubicación
    if ($_SERVER["REQUEST_METHOD"] == "POST" && isset($_POST['asignarUbicacion'], $_POST['numserie'], $_POST['idubicacion'])) {
        $numserie = $_POST['numserie'];
        // Recupera el número de serie de la máquina enviada por POST.

        $idubicacion = $_POST['idubicacion'];
        // Recupera el ID de la ubicación seleccionada de la lista desplegable enviada por POST.

        try {
            // Inicia otro bloque try-catch para manejar errores de la actualización en la base de datos.

            $updateSql = "UPDATE maquina SET idubicacion = :idubicacion WHERE numserie = :numserie";
            // Consulta SQL para actualizar la ubicación de la máquina en la base de datos.

            $stmt = $dbh->prepare($updateSql);
            // Prepara la consulta para su ejecución.

            $stmt->bindParam(':idubicacion', $idubicacion);
            // Asocia el valor del parámetro 'idubicacion' de la consulta con la variable '$idubicacion'.

            $stmt->bindParam(':numserie', $numserie);
            // Asocia el valor del parámetro 'numserie' de la consulta con la variable '$numserie'.

            $stmt->execute();
            // Ejecuta la consulta para actualizar la base de datos.

            // Redirige a la página 'ubicaciones.php' después de realizar la actualización.
            header("Location: ubicaciones.php");
            exit;
        } catch (Exception $e) {
            // Si ocurre un error al ejecutar la actualización, muestra un mensaje de error.
            echo "<p style='color: red;'>Error al asignar la ubicación: " . $e->getMessage() . "</p>";
        }
    }
} catch (Exception $e) {
    // Si ocurre un error al obtener las máquinas o ubicaciones, muestra un mensaje de error general.
    echo "<p>Error: " . $e->getMessage() . "</p>";
}
?>

<!DOCTYPE html>
<html lang="es">
<!-- Inicia la estructura de la página HTML con el atributo 'lang' para indicar que el idioma es español. -->

<head>
    <meta charset="UTF-8">
    <!-- Define la codificación de caracteres de la página como UTF-8 para admitir caracteres especiales. -->

    <title>Actualizar Ubicación</title>
    <!-- Título de la página que aparecerá en la pestaña del navegador. -->

    <link rel="stylesheet" href="./css/ubicaciones.css">
    <!-- Enlaza el archivo de estilos específico para esta página. -->

    <link rel="stylesheet" href="/topvending/css/stylesheet_m2.css">
    <!-- Enlaza otro archivo de estilos CSS compartido por varias páginas. -->

    <link rel="stylesheet" href="/topvending/css/hallentrada.css">
    <!-- Enlaza un tercer archivo de estilos CSS común para la página. -->

</head>

<body>
    <h1 id="h1">Asignar Máquinas en el Taller a Ubicaciones Existentes</h1>
    <!-- Título principal de la página, indicando que es para asignar máquinas a ubicaciones. -->

    <?php if ($maquinas && $ubicaciones): ?>
        <!-- Si hay máquinas y ubicaciones, muestra la tabla para asignar las ubicaciones. -->
        <table id="tablas">
            <!-- Inicia una tabla para mostrar las máquinas y las ubicaciones disponibles. -->
            <thead>
                <tr>
                    <th class="th_principal">Número de Serie</th>
                    <!-- Título de la columna para el número de serie de la máquina. -->
                    <th class="th_principal">Nueva Ubicación</th>
                    <!-- Título de la columna para la nueva ubicación. -->
                    <th class="th_principal">Acción</th>
                    <!-- Título de la columna para la acción (botón de asignar). -->
                </tr>
            </thead>
            <tbody>
                <?php foreach ($maquinas as $maquina): ?>
                    <!-- Recorre todas las máquinas y genera una fila para cada una. -->
                    <tr>
                        <td class="tabla_principal_td"><?php echo htmlspecialchars($maquina['numserie']); ?></td>
                        <!-- Muestra el número de serie de la máquina en una celda de la tabla. -->

                        <form method="POST" action="ubicaciones.php">
                            <!-- Formulario que envía los datos de la máquina y la nueva ubicación cuando se hace clic en 'Asignar'. -->
                            <td class="tabla_principal_td">
                                <select name="idubicacion" required>
                                    <!-- Lista desplegable para seleccionar la nueva ubicación. -->
                                    <?php foreach ($ubicaciones as $ubicacion): ?>
                                        <!-- Recorre todas las ubicaciones disponibles. -->
                                        <option value="<?php echo htmlspecialchars($ubicacion['idubicacion']); ?>">
                                            <!-- Crea una opción para cada ubicación con su ID y dirección. -->
                                            <?php echo htmlspecialchars($ubicacion['dir']); ?>
                                        </option>
                                    <?php endforeach; ?>
                                </select>
                            </td>
                            <td class="tabla_principal_td">
                                <input type="hidden" name="numserie" value="<?php echo htmlspecialchars($maquina['numserie']); ?>">
                                <!-- Campo oculto con el número de serie de la máquina para enviarlo con el formulario. -->
                                <button type="submit" name="asignarUbicacion" id="btn1">Asignar</button>
                                <!-- Botón para enviar el formulario y asignar la ubicación seleccionada. -->
                            </td>
                        </form>
                    </tr>
                <?php endforeach; ?>
            </tbody>
        </table>
    <?php else: ?>
        <p class="error">No hay máquinas en el taller o ubicaciones disponibles para asignar.</p>
        <!-- Error en caso de que ya no hayan maquinas en el taller -->
    <?php endif; ?>
</body>

</html>