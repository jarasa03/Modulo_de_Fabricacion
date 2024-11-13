document.addEventListener("DOMContentLoaded", () => {
    // Función genérica para filtrar filas de una tabla
    function filtrarTabla(select, tablaId, columnaIndex, extraerValor = val => val) {
        const filtro = select.value.toLowerCase();
        const filas = document.querySelectorAll(`#${tablaId} .tr_contenido_principal`);
        
        filas.forEach(fila => {
            const celdaValor = fila.children[columnaIndex].textContent.trim();
            const valorComparar = extraerValor(celdaValor).toLowerCase();
            
            if (filtro === "" || valorComparar === filtro) {
                fila.style.display = ""; // Mostrar fila
            } else {
                fila.style.display = "none"; // Ocultar fila
            }
        });
    }

    // Filtrar por modelo (tabla máquinas)
    document.querySelector("#tit1 .filtros").addEventListener("change", function () {
        filtrarTabla(this, "maquinas_fabri", 4);
    });

    // Filtrar por cliente (tabla ubicaciones)
    document.querySelector("#tit2 .filtros:nth-of-type(1)").addEventListener("change", function () {
        filtrarTabla(this, "ubis_fabri", 1);
    });

    // Filtrar por ciudad (tabla ubicaciones)
    document.querySelector("#filtroCiudad").addEventListener("change", function () {
        filtrarTabla(this, "ubis_fabri", 2, (valor) => {
            const partes = valor.split(";");
            return partes[partes.length - 1].trim(); // Extraer y limpiar la ciudad
        });
    });
});
