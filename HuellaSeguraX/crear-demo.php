<?php
include_once('config/conexion.php');

// Crear usuario demo si no existe
$email_demo = "demo@petcare.com";
$consulta_demo = "SELECT id_usuario FROM usuarios WHERE email_usuario = '$email_demo'";
$resultado = $conexion->query($consulta_demo);

if ($resultado->num_rows == 0) {
    // Crear usuario demo
    $consulta_crear = "INSERT INTO usuarios (email_usuario, contraseña_usuario, telefono_usuario, nombre_usuario, apellido_usuario, foto_usuario, estado) 
                       VALUES ('$email_demo', 'demo123', '123456789', 'Usuario', 'Demo', 'demo.jpg', 'activo')";
    
    if ($conexion->query($consulta_crear)) {
        $usuario_demo_id = $conexion->insert_id;
        
        // Crear mascotas demo
        $mascota1 = "INSERT INTO mascotas (id_usuario, tipo, sexo, nombre_mascota, edad_mascota, cumpleaños_mascota, foto_mascota, estado) 
                     VALUES ($usuario_demo_id, 'perro', 'hembra', 'Luna', 3, '2021-05-15', 'luna-demo.jpg', 'activo')";
        
        $mascota2 = "INSERT INTO mascotas (id_usuario, tipo, sexo, nombre_mascota, edad_mascota, cumpleaños_mascota, foto_mascota, estado) 
                     VALUES ($usuario_demo_id, 'gato', 'macho', 'Michi', 2, '2022-03-20', 'michi-demo.jpg', 'activo')";
        
        $conexion->query($mascota1);
        $conexion->query($mascota2);
    }
}

// Responder con éxito
header('Content-Type: application/json');
echo json_encode([
    'success' => true,
    'email' => $email_demo,
    'password' => 'demo123'
]);

cerrarConexion();
?>