<?php
$user = "root";
$pass = "root";
$dbn = "maquinas_expendedoras";
try {
    //  Crear conexión
    $dbh = new PDO('mysql:host=localhost;dbname=' . $dbn, $user, $pass);
    $dbh->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);
    echo '<script>console.log("Conexion exitosa");</script>';
} catch (Exception $e) {
    echo '<script>console.log("Error en la conexión con la base de datos");</script>';
    echo $e->getMessage();
}
?>
<!DOCTYPE html>
<html lang="en">

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Fabricación</title>
    <link rel="stylesheet" href="../../css/stylesheet_m2.css">
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
                    <th class='th_principal'>Modelo</th>
                </tr>
            </thead>
        </table>
    </div>
    <div class="tablas" id="maquinas_fabri">
        <?php
$ssql1 = "SELECT idmaquina, numserie, idestado, idubicacion, modelo FROM maquina";
$ssql2 = "SELECT idubicacion, cliente, dir FROM ubicacion";

try {
    $stm = $dbh->prepare($ssql1);
    $stm->execute();
    $rows = $stm->fetchAll(PDO::FETCH_ASSOC); // Obtiene todas las filas como array asociativo
    echo "<table class='tabla_principal'>";
    foreach ($rows as $row) {
        echo "<tr class='tr_contenido_principal'>";
        echo "<td class='tabla_principal_td'>" . htmlspecialchars($row['idmaquina']) . "</td>";
        echo "<td class='tabla_principal_td'>" . htmlspecialchars($row['numserie']) . "</td>";
        echo "<td class='tabla_principal_td'>" . htmlspecialchars($row['idestado']) . "</td>";
        echo "<td class='tabla_principal_td'>" . htmlspecialchars($row['idubicacion']) . "</td>";
        echo "<td class='tabla_principal_td'>" . htmlspecialchars($row['modelo']) . "</td>";
        echo "</tr>";
    }
    echo "</table>";
} catch (PDOException $e) {
    echo '<script>console.log("Error al recoger los errores de máquinas");</script>';
}
?>
    </div>
    <div id="tit2">
        <!-- Cambié el <tr> fuera de la tabla a un <thead> -->
        <table class="tabla_encabezado">
            <thead>
                <tr>
                    <th class='th_principal'>Id de Ubicación</th>
                    <th class='th_principal'>Cliente</th>
                    <th class='th_principal'>Dirección</th>
                </tr>
            </thead>
        </table>
    </div>
    <div class="tablas" id="ubis_fabri">
        <?php
try {
    $stm = $dbh->prepare($ssql2);
    $stm->execute();
    $rows = $stm->fetchAll(PDO::FETCH_ASSOC); // Obtiene todas las filas como array asociativo
    echo "<table class='tabla_principal'>";
    foreach ($rows as $row) {
        echo "<tr class='tr_contenido_principal'>";
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