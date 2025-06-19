<?php
// Asegúrate de que no haya espacios o salida de texto antes de la etiqueta <?php

// 1. Configuración de la conexión a la base de datos
$servidor = "localhost";        // Generalmente siempre es localhost para XAMPP
$usuario = "root";              // El usuario por defecto de MySQL en XAMPP es root
$password = "";                 // Por defecto no tiene contraseña
$nombre_bd = "jelly_charms_db"; // ¡ASEGÚRATE de que este sea EXACTAMENTE el nombre de tu base de datos!

// 2. Crear la conexión
$conn = new mysqli($servidor, $usuario, $password, $nombre_bd);

// 3. Verificar la conexión
if ($conn->connect_error) {
    // Si la conexión falla, muestra un error y detiene el script
    die("Error de conexión a la base de datos: " . $conn->connect_error);
}

// 4. Procesar los datos del formulario cuando se envía (método POST)
if ($_SERVER["REQUEST_METHOD"] == "POST") {
    // Obtener los datos del formulario.
    // Usamos real_escape_string para seguridad (previene inyección SQL).
    // htmlspecialchars para prevenir ataques XSS al mostrar datos (aunque no se muestren aquí).

    // Campos obligatorios (asumimos que tienen un 'name' en tu formulario HTML)
    $nombre_completo = $conn->real_escape_string(htmlspecialchars($_POST['nombre_completo']));
    $correo_electronico = $conn->real_escape_string(htmlspecialchars($_POST['correo_electronico']));
    $telefono = $conn->real_escape_string(htmlspecialchars($_POST['telefono']));
    $tipo_producto = $conn->real_escape_string(htmlspecialchars($_POST['tipo_producto']));
    $material = $conn->real_escape_string(htmlspecialchars($_POST['material']));
    $direccion_envio = $conn->real_escape_string(htmlspecialchars($_POST['direccion_envio']));

    // Campos opcionales que pueden ser NULL en la BD si no se envían
    // Si el campo está vacío, se guarda NULL en la base de datos
    $fecha_entrega = !empty($_POST['fecha_entrega']) ? $conn->real_escape_string($_POST['fecha_entrega']) : NULL;
    $instrucciones_especiales = !empty($_POST['instrucciones_especiales']) ? $conn->real_escape_string(htmlspecialchars($_POST['instrucciones_especiales'])) : NULL;

    // Para los checkboxes de estilo (name="estilo[]" en HTML)
    $estilos_preferidos = "";
    if (isset($_POST['estilo']) && is_array($_POST['estilo'])) {
        $estilos_preferidos = $conn->real_escape_string(implode(", ", $_POST['estilo']));
    } else {
        $estilos_preferidos = NULL; // Si no se selecciona ninguno, guardar NULL
    }

    // Para el checkbox de términos y condiciones (name="terminos_aceptados" en HTML)
    // Se guarda 1 si está marcado, 0 si no lo está.
    $terminos_aceptados = isset($_POST['terminos_aceptados']) ? 1 : 0; 

    // Obtener la fecha y hora actual para el registro del envío del pedido
    $fecha_envio = date('Y-m-d H:i:s');

    // 5. Preparar la consulta SQL para insertar datos
    // Asegúrate de que los nombres de las columnas en INSERT INTO (...) VALUES (...)
    // coincidan EXACTAMENTE con los nombres de las columnas en tu tabla 'pedidos' en phpMyAdmin.
    $sql = "INSERT INTO pedidos (
                nombre_completo,
                correo_electronico,
                telefono,
                tipo_producto,
                material,
                direccion_envio,
                fecha_entrega,
                instrucciones_especiales,
                estilos_preferidos,
                terminos_aceptados,
                fecha_envio
            ) VALUES (
                '$nombre_completo',
                '$correo_electronico',
                '$telefono',
                '$tipo_producto',
                '$material',
                '$direccion_envio',
                " . ($fecha_entrega ? "'$fecha_entrega'" : "NULL") . ",
                " . ($instrucciones_especiales ? "'$instrucciones_especiales'" : "NULL") . ",
                " . ($estilos_preferidos ? "'$estilos_preferidos'" : "NULL") . ",
                '$terminos_aceptados',
                '$fecha_envio'
            )";

    // 6. Ejecutar la consulta y verificar si fue exitosa
    if ($conn->query($sql) === TRUE) {
        // *** CORRECCIÓN PRINCIPAL PARA EL PATRÓN PRG ***
        // Redirigir al usuario a la página de éxito
        header("Location: gracias.html"); // ¡Asegúrate que esta ruta es correcta y el nombre de tu archivo es gracias.html! // Redirige a la página de éxito que creaste
        exit(); // MUY IMPORTANTE: Detener la ejecución del script aquí para que la redirección funcione
    } else {
        // Si hay un error al insertar, mostrar un mensaje de error detallado para depuración
        echo "<h1>¡Oops! Ha ocurrido un error al enviar tu pedido.</h1>";
        echo "<p>Por favor, intenta de nuevo más tarde.</p>";
        echo "<p><strong>Detalle del error:</strong> " . $conn->error . "</p>"; // Muestra el error de MySQL
        echo "<p><strong>Consulta SQL que falló:</strong> " . $sql . "</p>"; // Muestra la consulta SQL que falló
    }
} else {
    // Si alguien intenta acceder al archivo PHP directamente (sin un envío POST de formulario)
    echo "<h1>Acceso no permitido.</h1>";
    echo "<p>Por favor, envía el formulario para procesar tu pedido.</p>";
}

// 7. Cerrar la conexión a la base de datos
$conn->close();
?>