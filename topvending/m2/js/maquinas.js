document.getElementById("formulario_maquina").addEventListener("submit", () => {

    // Recoger los datos del formulario
    let formData = new FormData(this); // 'this' hace referencia al formulario

    // Enviar los datos al archivo PHP usando fetch
    fetch('../maquinas.php', {
            method: 'POST',
            body: formData // Enviar los datos del formulario
        })
        .then(response => response.text()) // Procesar la respuesta del servidor
        .then(data => {
            console.log(data); // Aquí puedes hacer algo con la respuesta del servidor
            // Tal vez actualizar la interfaz o mostrar un mensaje al usuario
        })
        .catch(error => console.error('Error:', error));
});