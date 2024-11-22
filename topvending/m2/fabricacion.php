<?php
// Inicia una sesión para almacenar datos persistentes entre solicitudes
session_start();

// Define una constante "DOCROOT" que representa la raíz del directorio del proyecto
define("DOCROOT", $_SERVER['DOCUMENT_ROOT'] . "/topvending");

// Incluye las clases necesarias para manejar la base de datos y las funciones auxiliares
require_once DOCROOT . "/clases/basededatos.php";
require_once DOCROOT . '/clases/funciones.php';

// Crea una conexión con la base de datos utilizando una función personalizada
$dbh = conectar();

// Genera un menú dinámico utilizando la conexión a la base de datos
echo crearMenu($dbh);

// Validar las entradas de los filtros que llegan a través de la URL para evitar problemas de seguridad
$modelo = isset($_GET['modelo']) ? htmlspecialchars($_GET['modelo']) : ''; // Filtro para el modelo de máquina
$cliente = isset($_GET['cliente']) ? htmlspecialchars($_GET['cliente']) : ''; // Filtro para el cliente
$ciudad = isset($_GET['ciudad']) ? htmlspecialchars($_GET['ciudad']) : ''; // Filtro para la ciudad

// Consulta SQL básica para obtener datos de las máquinas
$ssql1 = "SELECT idmaquina, numserie, idestado, idubicacion, modelo FROM maquina";

// Si el filtro de modelo está definido, se añade una cláusula WHERE
if ($modelo) {
    $ssql1 .= " WHERE modelo = :modelo";
}

// Consulta SQL básica para obtener datos de las ubicaciones
$ssql2 = "SELECT idubicacion, cliente, dir FROM ubicacion";

// Construcción dinámica de la cláusula WHERE según los filtros definidos
$where = [];
if ($cliente) {
    $where[] = "cliente = :cliente"; // Filtro por cliente
}
if ($ciudad) {
    $where[] = "dir LIKE :ciudad"; // Filtro por ciudad (búsqueda parcial)
}
if (!empty($where)) {
    // Combina las condiciones del filtro con "AND" y las añade a la consulta
    $ssql2 .= " WHERE " . implode(" AND ", $where);
}

// Intenta ejecutar las consultas y manejar cualquier excepción que ocurra
try {
    // Preparar y ejecutar la consulta para las máquinas
    $stm = $dbh->prepare($ssql1);
    if ($modelo) {
        $stm->bindParam(':modelo', $modelo, PDO::PARAM_STR); // Asigna el valor del filtro de modelo
    }
    $stm->execute();
    $rows_maquinas = $stm->fetchAll(PDO::FETCH_ASSOC); // Recupera todas las filas resultantes

    // Preparar y ejecutar la consulta para las ubicaciones
    $stm = $dbh->prepare($ssql2);
    if ($cliente) {
        $stm->bindParam(':cliente', $cliente, PDO::PARAM_STR); // Asigna el valor del filtro de cliente
    }
    if ($ciudad) {
        $ciudad_param = "%$ciudad"; // Agrega los comodines para la búsqueda parcial
        $stm->bindParam(':ciudad', $ciudad_param, PDO::PARAM_STR); // Asigna el valor del filtro de ciudad
    }
    $stm->execute();
    $rows_ubicaciones = $stm->fetchAll(PDO::FETCH_ASSOC); // Recupera todas las filas resultantes
} catch (PDOException $e) {
    // Captura errores de la base de datos y los muestra en la consola del navegador
    echo '<script>console.log("Error al recoger los datos: ' . $e->getMessage() . '");</script>';
}

// Si se envía un formulario para seleccionar una fila de la tabla de máquinas
if (isset($_POST['seleccionar_fila'])) {
    // Recuperar los datos enviados en el formulario
    $id_maquina = $_POST['id_maquina'];
    $numero_serie = $_POST['numero_serie'];
    $id_estado = $_POST['id_estado'];
    $id_ubicacion = $_POST['id_ubicacion'];
    $modelo = $_POST['modelo'];

    // Guardar cada dato en una cookie con duración de 1 hora
    setcookie('id_maquina', $id_maquina, time() + 3600, "/");
    setcookie('numero_serie', $numero_serie, time() + 3600, "/");
    setcookie('id_estado', $id_estado, time() + 3600, "/");
    setcookie('id_ubicacion', $id_ubicacion, time() + 3600, "/");
    setcookie('modelo', $modelo, time() + 3600, "/");

    // Redirige a la página de máquinas (evita reenvíos de formulario al recargar)
    header("Location: maquinas.php");
    exit();
}

