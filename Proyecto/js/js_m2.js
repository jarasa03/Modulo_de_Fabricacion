document.addEventListener("DOMContentLoaded", () => {
    // Función para filtrar filas de una tabla considerando múltiples filtros
    function filtrarTabla(tablaId, filtros) {
        const filas = document.querySelectorAll(`#${tablaId} .tr_contenido_principal`);

        filas.forEach(fila => {
            let mostrarFila = true;

            filtros.forEach(({ select, columnaIndex, extraerValor }) => {
                const filtro = select.value.toLowerCase();
                const celdaValor = fila.children[columnaIndex].textContent.trim();
                const valorComparar = extraerValor ? extraerValor(celdaValor).toLowerCase() : celdaValor.toLowerCase();

                if (filtro !== "" && valorComparar !== filtro) {
                    mostrarFila = false; // Si no cumple el filtro, ocultar fila
                }
            });

            fila.style.display = mostrarFila ? "" : "none"; // Mostrar u ocultar según condiciones
        });
    }

    // Filtro para la tabla "maquinas_fabri"
    const filtroModelo = document.querySelector("#tit1 .filtros");

    filtroModelo.addEventListener("change", () => {
        filtrarTabla("maquinas_fabri", [{
            select: filtroModelo,
            columnaIndex: 4, // Columna del modelo
        }]);
    });

    // Selección de filtros para la tabla "ubis_fabri"
    const filtroCliente = document.querySelector("#tit2 .filtros:nth-of-type(1)");
    const filtroCiudad = document.querySelector("#filtroCiudad");

    // Configurar los filtros
    const filtrosUbicaciones = [{
            select: filtroCliente,
            columnaIndex: 1, // Columna del cliente
        },
        {
            select: filtroCiudad,
            columnaIndex: 2, // Columna de la dirección
            extraerValor: (valor) => {
                const partes = valor.split(";");
                return partes[partes.length - 1].trim(); // Extraer ciudad de la dirección
            }
        }
    ];

    // Agregar eventos para aplicar filtros simultáneamente en "ubis_fabri"
    filtroCliente.addEventListener("change", () => filtrarTabla("ubis_fabri", filtrosUbicaciones));
    filtroCiudad.addEventListener("change", () => filtrarTabla("ubis_fabri", filtrosUbicaciones));

    // Función para marcar una fila como seleccionada
    function seleccionarFila(tr) {
        document.querySelectorAll('.tr_contenido_principal').forEach(row => {
            row.classList.remove('seleccionada');
        });
        tr.classList.add('seleccionada');
    }

    // Función para guardar la selección en una cookie
    function guardarSeleccion(valores) {
        document.cookie = `filaSeleccionada=${valores}; path=/; max-age=${30*24*60*60}`;
    }

    // Manejar clics en las filas
    document.querySelectorAll(".tr_contenido_principal").forEach(tr => {
        tr.addEventListener("click", function() {
            var valoresFila = Array.from(this.getElementsByTagName("td"))
                                   .map(td => td.textContent.trim());
            var valoresString = valoresFila.join(",");
            
            seleccionarFila(this);
            guardarSeleccion(valoresString);
        });
    });

    // Marcar la fila seleccionada al cargar la página
    const filaSeleccionada = document.cookie.split('; ').find(row => row.startsWith('filaSeleccionada='))?.split('=')[1];
    if (filaSeleccionada) {
        const valores = filaSeleccionada.split(',');
        document.querySelectorAll('.tr_contenido_principal').forEach(tr => {
            const celdas = tr.getElementsByTagName('td');
            if (Array.from(celdas).every((td, index) => td.textContent.trim() === valores[index])) {
                seleccionarFila(tr);
            }
        });
    }
});