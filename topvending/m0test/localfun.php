<?php
declare(strict_types=1);

require_once DOCROOT . "/clases/basededatos.php";
require_once DOCROOT . '/clases/funciones.php';


function findEmpleadoById($idEmpleado) {
    $dbh = conectar();
    if ($dbh) {
        $stm = $dbh->prepare("SELECT * FROM empleado WHERE idEmpleado = ?");
        $stm->bindParam(1, $idEmpleado);
        $stm->execute();
        $e = $stm->fetch(PDO::FETCH_ASSOC);
        return $e;
    }
    else return null;
} // findEmpleadoById()

?>