// Si se envía un formulario para seleccionar una fila de la tabla de ubicaciones
if (isset($_POST['seleccionar_fila2'])) {
    // Recuperar los datos enviados en el formulario
    $idubicacion = $_POST['idubicacion'];
    $cliente = $_POST['cliente'];
    $dir = $_POST['dir'];

    // Guardar cada dato en una cookie con duración de 1 hora
    setcookie('idubicacion', $idubicacion, time() + 3600, "/");
    setcookie('cliente', $cliente, time() + 3600, "/");
    setcookie('dir', $dir, time() + 3600, "/");

    // Redirige a la página de modificar ubicaciones
    header("Location: modificar_ubicaciones.php");
    exit();
}
?>


<!DOCTYPE html>
<html lang="es">
<!-- Establece el idioma del documento a español -->

<head>
    <!-- Configuración de la codificación de caracteres a UTF-8 -->
    <meta charset="UTF-8">
    <!-- Configuración de la escala de la vista para que se adapte a diferentes dispositivos -->
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <!-- Título de la página -->
    <title>Fabricación</title>
    <!-- Enlace a las hojas de estilo externas -->
    <link rel="stylesheet" href="/topvending/css/stylesheet_m2.css">
    <link rel="stylesheet" href="/topvending/css/hallentrada.css">
</head>

