document.addEventListener('DOMContentLoaded', function() {
    const rows = document.querySelectorAll('.tr_contenido_principal');

    rows.forEach(row => {
        row.addEventListener('click', function() {
            // Remover selección actual
            rows.forEach(r => r.classList.remove('selected'));

            // Añadir selección a la fila clicada
            this.classList.add('selected');

            // Guardar todos los datos de la fila en la cookie
            const cells = this.cells;
            const rowData = Array.from(cells).map(cell => cell.textContent.trim()).join(',');
            document.cookie = `filaSeleccionada=${rowData}; path=/; max-age=86400`; // Cookie válida por 1 día
        });
    });

    // Al cargar, resaltar la fila guardada en la cookie
    const selectedData = getCookie('filaSeleccionada');
    if (selectedData) {
        const selectedId = selectedData.split(',')[0]; // El ID es el primer elemento
        rows.forEach(row => {
            if (row.cells[0].textContent.trim() === selectedId) {
                row.classList.add('selected');
            }
        });
    }

    // Funcionalidad para los filtros
    const filtros = document.querySelectorAll('.filtros');
    filtros.forEach(filtro => {
        filtro.addEventListener('change', function() {
            const columna = this.parentElement.cellIndex;
            const valor = this.value.toLowerCase();
            
            rows.forEach(row => {
                const cellValue = row.cells[columna].textContent.toLowerCase();
                if (valor === '' || cellValue.includes(valor)) {
                    row.style.display = '';
                } else {
                    row.style.display = 'none';
                }
            });
        });
    });
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