document.addEventListener('DOMContentLoaded', function() {
    // Tu código existente aquí

    // Añadir funcionalidad para seleccionar filas y guardar en cookie
    const tablas = document.querySelectorAll('.tabla_principal');
    tablas.forEach(tabla => {
        tabla.addEventListener('click', function(e) {
            const tr = e.target.closest('.tr_contenido_principal');
            if (tr) {
                // Remover la selección de todas las filas en todas las tablas
                document.querySelectorAll('.tr_contenido_principal').forEach(row => {
                    row.classList.remove('selected');
                });

                // Seleccionar la fila clicada
                tr.classList.add('selected');

                // Guardar el ID de la fila en una cookie
                const id = tr.cells[0].textContent; // Asumiendo que el ID está en la primera celda
                document.cookie = `filaSeleccionada=${id}; path=/; max-age=86400`; // Cookie válida por 1 día
            }
        });
    });

    // Cargar la selección guardada al cargar la página
    const selectedId = getCookie('filaSeleccionada');
    if (selectedId) {
        const selectedRow = document.querySelector(`.tr_contenido_principal td:first-child:contains('${selectedId}')`).closest('tr');
        if (selectedRow) {
            selectedRow.classList.add('selected');
        }
    }
});

// Función para obtener el valor de una cookie
function getCookie(name) {
    const nameEQ = name + "=";
    const ca = document.cookie.split(';');
    for (let i = 0; i < ca.length; i++) {
        let c = ca[i];
        while (c.charAt(0) == ' ') c = c.substring(1);
        if (c.indexOf(nameEQ) == 0) return c.substring(nameEQ.length, c.length);
    }
    return null;
}

// Añadir un selector personalizado para buscar texto en celdas
jQuery.expr[':'].contains = function(a, i, m) {
    return jQuery(a).text().toUpperCase().indexOf(m[3].toUpperCase()) >= 0;
};
document.addEventListener('DOMContentLoaded', function() {
    const rows = document.querySelectorAll('.tr_contenido_principal');

    rows.forEach(row => {
        row.addEventListener('click', function() {
            // Remover selección actual
            rows.forEach(r => r.classList.remove('selected'));

            // Añadir selección a la fila clicada
            this.classList.add('selected');

            // Guardar el ID de la fila seleccionada en una cookie
            const id = this.cells[0].textContent.trim(); // Asumiendo que el ID está en la primera celda
            document.cookie = `filaSeleccionada=${id}; path=/; max-age=86400`; // Cookie válida por 1 día
        });
    });

    // Al cargar, resaltar la fila guardada en la cookie
    const selectedId = getCookie('filaSeleccionada');
    if (selectedId) {
        rows.forEach(row => {
            if (row.cells[0].textContent.trim() === selectedId) {
                row.classList.add('selected');
            }
        });
    }
});

// Función para obtener el valor de una cookie
function getCookie(name) {
    const nameEQ = name + "=";
    const ca = document.cookie.split(';');
    for (let i = 0; i < ca.length; i++) {
        let c = ca[i];
        while (c.charAt(0) == ' ') c = c.substring(1);
        if (c.indexOf(nameEQ) == 0) return c.substring(nameEQ.length, c.length);
    }
    return null;
}