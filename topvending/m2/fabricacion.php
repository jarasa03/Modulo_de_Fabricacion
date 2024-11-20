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

?>
<!DOCTYPE html>
<html lang="es">

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Fabricación</title>
    <link rel="stylesheet" href="/topvending/css/stylesheet_m2.css">
    <link rel="stylesheet" href="/topvending/css/hallentrada.css">
    <script src=<?php echo DOCROOT . "/js/js_m2.js" ?> defer></script>
</head>

<body>
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
                        <select class="filtros">
                            <option value="">Todos</option>
                            <?php
                            $modelos_query = "SELECT DISTINCT modelo FROM maquina";
                            $stm = $dbh->prepare($modelos_query);
                            $stm->execute();
                            $modelos = $stm->fetchAll(PDO::FETCH_COLUMN);
                            foreach ($modelos as $modelo) {
                                echo "<option>" . htmlspecialchars($modelo) . "</option>";
                            }
                            ?>
                        </select>
                    </th>
                </tr>
            </thead>
        </table>
    </div>
    <div class="tablas" id="maquinas_fabri">
        <?php
        $ssql1 = "SELECT idmaquina, numserie, idestado, idubicacion, modelo FROM maquina";
        try {
            $stm = $dbh->prepare($ssql1);
            $stm->execute();
            $rows = $stm->fetchAll(PDO::FETCH_ASSOC);
            echo "<table class='tabla_principal'>";
            foreach ($rows as $row) {
                // Comprobar si esta fila es la seleccionada
                $isSelected = ($row['idmaquina'] == $selected_row) ? ' selected' : '';
                echo "<tr class='tr_contenido_principal$isSelected'>";
                echo "<td class='tabla_principal_td'>" . htmlspecialchars($row['idmaquina']) . "</td>";
                echo "<td class='tabla_principal_td'>" . htmlspecialchars($row['numserie']) . "</td>";
                echo "<td class='tabla_principal_td'>" . htmlspecialchars($row['idestado']) . "</td>";
                echo "<td class='tabla_principal_td'>" . htmlspecialchars($row['idubicacion']) . "</td>";
                echo "<td class='tabla_principal_td'>" . htmlspecialchars($row['modelo']) . "</td>";
                echo "</tr>";
            }
            echo "</table>";
        } catch (PDOException $e) {
            echo '<script>console.log("Error al recoger los datos de máquinas");</script>';
        }
        ?>
    </div>

    <div id="tit2">
        <table class="tabla_encabezado">
            <thead>
                <tr>
                    <th class='th_principal'>Id de Ubicación</th>
                    <th class='th_principal'>
                        Cliente
                        <select class="filtros">
                            <option value="">Todos</option>
                            <?php
                            $clientes_query = "SELECT DISTINCT cliente FROM ubicacion";
                            $stm = $dbh->prepare($clientes_query);
                            $stm->execute();
                            $clientes = $stm->fetchAll(PDO::FETCH_COLUMN);
                            foreach ($clientes as $cliente) {
                                echo "<option>" . htmlspecialchars($cliente) . "</option>";
                            }
                            ?>
                        </select>
                    </th>
                    <th class='th_principal'>
                        Ciudad
                        <select class="filtros" id="filtroCiudad">
                            <option value="">Todos</option>
                            <?php
                            $dire_query = "SELECT DISTINCT dir FROM ubicacion";
                            $stm = $dbh->prepare($dire_query);
                            $stm->execute();
                            $dires = $stm->fetchAll(PDO::FETCH_COLUMN);

                            $ciudades = [];
                            foreach ($dires as $dire) {
                                $partes = explode(";", $dire);
                                $ciudad = trim(end($partes));
                                if (!in_array($ciudad, $ciudades)) {
                                    $ciudades[] = $ciudad;
                                    echo "<option>" . htmlspecialchars($ciudad) . "</option>";
                                }
                            }
                            ?>
                        </select>
                    </th>
                </tr>
            </thead>
        </table>
    </div>
    <div class="tablas" id="ubis_fabri">
        <?php
        $ssql2 = "SELECT idubicacion, cliente, dir FROM ubicacion";
        try {
            $stm = $dbh->prepare($ssql2);
            $stm->execute();
            $rows = $stm->fetchAll(PDO::FETCH_ASSOC);
            echo "<table class='tabla_principal'>";
            foreach ($rows as $row) {
                // Comprobar si esta fila es la seleccionada
                $isSelected = ($row['idubicacion'] == $selected_row) ? ' selected' : '';
                echo "<tr class='tr_contenido_principal$isSelected'>";
                echo "<td class='tabla_principal_td'>" . htmlspecialchars($row['idubicacion']) . "</td>";
                echo "<td class='tabla_principal_td'>" . htmlspecialchars($row['cliente']) . "</td>";
                echo "<td class='tabla_principal_td'>" . htmlspecialchars($row['dir']) . "</td>";
                echo "</tr>";
            }
            echo "</table>";
        } catch (PDOException $e) {
            echo '<script>console.log("Error al recoger los registros de ubicaciones");</script>';
        }
        ?>
    </div>
</body>

</html>