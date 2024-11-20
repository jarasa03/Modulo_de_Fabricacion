<?php
session_start();
define("DOCROOT", $_SERVER['DOCUMENT_ROOT'] . "/topvending");
require_once DOCROOT . "/clases/basededatos.php";
require_once DOCROOT . '/clases/funciones.php';
$dbh = conectar();
echo crearMenu($dbh);

// Obtener el valor de la cookie si existe
if (isset($_COOKIE['filaSeleccionada'])) {
    $cookieValue = htmlspecialchars($_COOKIE['filaSeleccionada']);
    $valoresArray = explode(',', $cookieValue);
    $selected_row = $valoresArray[0]; // El ID de la máquina es el primer valor
} else {
    $selected_row = '';
}

// Obtener los valores de los filtros
$modelo = isset($_GET['modelo']) ? $_GET['modelo'] : '';
$cliente = isset($_GET['cliente']) ? $_GET['cliente'] : '';
$ciudad = isset($_GET['ciudad']) ? $_GET['ciudad'] : '';

// Consulta SQL para máquinas con filtro
$ssql1 = "SELECT idmaquina, numserie, idestado, idubicacion, modelo FROM maquina";
if ($modelo) {
    $ssql1 .= " WHERE modelo = :modelo";
}

// Consulta SQL para ubicaciones con filtros
$ssql2 = "SELECT idubicacion, cliente, dir FROM ubicacion";
$where = [];
if ($cliente) {
    $where[] = "cliente = :cliente";
}
if ($ciudad) {
    $where[] = "dir LIKE :ciudad";
}
if (!empty($where)) {
    $ssql2 .= " WHERE " . implode(" AND ", $where);
}

// Ejecutar las consultas
try {
    $stm = $dbh->prepare($ssql1);
    if ($modelo) {
        $stm->bindParam(':modelo', $modelo, PDO::PARAM_STR);
    }
    $stm->execute();
    $rows_maquinas = $stm->fetchAll(PDO::FETCH_ASSOC);

    $stm = $dbh->prepare($ssql2);
    if ($cliente) {
        $stm->bindParam(':cliente', $cliente, PDO::PARAM_STR);
    }
    if ($ciudad) {
        $ciudad_param = "%$ciudad";
        $stm->bindParam(':ciudad', $ciudad_param, PDO::PARAM_STR);
    }
    $stm->execute();
    $rows_ubicaciones = $stm->fetchAll(PDO::FETCH_ASSOC);
} catch (PDOException $e) {
    echo '<script>console.log("Error al recoger los datos: ' . $e->getMessage() . '");</script>';
}
?>
<!DOCTYPE html>
<html lang="es">

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Fabricación</title>
    <link rel="stylesheet" href="/topvending/css/stylesheet_m2.css">
    <link rel="stylesheet" href="/topvending/css/hallentrada.css">
    <script src="<?php echo DOCROOT . "/js/js_m2.js" ?>" defer></script>
</head>

<body>
    <form method="GET" action="">
        <header></header>
        <div class="titulos" id="tit1">
            <table class="tabla_encabezado">
                <thead>
                    <tr>
                        <th class='th_principal'>Id de Máquina</th>
                        <th class='th_principal'>Número de Serie</th>
                        <th class='th_principal'>Id de Estado</th>
                        <th class='th_principal'>Id de Ubicación</th>
                        <th class='th_principal'>
                            Modelo
                            <select class="filtros" name="modelo" onchange="this.form.submit()">
                                <option value="">Todos</option>
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
                        </th>
                    </tr>
                </thead>
            </table>
        </div>
        <div class="tablas" id="maquinas_fabri">
            <table class='tabla_principal'>
                <?php
                foreach ($rows_maquinas as $row) {
                    $isSelected = ($row['idmaquina'] == $selected_row) ? ' selected' : '';
                    echo "<tr class='tr_contenido_principal$isSelected'>";
                    echo "<td class='tabla_principal_td'>" . htmlspecialchars($row['idmaquina']) . "</td>";
                    echo "<td class='tabla_principal_td'>" . htmlspecialchars($row['numserie']) . "</td>";
                    echo "<td class='tabla_principal_td'>" . htmlspecialchars($row['idestado']) . "</td>";
                    echo "<td class='tabla_principal_td'>" . htmlspecialchars($row['idubicacion']) . "</td>";
                    echo "<td class='tabla_principal_td'>" . htmlspecialchars($row['modelo']) . "</td>";
                    echo "</tr>";
                }
                ?>
            </table>
        </div>

        <div id="tit2">
            <table class="tabla_encabezado">
                <thead>
                    <tr>
                        <th class='th_principal'>Id de Ubicación</th>
                        <th class='th_principal'>
                            Cliente
                            <select class="filtros" name="cliente" onchange="this.form.submit()">
                                <option value="">Todos</option>
                                <?php
                                $clientes_query = "SELECT DISTINCT cliente FROM ubicacion";
                                $stm = $dbh->prepare($clientes_query);
                                $stm->execute();
                                $clientes = $stm->fetchAll(PDO::FETCH_COLUMN);
                                foreach ($clientes as $cliente_option) {
                                    $selected = ($cliente == $cliente_option) ? 'selected' : '';
                                    echo "<option value='" . htmlspecialchars($cliente_option) . "' $selected>" . htmlspecialchars($cliente_option) . "</option>";
                                }
                                ?>
                            </select>
                        </th>
                        <th class='th_principal'>
                            Ciudad
                            <select class="filtros" name="ciudad" onchange="this.form.submit()">
                                <option value="">Todos</option>
                                <?php
                                $dire_query = "SELECT DISTINCT SUBSTRING_INDEX(dir, ';', -1) as ciudad FROM ubicacion";
                                $stm = $dbh->prepare($dire_query);
                                $stm->execute();
                                $ciudades = $stm->fetchAll(PDO::FETCH_COLUMN);
                                foreach ($ciudades as $ciudad_option) {
                                    $ciudad_option = trim($ciudad_option);
                                    $selected = ($ciudad == $ciudad_option) ? 'selected' : '';
                                    echo "<option value='" . htmlspecialchars($ciudad_option) . "' $selected>" . htmlspecialchars($ciudad_option) . "</option>";
                                }
                                ?>
                            </select>
                        </th>
                    </tr>
                </thead>
            </table>
        </div>
        <div class="tablas" id="ubis_fabri">
            <table class='tabla_principal'>
                <?php
                foreach ($rows_ubicaciones as $row) {
                    $isSelected = ($row['idubicacion'] == $selected_row) ? ' selected' : '';
                    echo "<tr class='tr_contenido_principal$isSelected'>";
                    echo "<td class='tabla_principal_td'>" . htmlspecialchars($row['idubicacion']) . "</td>";
                    echo "<td class='tabla_principal_td'>" . htmlspecialchars($row['cliente']) . "</td>";
                    echo "<td class='tabla_principal_td'>" . htmlspecialchars($row['dir']) . "</td>";
                    echo "</tr>";
                }
                ?>
            </table>
        </div>
    </form>
</body>

</html>