<body>
    <!-- Formulario para enviar datos de filtro por método GET -->
    <form method="GET" action="">
        <!-- Encabezado vacío, probablemente reservado para futuros contenidos -->
        <header></header>
        <div class="titulos" id="tit1">
            <!-- Tabla para los encabezados de las columnas -->
            <table class="tabla_encabezado">
                <thead>
                    <tr>
                        <!-- Encabezados de la tabla -->
                        <th class='th_principal'>Id de Máquina</th>
                        <th class='th_principal'>Número de Serie</th>
                        <th class='th_principal'>Id de Estado</th>
                        <th class='th_principal'>Id de Ubicación</th>
                        <th class='th_principal'>
                            <!-- Filtro por modelo con un desplegable -->
                            Modelo
                            <select class="filtros" name="modelo" onchange="this.form.submit()">
                                <!-- Opción para mostrar todas las máquinas -->
                                <option value="">Todos</option>
                                <?php
                                // Consulta para obtener los modelos únicos desde la base de datos
                                $modelos_query = "SELECT DISTINCT modelo FROM maquina";
                                $stm = $dbh->prepare($modelos_query);
                                $stm->execute();
                                // Obtiene los resultados como un array de una sola columna
                                $modelos = $stm->fetchAll(PDO::FETCH_COLUMN);
                                // Genera las opciones del desplegable
                                foreach ($modelos as $modelo_option) {
                                    // Marca la opción seleccionada si coincide con el valor actual de $modelo
                                    $selected = ($modelo == $modelo_option) ? 'selected' : '';
                                    // Escapa los valores para evitar problemas de seguridad
                                    echo "<option value='" . htmlspecialchars($modelo_option) . "' $selected>" . htmlspecialchars($modelo_option) . "</option>";
                                }
                                ?>
                            </select>
                        </th>
                        <th class='th_principal'>Modificar</th>
                    </tr>
                </thead>
            </table>
        </div>
    </form>
    <div class="tablas" id="maquinas_fabri">
        <!-- Tabla principal para mostrar los datos de las máquinas -->
        <table class='tabla_principal'>
            <?php
            // Itera sobre los resultados de las máquinas obtenidos de la base de datos
            foreach ($rows_maquinas as $row) {
                echo "<tr class='tr_contenido_principal'>";
                // Muestra los datos en las celdas de la tabla
                echo "<td class='tabla_principal_td'>" . htmlspecialchars($row['idmaquina']) . "</td>";
                echo "<td class='tabla_principal_td'>" . htmlspecialchars($row['numserie']) . "</td>";
                echo "<td class='tabla_principal_td'>" . htmlspecialchars($row['idestado']) . "</td>";
                echo "<td class='tabla_principal_td'>" . htmlspecialchars($row['idubicacion']) . "</td>";
                echo "<td class='tabla_principal_td'>" . htmlspecialchars($row['modelo']) . "</td>";
                // Columna para seleccionar una fila específica
                echo "<td class='tabla_principal_td'>
                            <form method='POST'>
                                <!-- Campos ocultos para enviar los datos de la fila seleccionada -->
                                <input type='hidden' name='id_maquina' value='" . htmlspecialchars($row['idmaquina']) . "'>
                                <input type='hidden' name='numero_serie' value='" . htmlspecialchars($row['numserie']) . "'>
                                <input type='hidden' name='id_estado' value='" . htmlspecialchars($row['idestado']) . "'>
                                <input type='hidden' name='id_ubicacion' value='" . htmlspecialchars($row['idubicacion']) . "'>
                                <input type='hidden' name='modelo' value='" . htmlspecialchars($row['modelo']) . "'>
                                <!-- Botón para enviar el formulario -->
                                <button type='submit' name='seleccionar_fila'>Seleccionar</button>
                            </form>
                          </td>";
                echo "</tr>";
            }
            ?>
        </table>
    </div>

    <div id="tit2">
        <!-- Tabla para los encabezados de las columnas -->
        <table class="tabla_encabezado">
            <thead>
                <!-- Formulario para filtros de búsqueda -->
                <form method="GET" action="">
                    <tr>
                        <!-- Encabezado para el Id de Ubicación -->
                        <th class='th_principal'>Id de Ubicación</th>
                        <th class='th_principal'>
                            <!-- Filtro para seleccionar Cliente -->
                            Cliente
                            <select class="filtros" name="cliente" onchange="this.form.submit()">
                                <option value="">Todos</option>
                                <?php
                                // Consulta para obtener los clientes únicos desde la tabla 'ubicacion'
                                $clientes_query = "SELECT DISTINCT cliente FROM ubicacion";
                                $stm = $dbh->prepare($clientes_query);
                                $stm->execute();
                                // Recupera los resultados como una lista de una sola columna
                                $clientes = $stm->fetchAll(PDO::FETCH_COLUMN);
                                // Genera las opciones del desplegable para Clientes
                                foreach ($clientes as $cliente_option) {
                                    $selected = ($cliente == $cliente_option) ? 'selected' : '';
                                    echo "<option value='" . htmlspecialchars($cliente_option) . "' $selected>" . htmlspecialchars($cliente_option) . "</option>";
                                }
                                ?>
                            </select>
                        </th>
                        <th class='th_principal'>
                            <!-- Filtro para seleccionar Ciudad -->
                            Ciudad
                            <select class="filtros" name="ciudad" onchange="this.form.submit()">
                                <option value="">Todos</option>
                                <?php
                                // Consulta para obtener las ciudades únicas extrayendo la última parte del campo 'dir'
                                $dire_query = "SELECT DISTINCT SUBSTRING_INDEX(dir, ';', -1) as ciudad FROM ubicacion";
                                $stm = $dbh->prepare($dire_query);
                                $stm->execute();
                                // Recupera las ciudades de la base de datos
                                $ciudades = $stm->fetchAll(PDO::FETCH_COLUMN);
                                foreach ($ciudades as $ciudad_option) {
                                    $ciudad_option = trim($ciudad_option); // Elimina espacios en blanco
                                    $selected = ($ciudad == $ciudad_option) ? 'selected' : '';
                                    echo "<option value='" . htmlspecialchars($ciudad_option) . "' $selected>" . htmlspecialchars($ciudad_option) . "</option>";
                                }
                                ?>
                            </select>
                        </th>
                        <!-- Encabezado para acciones de modificación -->
                        <th>Modificar</th>
                    </tr>
                </form>
            </thead>
        </table>
    </div>

    <div class="tablas" id="ubis_fabri">
        <!-- Tabla principal para mostrar los resultados de ubicaciones -->
        <table class='tabla_principal'>
            <?php
            // Itera sobre los resultados de ubicaciones obtenidos de la base de datos
            foreach ($rows_ubicaciones as $row) {
                echo "<tr class='tr_contenido_principal'>";
                // Muestra los datos en las celdas de la tabla
                echo "<td class='tabla_principal_td'>" . htmlspecialchars($row['idubicacion']) . "</td>";
                echo "<td class='tabla_principal_td'>" . htmlspecialchars($row['cliente']) . "</td>";
                echo "<td class='tabla_principal_td'>" . htmlspecialchars($row['dir']) . "</td>";
                // Columna para seleccionar una fila específica
                echo "<td class='tabla_principal_td'>
                    <form method='POST'>
                        <!-- Campos ocultos para enviar los datos de la fila seleccionada -->
                        <input type='hidden' name='idubicacion' value='" . htmlspecialchars($row['idubicacion']) . "'>
                        <input type='hidden' name='cliente' value='" . htmlspecialchars($row['cliente']) . "'>
                        <input type='hidden' name='dir' value='" . htmlspecialchars($row['dir']) . "'>
                        <!-- Botón para enviar el formulario -->
                        <button type='submit' name='seleccionar_fila2'>Seleccionar</button>
                    </form>
                  </td>";
                echo "</tr>";
            }
            ?>
        </table>
    </div>